<?php
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../inc/mail.php';
// Include structured logger so we can persist SMTP session traces and errors
require_once __DIR__ . '/../inc/log.php';

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = filter_var($_POST['to'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$to) {
        $result = ['ok' => false, 'msg' => 'Invalid email address'];
    } else {
        $subject = 'SMTP Test from Indoor Climate Website';
        $body = '<p>This is a test email sent at ' . htmlspecialchars(date('c')) . ' from the website.</p>';
        $ok = false;
        $error_msg = '';
        try {
            // If admin forced API use, call the HTTP API directly
            if (!empty($_POST['use_api'])) {
                try {
                    $resp = send_via_brevo_api($to, $subject, $body, $smtp_from);
                    $ok = true;
                    $api_result = $resp;
                } catch (Exception $e) {
                    $error_msg = 'Brevo API direct error: ' . $e->getMessage();
                    error_log('Brevo API direct error: ' . $e->getMessage());
                    $ok = false;
                }
            } else {
                // Normal path: try send_mail which will attempt SMTP/socket/mail() and fall back to API if configured
                $ok = send_mail($to, $subject, $body);
            }
        } catch (Exception $e) {
            $error_msg = 'send_mail threw: ' . $e->getMessage();
            error_log('Test SMTP exception: ' . $e->getMessage());
            $ok = false;
        }

        // If send_mail returned false, attempt a direct socket-based send for more detailed diagnostics
        if (!$ok) {
            try {
                if (function_exists('smtp_send_via_socket')) {
                    smtp_send_via_socket($to, $subject, $body, $smtp_from, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_secure);
                    $ok = true;
                } else {
                    $error_msg .= ' socket sender not available.';
                }
            } catch (Exception $e) {
                $error_msg .= ' socket send failed: ' . $e->getMessage();
                error_log('Test SMTP socket exception: ' . $e->getMessage());
                $ok = false;
            }
        }

        if ($ok) {
            // Pull last smtp_session entry for diagnostic display
            $trace_msg = '';
            if (function_exists('app_log')) {
                // Try to read the logs file directly for the last smtp_session entry
                $logfile = __DIR__ . '/../../logs/app.log';
                if (is_readable($logfile)) {
                    $lines = array_slice(file($logfile), -200);
                    foreach (array_reverse($lines) as $line) {
                        if (strpos($line, '"smtp_session"') !== false) { $trace_msg = $line; break; }
                    }
                }
            }
            $result = ['ok' => true, 'msg' => 'Message sent successfully to ' . htmlspecialchars($to), 'trace' => $trace_msg];
        } else {
            // Log structured error to app log as well (if available)
            if (function_exists('app_log')) {
                app_log('error', 'smtp_test_failed', ['to' => $to, 'error' => $error_msg]);
            }
            $msg = 'Failed to send message.';
            if ($error_msg) $msg .= ' Details: ' . htmlspecialchars($error_msg);
            $msg .= ' Check server error logs (apache/php-fpm) for full traces.';
            $result = ['ok' => false, 'msg' => $msg];
        }
    }
}

?>
<main>
    <h2>SMTP Test</h2>
    <p>Use this page to send a live SMTP test using the configured SMTP relay. Requires admin access.</p>
    <?php if ($result): ?>
        <div class="message <?php echo $result['ok'] ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($result['msg']); ?></div>
            <?php if (!empty($result['trace'])): ?>
                <h3>SMTP trace (last entry)</h3>
                <pre><?php echo htmlspecialchars($result['trace']); ?></pre>
            <?php else: ?>
                <?php // show recent app.log lines for diagnosis ?>
                <h3>Recent app log</h3>
                <pre><?php
                    $logfile = __DIR__ . '/../../logs/app.log';
                    if (is_readable($logfile)) {
                        echo htmlspecialchars(implode('', array_slice(file($logfile), -200)));
                    } else {
                        echo "(no app.log found or not readable)";
                    }
                ?></pre>
            <?php endif; ?>
    <?php endif; ?>
    <form method="post" class="form-inline">
        <label for="to">Recipient email:</label>
        <input type="email" id="to" name="to" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required />
        <p><label><input type="checkbox" name="use_api" value="1" /> Force using Brevo HTTP API (fallback enabled automatically)</label></p>
        <button class="btn btn-success" type="submit">Send test</button>
    </form>
</main>
