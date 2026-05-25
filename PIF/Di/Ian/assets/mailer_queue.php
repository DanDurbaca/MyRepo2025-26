<?php
/**
 * Background mailer helper - queues emails for async sending
 * Uses simple file-based queue to avoid blocking page loads
 */

function mailer_queue_email(string $to, string $subject, string $html): bool
{
    $queueDir = __DIR__ . '/mail_queue';
    if (!is_dir($queueDir)) {
        @mkdir($queueDir, 0755, true);
    }
    
    $queueFile = $queueDir . '/' . uniqid('mail_', true) . '.json';
    $data = [
        'to' => $to,
        'subject' => $subject,
        'html' => $html,
        'queued_at' => date('Y-m-d H:i:s'),
    ];
    
    $written = @file_put_contents($queueFile, json_encode($data, JSON_PRETTY_PRINT));
    if ($written === false) {
        error_log('[mailer] Failed to queue email to ' . $to);
        return false;
    }
    
    // Trigger background processor (fire and forget)
    @exec('php ' . escapeshellarg(__DIR__ . '/mail_processor.php') . ' > /dev/null 2>&1 &');
    
    return true;
}

function mailer_send_async(string $to, string $subject, string $html): bool
{
    // Queue the email instead of sending directly
    return mailer_queue_email($to, $subject, $html);
}
