<?php
/**
 * Minimal mail helper for sending HTML emails with a central config.
 * Uses PHP's mail() (system MTA). If SMTP credentials are configured later,
 * this module can be extended to use them without touching callers.
 */

require_once __DIR__ . '/mailer_queue.php';

function mailer_config(): array
{
    $cfgFile = __DIR__ . '/mail_config.php';
    $base = [
        'host' => 'localhost',
        'port' => 587,
        'username' => '',
        'password' => '',
        'from' => '',
        'from_name' => 'Portable Indoor Feedback',
    ];
    if (is_file($cfgFile)) {
        $fileCfg = include $cfgFile;
        if (is_array($fileCfg)) {
            $base = array_merge($base, $fileCfg);
        }
    }
    // Env overrides for secrets
    if (getenv('MAIL_PASSWORD') !== false) {
        $base['password'] = getenv('MAIL_PASSWORD');
    }
    if (getenv('MAIL_FROM') !== false) {
        $base['from'] = getenv('MAIL_FROM');
    }
    if (getenv('MAIL_FROM_NAME') !== false) {
        $base['from_name'] = getenv('MAIL_FROM_NAME');
    }
    return $base;
}

function mailer_from(array $cfg): array
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $email = $cfg['from'] ?: ($cfg['username'] ?: ('no-reply@' . preg_replace('/^www\./', '', $host)));
    $name = $cfg['from_name'] ?: 'Portable Indoor Feedback';
    return [$email, $name];
}

function mailer_send(string $to, string $subject, string $html): bool
{
    $cfg = mailer_config();
    [$from, $fromName] = mailer_from($cfg);
    $headers = [];
    $headers[] = 'From: ' . sprintf('%s <%s>', $fromName, $from);
    $headers[] = 'Reply-To: ' . $from;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    // If SMTP creds are provided, use SMTP; otherwise fallback to mail()
    if (!empty($cfg['host']) && !empty($cfg['username']) && !empty($cfg['password']) && $cfg['host'] !== 'localhost') {
        $ok = smtp_send($cfg, $from, $to, $subject, $html, $headers);
        if (!$ok) {
            error_log('[mailer] smtp_send() failed; falling back to mail() for ' . $to);
        } else {
            return true;
        }
    }

    $ok = @mail($to, $subject, $html, implode("\r\n", $headers));
    if (!$ok) {
        error_log('[mailer] mail() failed to send to ' . $to);
    }
    return (bool)$ok;
}

function send_welcome_email(string $to, string $firstName, string $username): bool
{
    $subject = 'Welcome to Portable Indoor Feedback';
    $body = '<h2>Welcome, ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . '!</h2>' .
            '<p>Thank you for signing up for Portable Indoor Feedback.</p>' .
            '<p><strong>Account Details:</strong></p>' .
            '<ul>' .
            '<li>Username: ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</li>' .
            '<li>Email: ' . htmlspecialchars($to, ENT_QUOTES, 'UTF-8') . '</li>' .
            '</ul>' .
            '<p>You can now log in and start managing your weather stations and measurements.</p>' .
            '<p>Best regards,<br>The Portable Indoor Feedback Team</p>';
    return mailer_send_async($to, $subject, $body);
}

function send_station_added_email(string $to, string $firstName, string $serial, ?string $name = null): bool
{
    $subject = 'Station registered successfully';
    $title = htmlspecialchars($name ?: $serial, ENT_QUOTES, 'UTF-8');
    $serialEsc = htmlspecialchars($serial, ENT_QUOTES, 'UTF-8');
    $body = '<h2>Hi ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ',</h2>' .
            '<p>Your station has been registered to your account.</p>' .
            '<ul>' .
            '<li><strong>Serial:</strong> ' . $serialEsc . '</li>' .
            '<li><strong>Name:</strong> ' . $title . '</li>' .
            '</ul>' .
            '<p>You can edit details anytime in the Stations page.</p>';
    return mailer_send_async($to, $subject, $body);
}

function send_account_changed_email(string $to, string $firstName, string $what = 'account details'): bool
{
    $subject = 'Your ' . $what . ' was updated';
    $body = '<h2>Hello ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ',</h2>' .
            '<p>This is a confirmation that your ' . htmlspecialchars($what, ENT_QUOTES, 'UTF-8') . ' was changed.</p>' .
            '<p>If you did not make this change, please contact support immediately.</p>';
    return mailer_send_async($to, $subject, $body);
}

function send_station_deleted_email(string $to, string $firstName, string $serial, ?string $name = null): bool
{
    $subject = 'Station deleted';
    $title = htmlspecialchars($name ?: $serial, ENT_QUOTES, 'UTF-8');
    $serialEsc = htmlspecialchars($serial, ENT_QUOTES, 'UTF-8');
    $body = '<h2>Hi ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ',</h2>' .
            '<p>Your station has been deleted from your account.</p>' .
            '<ul>' .
            '<li><strong>Serial:</strong> ' . $serialEsc . '</li>' .
            '<li><strong>Name:</strong> ' . $title . '</li>' .
            '</ul>' .
            '<p>All measurements associated with this station have also been removed.</p>' .
            '<p>If you did not make this change, please contact support immediately.</p>';
    return mailer_send_async($to, $subject, $body);
}

function send_invitation_email(string $to, string $fromName, string $token): bool
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $link = $scheme . '://' . $host . '/invite.php?token=' . urlencode($token);
    $subject = htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8') . ' invited you to Portable Indoor Feedback';
    $body = '<h2>You\'re Invited!</h2>' .
            '<p>' . htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8') . ' has invited you to join Portable Indoor Feedback.</p>' .
            '<p>Click the link below to sign up and automatically become friends:</p>' .
            '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block; padding:10px 20px; background:#4ab846; color:#ffffff; text-decoration:none; border-radius:4px;">Accept Invitation</a></p>' .
            '<p>Or copy this link: <br><code>' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</code></p>' .
            '<p>This invitation will expire in 7 days.</p>' .
            '<p>Best regards,<br>The Portable Indoor Feedback Team</p>';
    return mailer_send_async($to, $subject, $body);
}

// --- SMTP transport ---
function smtp_send(array $cfg, string $from, string $to, string $subject, string $html, array $headers): bool
{
    $host = $cfg['host'] ?: 'localhost';
    $port = (int)($cfg['port'] ?? 587);
    $username = $cfg['username'] ?? '';
    $password = $cfg['password'] ?? '';

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ]);

    $fp = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    if (!$fp) {
        error_log("[mailer] SMTP connect failed: {$errstr} ({$errno})");
        return false;
    }
    stream_set_timeout($fp, 15);

    $read = function() use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') break;
        }
        return $data;
    };
    $write = function(string $cmd) use ($fp) {
        fputs($fp, $cmd . "\r\n");
    };
    $expect = function(string $response, string $label) use ($read): bool {
        $data = $read();
        if (strpos($data, $response) !== 0) {
            error_log("[mailer] SMTP unexpected {$label}: {$data}");
            return false;
        }
        return true;
    };

    $greet = $read();
    if (strpos($greet, '220') !== 0) {
        error_log('[mailer] SMTP greeting failed: ' . $greet);
        fclose($fp);
        return false;
    }

    $write('EHLO localhost');
    $ehlo = $read();
    // Try STARTTLS if offered
    if (stripos($ehlo, 'STARTTLS') !== false) {
        $write('STARTTLS');
        if (!$expect('220', 'STARTTLS')) {
            // continue without TLS
        } else {
            if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log('[mailer] SMTP TLS negotiation failed');
            } else {
                // Re-issue EHLO after TLS
                $write('EHLO localhost');
                $ehlo = $read();
            }
        }
    }

    // AUTH LOGIN
    $write('AUTH LOGIN');
    if (!$expect('334', 'AUTH LOGIN challenge')) { fclose($fp); return false; }
    $write(base64_encode($username));
    if (!$expect('334', 'username response')) { fclose($fp); return false; }
    $write(base64_encode($password));
    $authResp = $read();
    if (strpos($authResp, '235') !== 0 && strpos($authResp, '250') !== 0) {
        error_log('[mailer] SMTP auth failed: ' . $authResp);
        fclose($fp);
        return false;
    }

    // Envelope
    $write('MAIL FROM:<' . $from . '>');
    if (!$expect('250', 'MAIL FROM')) { fclose($fp); return false; }
    $write('RCPT TO:<' . $to . '>');
    if (!$expect('250', 'RCPT TO')) { fclose($fp); return false; }
    $write('DATA');
    if (!$expect('354', 'DATA')) { fclose($fp); return false; }

    // Build message
    $messageHeaders = $headers;
    $messageHeaders[] = 'To: <' . $to . '>';
    $messageHeaders[] = 'Subject: ' . $subject;
    $data = implode("\r\n", $messageHeaders) . "\r\n\r\n" . $html;
    // Dot-stuffing
    $data = preg_replace('/\r?\n\./', "\r\n..", $data);

    fputs($fp, $data . "\r\n.\r\n");
    if (!$expect('250', 'message accept')) { fclose($fp); return false; }

    $write('QUIT');
    fclose($fp);
    return true;
}
