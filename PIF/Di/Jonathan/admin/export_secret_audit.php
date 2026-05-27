<?php
// Export secret_audit rows as CSV using supplied filters (admin)
require_once __DIR__ . '/_header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = 'POST required for export.';
    header('Location: secret_audit.php');
    exit;
}

if (!validate_csrf($_POST['csrf_token'] ?? '')) {
    $_SESSION['flash'] = 'Invalid CSRF token.';
    header('Location: secret_audit.php');
    exit;
}

$station = trim($_POST['station'] ?? '');
$event = trim($_POST['event'] ?? '');
$from = trim($_POST['from'] ?? '');
$to = trim($_POST['to'] ?? '');

// Build query
$where = [];
$params = [];
if ($station !== '') { $where[] = 'pkfk_station = ?'; $params[] = $station; }
if ($event !== '') { $where[] = 'event = ?'; $params[] = $event; }
if ($from !== '') { $where[] = 'generated_at >= ?'; $params[] = $from; }
if ($to !== '') { $where[] = 'generated_at <= ?'; $params[] = $to; }
$sql = 'SELECT id, pkfk_station, generated_by, event, generated_at, meta FROM secret_audit';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY generated_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Output CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="secret_audit_export.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['id','station','generated_by','event','generated_at','meta']);
while ($row = $stmt->fetch()) {
    fputcsv($out, [$row['id'],$row['pkfk_station'],$row['generated_by'],$row['event'],$row['generated_at'],$row['meta']]);
}
fclose($out);
exit;
