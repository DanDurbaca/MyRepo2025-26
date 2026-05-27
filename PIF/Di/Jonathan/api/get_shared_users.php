<?php
// Return list of users who have access to a collection (owner-only)
require_once '../config.php';
require_once '../inc/csrf.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$username = $_SESSION['username'];
$collection_id = $_GET['collection_id'] ?? '';

if (empty($collection_id) || !ctype_digit($collection_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid collection ID']);
    exit;
}

// Verify user owns the collection
$stmt = $pdo->prepare("SELECT pk_collection FROM collection WHERE pk_collection = ? AND fk_user_creates = ?");
$stmt->execute([$collection_id, $username]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'You do not own this collection']);
    exit;
}

// Get shared users
$stmt = $pdo->prepare("
    SELECT u.pk_username as username, u.firstName, u.lastName
    FROM hasaccess h
    JOIN user u ON h.pkfk_user = u.pk_username
    WHERE h.pkfk_collection = ?
    ORDER BY u.pk_username
");
$stmt->execute([$collection_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'users' => $users]);
?>