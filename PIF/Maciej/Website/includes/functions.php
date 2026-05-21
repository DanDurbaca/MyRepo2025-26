<?php
// includes/functions.php
// General helper functions used across the application


// Database connection
// Include the database configuration file to access $conn (PDO object)
require_once __DIR__ . '/../config/database.php';


// Authentication helpers
// Check if the current user has Admin role
function isAdmin() {
    // Returns true if session role is set to 'Admin', otherwise false
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}


// Collection & Station helpers
// Fetch all stations for a user
function getUserStations($conn, $username, $is_admin = false) {
    if ($is_admin) {
        // Admin: fetch all stations in the system, no user filter
        $stmt = $conn->prepare("SELECT pk_serialNumber, name FROM station ORDER BY name");
        $stmt->execute();
    } else {
        // Regular user: fetch only stations owned by this user
        $stmt = $conn->prepare("SELECT pk_serialNumber, name FROM station WHERE fk_user_owns = :username ORDER BY name");
        $stmt->execute([':username' => $username]);
    }

    // Return all matching stations as associative array
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch collections created by the user (or all if admin)
function getUserCollections($conn, $username, $is_admin = false) {
    if ($is_admin) {
        // Admin: get all collections with creator info and measurement count
        $stmt = $conn->prepare("
            SELECT c.*, u.pk_username AS creator_username, u.firstName, u.lastName,
                   COUNT(ct.pkfk_measurement) AS measurement_count
            FROM collection c
            JOIN user u ON c.fk_user_creates = u.pk_username
            LEFT JOIN contains ct ON ct.pkfk_collection = c.pk_collection
            GROUP BY c.pk_collection
            ORDER BY c.name
        ");
        $stmt->execute();
    } else {
        // Regular user: get only collections created by this user
        $stmt = $conn->prepare("
            SELECT c.*, COUNT(ct.pkfk_measurement) AS measurement_count
            FROM collection c
            LEFT JOIN contains ct ON ct.pkfk_collection = c.pk_collection
            WHERE c.fk_user_creates = :username
            GROUP BY c.pk_collection
            ORDER BY c.name
        ");
        $stmt->execute([':username' => $username]);
    }

    // Return collections with their measurement counts
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch collections that have been shared with the user
function getSharedCollections($conn, $username) {
    $stmt = $conn->prepare("
        SELECT c.*, u.pk_username AS creator_username, u.firstName, u.lastName,
               ANY_VALUE(s.name) AS station_name,
               ANY_VALUE(s.pk_serialNumber) AS station_sn,
               COUNT(ct.pkfk_measurement) AS measurement_count
        FROM collection c
        JOIN user u ON c.fk_user_creates = u.pk_username
        JOIN hasaccess ha ON c.pk_collection = ha.pkfk_collection
        LEFT JOIN contains ct ON ct.pkfk_collection = c.pk_collection
        LEFT JOIN station s ON (
            SELECT fk_station_records 
            FROM measurement m
            JOIN contains ct2 ON m.pk_measurement = ct2.pkfk_measurement
            WHERE ct2.pkfk_collection = c.pk_collection
            LIMIT 1
        ) = s.pk_serialNumber
        WHERE ha.pkfk_user = :username
        GROUP BY c.pk_collection
        ORDER BY c.name
    ");
    $stmt->execute([':username' => $username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Friends helpers
// Get all friends of a given user
function getUserFriends($conn, $username) {
    $stmt = $conn->prepare("
        SELECT u.pk_username, u.firstName, u.lastName
        FROM user u
        JOIN isfriend f ON u.pk_username = f.pkfk_user_friend
        WHERE f.pkfk_user_user = :username
        ORDER BY u.firstName, u.lastName
    ");
    $stmt->execute([':username' => $username]);

    // Returns an array of users who are friends with this user
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all users that are not friends with the current user
function getNonFriends($conn, $username) {
    $stmt = $conn->prepare("
        SELECT pk_username, firstName, lastName
        FROM user
        WHERE pk_username != :username
          AND pk_username NOT IN (
              SELECT pkfk_user_friend FROM isfriend WHERE pkfk_user_user = :username
          )
        ORDER BY firstName, lastName
    ");
    $stmt->execute([':username' => $username]);

    // Returns users that can be added as new friends
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
