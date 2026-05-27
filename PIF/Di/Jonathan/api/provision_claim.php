<?php
// Claim a provisioned station token for authenticated users; grants ownership if unclaimed
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // fallback to form:
    $data = $_POST;
}

$serial = trim($data['serial'] ?? '');
$token = trim($data['token'] ?? '');
if ($serial === '' || $token === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing serial or token']);
    exit;
}

try {
    $hashed = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id, expires_at FROM station_provision WHERE pkfk_station = ? AND token = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$serial, $hashed]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Invalid token']);
        exit;
    }
    if (new DateTimeImmutable($row['expires_at']) < new DateTimeImmutable('now')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Token expired']);
        exit;
    }
    // claim station if unowned
    $claim = $pdo->prepare('UPDATE station SET fk_user_owns = ? WHERE pk_serialNumber = ? AND fk_user_owns IS NULL');
    $claim->execute([$_SESSION['username'], $serial]);
    if ($claim->rowCount() === 0) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'Station already owned or claim failed']);
        exit;
    }
    // mark provision row as used (delete)
    $pdo->prepare('DELETE FROM station_provision WHERE id = ?')->execute([$row['id']]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    error_log('provision_claim error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}

?>
