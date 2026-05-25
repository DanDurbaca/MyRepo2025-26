<?php
/**
 * Background mail processor - processes queued emails
 * Runs as a separate process to avoid blocking web requests
 */

require_once __DIR__ . '/mailer.php';

$queueDir = __DIR__ . '/mail_queue';
if (!is_dir($queueDir)) {
    exit(0);
}

$files = glob($queueDir . '/mail_*.json');
if (empty($files)) {
    exit(0);
}

// Process up to 10 emails per run to avoid long-running processes
$processed = 0;
foreach ($files as $file) {
    if ($processed >= 10) {
        break;
    }
    
    $data = @json_decode(file_get_contents($file), true);
    if (!$data || !isset($data['to'], $data['subject'], $data['html'])) {
        @unlink($file);
        continue;
    }
    
    // Send the email
    $success = mailer_send($data['to'], $data['subject'], $data['html']);
    
    if ($success) {
        // Remove from queue
        @unlink($file);
        $processed++;
    } else {
        // Check age - if older than 1 hour, remove it (failed permanently)
        $age = time() - filemtime($file);
        if ($age > 3600) {
            error_log('[mailer_processor] Removing old failed email: ' . $file);
            @unlink($file);
        }
        // Otherwise leave it for retry on next run
    }
}

exit(0);
