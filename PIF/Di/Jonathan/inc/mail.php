<?php
// Simple mail helper. In production, configure a real SMTP transport or use a mailer library.
// Send an email using configured transports: PHPMailer SMTP, socket SMTP, native mail(), or API fallback
function send_mail($to, $subject, $body) {
    // Prefer SMTP via PHPMailer if available and configured in config.php
    global $smtp_enabled, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_secure, $smtp_from;
    $sent = false;

    if ($smtp_enabled && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = $smtp_secure;
            $mail->Port = intval($smtp_port);
            $mail->setFrom($smtp_from, 'Indoor Climate');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $sent = $mail->send();
        } catch (Exception $e) {
            error_log('PHPMailer send failed: ' . $e->getMessage());
            $sent = false;
        }
    }

    // If PHPMailer not available and SMTP is enabled, try a minimal socket-based SMTP sender
    if ($smtp_enabled && !$sent && !class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $sent = smtp_send_via_socket($to, $subject, $body, $smtp_from, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_secure);
        } catch (Exception $e) {
            error_log('Socket SMTP send failed: ' . $e->getMessage());
            $sent = false;
        }
    }

    // Fallback to the native mail() function if PHPMailer not available or SMTP disabled
    if (!$sent) {
        $headers = "From: $smtp_from\r\n" .
                   "MIME-Version: 1.0\r\n" .
                   "Content-type: text/html; charset=utf-8\r\n";
        try {
            $sent = mail($to, $subject, $body, $headers);
        } catch (Exception $e) {
            error_log('mail() failed: ' . $e->getMessage());
            $sent = false;
        }
    }

    // If native mail() failed and Brevo API key present, try Brevo API as an HTTP fallback
    if (!$sent) {
        try {
            $apiResp = send_via_brevo_api($to, $subject, $body, $smtp_from);
            if (!empty($apiResp)) {
                // Consider this a success
                $sent = true;
            }
        } catch (Exception $e) {
            error_log('Brevo API send failed: ' . $e->getMessage());
            $sent = false;
        }
    }

    // Write to tmp_emails for development/debug so tokens can be read during tests
    // This is controlled by the $debug_emails flag in config.php to avoid leaving dev artifacts enabled in production.
    global $debug_emails;
    $fn = null;
    if (!empty($debug_emails)) {
        $tmpdir = __DIR__ . '/../../tmp_emails';
        $writedir = $tmpdir;
        // Try to create folder, but fall back to system temp dir on failure
        if (!is_dir($writedir)) {
            // attempt to create, but avoid letting warnings become exceptions
            $ok = @mkdir($writedir, 0755, true);
            if (!$ok || !is_dir($writedir) || !is_writable($writedir)) {
                // fallback
                $writedir = sys_get_temp_dir();
            }
        }
        $fn = $writedir . DIRECTORY_SEPARATOR . date('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $to) . '.html';
        $content = "To: $to\nSubject: $subject\n\n" . $body;
        // suppress warnings when writing, and don't throw on failure
        @file_put_contents($fn, $content);
    }

    // IMPORTANT: return true only if an actual delivery method succeeded.
    // Do not treat the presence of a debug email file as a successful send — that masks failures.
    return (bool)$sent;
}

// Minimal SMTP sender using sockets to deliver a single message via an SMTP relay.
// Implements EHLO/STARTTLS/AUTH/DATA over a raw socket for simple relay use
function smtp_send_via_socket($to, $subject, $body, $from, $host, $port, $user, $pass, $secure = 'tls') {
    $timeout = 10;
    $errno = 0; $errstr = '';
    $transport = '';
    $context = stream_context_create([]);

    // If ssl direct (typically port 465), use ssl:// transport
    if (intval($port) === 465 || $secure === 'ssl') {
        $transport = 'ssl://' . $host;
    } else {
        $transport = $host;
    }

    $fp = @stream_socket_client($transport . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    if (!$fp) {
        throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
    }

    stream_set_timeout($fp, $timeout);

    // Read lines from SMTP socket until the response's final line (space after status code)
    $read = function() use ($fp) {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };

    $trace = [];
    $greeting = $read();
    $trace[] = ['recv' => $greeting];
    // EHLO
    fwrite($fp, "EHLO localhost\r\n");
    $ehlo = $read();
    $trace[] = ['cmd' => 'EHLO', 'recv' => $ehlo];

    // If using STARTTLS, negotiate it
    if (($secure === 'tls' || intval($port) === 587) && stripos($ehlo, 'STARTTLS') !== false) {
        fwrite($fp, "STARTTLS\r\n");
        $start = $read();
        $trace[] = ['cmd' => 'STARTTLS', 'recv' => $start];
        // enable crypto
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception('Failed to enable TLS for SMTP connection');
        }
        // EHLO again after STARTTLS
        fwrite($fp, "EHLO localhost\r\n");
        $ehlo = $read();
        $trace[] = ['cmd' => 'EHLO(post-STARTTLS)', 'recv' => $ehlo];
    }

    // Auth login
    fwrite($fp, "AUTH LOGIN\r\n");
    $auth1 = $read();
    $trace[] = ['cmd' => 'AUTH', 'recv' => $auth1];
    fwrite($fp, base64_encode($user) . "\r\n");
    $auth2 = $read();
    $trace[] = ['cmd' => 'AUTH user', 'recv' => $auth2];
    fwrite($fp, base64_encode($pass) . "\r\n");
    $auth3 = $read();
    $trace[] = ['cmd' => 'AUTH pass', 'recv' => $auth3];
    if (strpos($auth3, '235') === false) {
        throw new Exception('SMTP authentication failed: ' . trim($auth3));
    }

    // Use envelope sender equal to the authenticated user if possible (some relays require verified sender)
    $envelope_from = $user ?: $from;

    // MAIL FROM
    fwrite($fp, "MAIL FROM:<$envelope_from>\r\n");
    $mfrom = $read();
    $trace[] = ['cmd' => 'MAIL FROM', 'recv' => $mfrom];
    if (strpos($mfrom, '250') === false && strpos($mfrom, '251') === false) {
        throw new Exception('MAIL FROM rejected: ' . trim($mfrom));
    }

    // RCPT TO
    fwrite($fp, "RCPT TO:<$to>\r\n");
    $rcpt = $read();
    $trace[] = ['cmd' => 'RCPT TO', 'recv' => $rcpt];
    if (strpos($rcpt, '250') === false && strpos($rcpt, '251') === false) {
        throw new Exception('RCPT TO rejected: ' . trim($rcpt));
    }

    // DATA
    fwrite($fp, "DATA\r\n");
    $dataResp = $read();
    if (strpos($dataResp, '354') === false) {
        throw new Exception('SMTP DATA not accepted: ' . $dataResp);
    }

    $headers = [];
    // Use the envelope sender (authenticated user) for the visible From header to match envelope and reduce spam filtering
    $headers[] = 'From: ' . $envelope_from;
    $headers[] = 'To: ' . $to;
    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=utf-8';
    $headers[] = 'Date: ' . date('r');

    $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n." . "\r\n";
    fwrite($fp, $message);
    $sendResp = $read();
    $trace[] = ['cmd' => 'DATA', 'recv' => $sendResp];
    if (strpos($sendResp, '250') === false) {
        throw new Exception('SMTP send failed: ' . trim($sendResp));
    }

    // QUIT
    fwrite($fp, "QUIT\r\n");
    $quit = $read();
    $trace[] = ['cmd' => 'QUIT', 'recv' => $quit];
    if (function_exists('app_log')) {
        app_log('info', 'smtp_session', ['host' => $host, 'port' => $port, 'envelope_from' => $envelope_from, 'to' => $to, 'trace' => $trace]);
    }
    fclose($fp);
    return true;
}

// Send via Brevo (Sendinblue) HTTP API v3. Returns decoded JSON response on success, throws Exception on error.
// HTTP POST to Brevo SMTP endpoint as a fallback when direct SMTP/mail() fails
function send_via_brevo_api($to, $subject, $body, $from_email = null) {
    global $smtp_pass, $smtp_user;
    // Use smtp_pass as API key if available (project stores Brevo credentials here)
    $apiKey = $smtp_pass;
    if (empty($apiKey)) {
        throw new Exception('Brevo API key not configured');
    }

    $url = 'https://api.brevo.com/v3/smtp/email';
    $payload = [
        'sender' => ['email' => $from_email ?? $smtp_user],
        'to' => [[ 'email' => $to ]],
        'subject' => $subject,
        'htmlContent' => $body
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'api-key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp === false) {
        throw new Exception('cURL error: ' . $err);
    }
    $json = json_decode($resp, true);
    if ($code < 200 || $code >= 300) {
        $msg = isset($json['message']) ? $json['message'] : $resp;
        throw new Exception('Brevo API error: HTTP ' . $code . ' - ' . $msg);
    }
    // Log minimal info but never log the API key
    if (function_exists('app_log')) {
        app_log('info', 'brevo_api_send', ['to' => $to, 'status_code' => $code, 'response' => is_array($json) ? substr(json_encode($json),0,512) : $resp]);
    }
    return $json;
}

// Build an absolute URL for the given site-relative path using current request context
function build_url($path) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $proto . '://' . $host . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/' . ltrim($path, '/');
}

?>
