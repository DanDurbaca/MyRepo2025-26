<?php
// controller/measurements_readonly.php
// Read-only controller for viewing measurements inside a collection
// Access allowed ONLY if:
// - user is the creator OR
// - collection is shared with the user

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$conn = getDbConnection();
$username = $_SESSION['username'] ?? '';

// Get collection_id from POST (AJAX) or GET (link)
$collection_id = (int)($_POST['collection_id'] ?? $_GET['collection_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;

if ($collection_id <= 0) {
    die('Invalid collection.');
}

// Fetch collection info
$stmt = $conn->prepare("
    SELECT 
        c.pk_collection,
        c.name,
        c.description,
        c.fk_user_creates AS creator_username,
        CONCAT(u.firstName, ' ', u.lastName) AS creator_name
    FROM collection c
    JOIN user u ON u.pk_username = c.fk_user_creates
    WHERE c.pk_collection = :cid
");
$stmt->execute(['cid' => $collection_id]);
$collection = $stmt->fetch();

if (!$collection) {
    die('Collection not found.');
}

$is_creator = ($collection['creator_username'] === $username);

// Check shared access if not creator
if (!$is_creator) {
    $stmt = $conn->prepare("
        SELECT 1
        FROM hasaccess
        WHERE pkfk_collection = :cid AND pkfk_user = :user
    ");
    $stmt->execute([
        'cid'  => $collection_id,
        'user' => $username
    ]);
    if (!$stmt->fetch()) die('You do not have access.');
}

// Count measurements
$stmt = $conn->prepare("SELECT COUNT(*) FROM contains WHERE pkfk_collection = :cid");
$stmt->execute(['cid' => $collection_id]);
$total_measurements = (int)$stmt->fetchColumn();
$total_pages = max(1, ceil($total_measurements / $per_page));

// Fetch measurements for current page
$stmt = $conn->prepare("
    SELECT 
        m.timestamp,
        m.temperature,
        m.humidity,
        m.pressure,
        m.light,
        m.gas,
        s.name AS station_name
    FROM measurement m
    JOIN contains c ON c.pkfk_measurement = m.pk_measurement
    JOIN station s ON s.pk_serialNumber = m.fk_station_records
    WHERE c.pkfk_collection = :cid
    ORDER BY m.timestamp DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':cid', $collection_id, PDO::PARAM_INT); // Collection ID
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);  // Items per page
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);   // Offset for pagination
$stmt->execute(); // Execute the query
$measurements = $stmt->fetchAll(); // Fetch all measurements for the page

// Pass data to view
$view_data = [
    'collection'         => $collection,
    'measurements'       => $measurements,
    'page'               => $page,
    'total_pages'        => $total_pages,
    'is_creator'         => $is_creator,
    'total_measurements' => $total_measurements
];

// Load the view
require __DIR__ . '/../pages/measurements_readonly_view.php';
?>