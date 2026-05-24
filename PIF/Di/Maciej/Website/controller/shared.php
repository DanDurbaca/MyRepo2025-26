<?php
// controller/shared.php
// Controller for handling collections that have been shared with the user

require_once __DIR__ . '/../config/database.php';   // Include DB connection setup
require_once __DIR__ . '/../includes/auth_check.php'; // Ensure user is logged in
require_once __DIR__ . '/../includes/functions.php';  // Include helper functions

$conn = getDbConnection();   // Initialize PDO database connection
$username = $_SESSION['username']; // Current logged-in user
$is_admin = isAdmin();      // Check if current user is admin

$success_message = ''; // Success feedback for UI
$error_message   = ''; // Error feedback for UI

// Fetch all collections shared with this user
$shared_collections = getSharedCollections($conn, $username);

// Handle POST actions (e.g., removing a shared collection)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; // Determine action type from form

    if ($action === 'unshare_collection') {
        $collection_id = $_POST['collection_id'] ?? ''; // Collection ID to remove
        if (!$collection_id) {
            $error_message = "Collection ID required.";
        } else {
            try {
                // Remove access for the current user
                $stmt = $conn->prepare("
                    DELETE FROM hasaccess 
                    WHERE pkfk_collection = :id AND pkfk_user = :user
                ");
                $stmt->execute([':id'=>$collection_id, ':user'=>$username]);

                $success_message = "Collection removed from your shared list.";

                // Refresh the shared collections after deletion
                $shared_collections = getSharedCollections($conn, $username);
            } catch (PDOException $e) {
                // Capture DB errors for display
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Pass data to the view for rendering
$view_data = compact('username','is_admin','shared_collections','success_message','error_message');
require __DIR__ . '/../pages/shared_view.php'; // Load the shared collections view
?>