<?php
// Returns the full secret for a station (admin only). Expects POST with csrf_token and station_id.
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/csrf.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf($token)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$serial = $_POST['station_id'] ?? '';
if (!$serial) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing station_id']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT secret FROM station WHERE pk_serialNumber = ?');
    $stmt->execute([$serial]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Station not found']);
        exit;
    }

    // Enforce show-once policy: only block reveal if a 'revealed' audit exists AFTER the most recent 'generated' audit.
    // Find latest generated timestamp (if any)
    $g = $pdo->prepare("SELECT generated_at FROM secret_audit WHERE pkfk_station = ? AND event = 'generated' ORDER BY generated_at DESC LIMIT 1");
    $g->execute([$serial]);
    $ga = $g->fetch();
    $lastGeneratedAt = $ga['generated_at'] ?? null;

    if ($lastGeneratedAt) {
        // Check if any 'revealed' event exists after the last generation
        $chk = $pdo->prepare("SELECT id FROM secret_audit WHERE pkfk_station = ? AND event = 'revealed' AND generated_at > ? LIMIT 1");
        $chk->execute([$serial, $lastGeneratedAt]);
    } else {
        // No generated record found; fall back to any revealed event
        $chk = $pdo->prepare("SELECT id FROM secret_audit WHERE pkfk_station = ? AND event = 'revealed' LIMIT 1");
        $chk->execute([$serial]);
    }
    $already = $chk->fetch();
    if ($already) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Secret already revealed previously for this station']);
        exit;
    }

    // Ensure a secret exists
    if (empty($row['secret'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'No secret set for this station']);
        exit;
    }

    // Record reveal event in audit table and return secret
    $audit = $pdo->prepare("INSERT INTO secret_audit (pkfk_station, generated_by, event, meta) VALUES (?, ?, 'revealed', ?)");
    $meta = json_encode(['ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'admin' => $_SESSION['username'] ?? 'unknown']);
    $audit->execute([$serial, $_SESSION['username'] ?? 'unknown', $meta]);

    echo json_encode(['ok' => true, 'secret' => $row['secret']]);
} catch (PDOException $e) {
    error_log('get_secret error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}

?>
