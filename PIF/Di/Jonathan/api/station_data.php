<?php
// Provides recent measurements for a station as JSON for dashboard/chart clients
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    global $host, $dbname, $username, $password;
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $station = $_GET['station'] ?? '685';
    $limit = min(200, intval($_GET['limit'] ?? 100));
    if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $station)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid station parameter']);
        exit;
    }

    $ownerStmt = $pdo->prepare('SELECT fk_user_owns FROM station WHERE pk_serialNumber = ?');
    $ownerStmt->execute([$station]);
    $ownerRow = $ownerStmt->fetch();
    if (!$ownerRow) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Station not found']);
        exit;
    }

    $owner = $ownerRow['fk_user_owns'];
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    $username = $_SESSION['username'];
    if (!$isAdmin && $owner !== $username) {
        $share = $pdo->prepare("SELECT 1 FROM station_share WHERE station_serial = ? AND shared_with = ? AND status = 'accepted'");
        $share->execute([$station, $username]);
        if (!$share->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Access denied']);
            exit;
        }
    }
    
    // Get measurements
    $sql = "SELECT temperature, humidity, pressure, light, gas, timestamp 
            FROM measurement 
            WHERE fk_station_records = :station 
            ORDER BY pk_measurement DESC 
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':station', $station);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rows = array_reverse($rows); // Oldest first for chart
    
    // Format for dashboard
    $measurements = array_map(function($r) {
        return [
            'timestamp' => $r['timestamp'],
            'temperature' => (float)$r['temperature'],
            'humidity' => (float)$r['humidity'],
            'pressure' => (float)$r['pressure'],
            'light' => (float)$r['light'],
            'air_quality' => (float)$r['gas']
        ];
    }, $rows);
    
    echo json_encode(['ok' => true, 'station' => $station, 'measurements' => $measurements]);
    
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>
