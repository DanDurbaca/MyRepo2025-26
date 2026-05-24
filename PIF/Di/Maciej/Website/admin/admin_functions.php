<?php
// admin/admin_functions.php
// Helper functions used only by admin pages
// These functions centralize admin-related logic so it is not duplicated

require_once __DIR__ . '/../config/database.php';   // Provides getDbConnection() and DB constants
require_once __DIR__ . '/../includes/functions.php';// Provides shared helpers like isAdmin()

/**
 * Ensure admin access
 * This function protects admin-only pages.
 * If the logged-in user is NOT an admin, they are redirected away.
 */
function checkAdminAccess() {
    if (!isAdmin()) {               // isAdmin() checks the user's role from the session
        header('Location: ../index.php'); // Redirect non-admin users to the homepage
        exit();                     // Stop script execution after redirect
    }
}


/**
 * Fetch all users
 * Used on admin pages that list or manage users.
 * Returns every row from the `user` table.
 */
function getAllUsers($conn) {
    // Prepare SQL to select all users ordered alphabetically
    $stmt = $conn->prepare("
        SELECT * 
        FROM user 
        ORDER BY firstName, lastName
    ");

    $stmt->execute();               // Execute the query
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Return all users as an associative array
}

/**
 * Fetch all stations with owner info
 * Uses LEFT JOIN so stations without owners are still included.
 * Useful for admin station management pages.
 */
function getAllStations($conn) {
    $stmt = $conn->prepare("
        SELECT 
            s.*,                                -- All station columns
            u.pk_username AS owner_username,   -- Owner username (if assigned)
            u.firstName,
            u.lastName
        FROM station s
        LEFT JOIN user u 
            ON s.fk_user_owns = u.pk_username  -- Link station to owning user
        ORDER BY s.name
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Each station includes owner info if available
}

/**
 * Fetch all collections with creator info
 * Uses INNER JOIN because every collection must have a creator.
 * Used on admin collection overview pages.
 */
function getAllCollections($conn) {
    $stmt = $conn->prepare("
        SELECT 
            c.*,                                -- All collection columns
            u.pk_username AS creator_username, -- Username of the creator
            u.firstName,
            u.lastName
        FROM collection c
        JOIN user u 
            ON c.fk_user_creates = u.pk_username
        ORDER BY c.name
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Return collections with creator details
}
?>