<?php
// Admin action: purge `secret_audit` rows and optionally old measurements older than specified days
require_once __DIR__ . '/_header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = 'POST required for purge.';
    header('Location: secret_audit.php');
    exit;
}

if (!validate_csrf($_POST['csrf_token'] ?? '')) {
    $_SESSION['flash'] = 'Invalid CSRF token.';
    header('Location: secret_audit.php');
    exit;
}

$days = intval($_POST['days'] ?? 0);
$purge_measurements = !empty($_POST['purge_measurements']);
if ($days <= 0) {
    $_SESSION['flash'] = 'Please provide a positive number of days.';
    header('Location: secret_audit.php');
    exit;
}

$cutoff = date('Y-m-d H:i:s', time() - ($days * 24 * 3600));

try {
    $pdo->beginTransaction();
    // purge secret_audit rows older than cutoff
    $stmt = $pdo->prepare('DELETE FROM secret_audit WHERE generated_at < ?');
    $stmt->execute([$cutoff]);
    $deleted = $stmt->rowCount();

    if ($purge_measurements) {
        // caution: this deletes measurement rows older than cutoff
        $stmt2 = $pdo->prepare('DELETE FROM measurement WHERE timestamp < ?');
        $stmt2->execute([$cutoff]);
        $deleted_meas = $stmt2->rowCount();
    } else {
        $deleted_meas = 0;
    }

    $pdo->commit();
    $_SESSION['flash'] = 'Purge completed. Audits deleted: ' . $deleted . '; Measurements deleted: ' . $deleted_meas;
    header('Location: secret_audit.php');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Purge failed: ' . $e->getMessage());
    $_SESSION['flash'] = 'Purge failed. See server logs.';
    header('Location: secret_audit.php');
    exit;
}

?>
