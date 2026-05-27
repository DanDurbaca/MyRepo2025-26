<?php
// Simple webhook receiver for Brevo (Sendinblue) transactional webhook events
require_once __DIR__ . '/../config.php';

// Accept JSON payload
$body = file_get_contents('php://input');
if (!$body) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'empty payload']);
    exit;
}

$json = json_decode($body, true);
if (!is_array($json)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid json']);
    exit;
}

// Brevo may POST an array of events; normalize to array
$events = isset($json[0]) ? $json : [$json];

try {
    require_once __DIR__ . '/../inc/log.php';
    foreach ($events as $ev) {
        $msgid = $ev['messageId'] ?? ($ev['message-id'] ?? null);
        $eventName = $ev['event'] ?? ($ev['status'] ?? 'unknown');
        $payload = json_encode($ev, JSON_UNESCAPED_SLASHES);
        // Persist into DB
        $stmt = $pdo->prepare('INSERT INTO email_events (message_id, event, payload) VALUES (?, ?, ?)');
        $stmt->execute([$msgid, $eventName, $payload]);
        app_log('info', 'email_webhook_received', ['message_id' => $msgid, 'event' => $eventName]);
    }
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    error_log('email_webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server error']);
}
