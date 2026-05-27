<?php
// Lists recent webhook events received from SMTP provider (bounces, deliveries) for auditing
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../config.php';

try {
    $stmt = $pdo->query('SELECT id, message_id, event, payload, received_at FROM email_events ORDER BY received_at DESC LIMIT 200');
    $events = $stmt->fetchAll();
} catch (Exception $e) {
    $events = [];
}
?>
<main>
    <h2>Email Events (Webhook)</h2>
    <p>This page lists recent webhook events received from the SMTP provider (bounces, delivered, complaints, etc.).</p>
    <div class="table-responsive">
    <table>
        <thead><tr><th>ID</th><th>Message ID</th><th>Event</th><th>Payload</th><th>Received At</th></tr></thead>
        <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td><?php echo htmlspecialchars($e['id']); ?></td>
                    <td><?php echo htmlspecialchars($e['message_id']); ?></td>
                    <td><?php echo htmlspecialchars($e['event']); ?></td>
                    <td><pre style="max-width:800px; white-space:pre-wrap"><?php echo htmlspecialchars($e['payload']); ?></pre></td>
                    <td><?php echo htmlspecialchars($e['received_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</main>
