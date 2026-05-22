<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_measurement':
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("
            SELECT m.*, s.name as station_name 
            FROM measurement m
            JOIN station s ON m.fk_station_records = s.pk_serialNumber
            WHERE m.pk_measurement = ?
        ");
        $stmt->execute([$id]);
        $measurement = $stmt->fetch();
        
        if ($measurement) {
            // Check permission
            if (!isAdmin()) {
                $stmt = $pdo->prepare("SELECT fk_user_owns FROM station WHERE pk_serialNumber = ?");
                $stmt->execute([$measurement['fk_station_records']]);
                $station = $stmt->fetch();
                
                if ($station['fk_user_owns'] !== $_SESSION['username']) {
                    echo json_encode(['success' => false, 'message' => 'Access denied']);
                    exit;
                }
            }
            
            echo json_encode(['success' => true, 'data' => $measurement]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Measurement not found']);
        }
        break;
        
    case 'get_collection':
        $id = (int)$_GET['id'];
        
        // Get collection info
        $stmt = $pdo->prepare("
            SELECT c.*, u.firstName, u.lastName 
            FROM collection c
            JOIN user u ON c.fk_user_creates = u.pk_username
            WHERE c.pk_collection = ?
        ");
        $stmt->execute([$id]);
        $collection = $stmt->fetch();
        
        if (!$collection) {
            echo json_encode(['success' => false, 'message' => 'Collection not found']);
            exit;
        }
        
        // Check permission
        if (!isAdmin() && $collection['fk_user_creates'] !== $_SESSION['username']) {
            // Check if shared with user
            $stmt = $pdo->prepare("SELECT 1 FROM hasaccess WHERE pkfk_collection = ? AND pkfk_user = ?");
            $stmt->execute([$id, $_SESSION['username']]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
        }
        
        // Get collection measurements
        $stmt = $pdo->prepare("
            SELECT m.* 
            FROM measurement m
            JOIN contains con ON m.pk_measurement = con.pkfk_measurement
            WHERE con.pkfk_collection = ?
            ORDER BY m.timestamp
        ");
        $stmt->execute([$id]);
        $measurements = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'collection' => [
                'name' => $collection['name'],
                'description' => $collection['description'],
                'creator' => $collection['firstName'] . ' ' . $collection['lastName']
            ],
            'measurements' => $measurements
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>