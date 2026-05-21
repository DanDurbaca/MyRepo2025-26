<?php
// controller/collections.php
// Controller for managing user collections (create, share, rename, delete)

require_once __DIR__ . '/../config/database.php';    // Database connection
require_once __DIR__ . '/../includes/auth_check.php'; // Ensure user is logged in
require_once __DIR__ . '/../includes/functions.php';  // Helper functions

$conn = getDbConnection();          // Initialize PDO connection
$username = $_SESSION['username'];  // Current logged-in username
$is_admin = isAdmin();              // Check if user has admin privileges

$success_message = ''; // Container for success feedback
$error_message = '';   // Container for error feedback

// Fetch user's stations and collections from database
$user_stations = getUserStations($conn, $username);  // Stations owned by the user
$collections = getUserCollections($conn, $username); // Collections created by the user

// Fetch user's friends for collection sharing
$stmt = $conn->prepare("
    SELECT u.pk_username, u.firstName, u.lastName
    FROM user u
    JOIN isfriend f ON u.pk_username = f.pkfk_user_friend
    WHERE f.pkfk_user_user = :username
    ORDER BY u.firstName, u.lastName
");
$stmt->execute([':username' => $username]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC); // Array of friend users

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; // Determine which action is being performed

    /* CREATE COLLECTION */
    if ($action === 'create_collection') {
        $name = trim($_POST['collection_name'] ?? ''); // Collection name from form
        $desc = trim($_POST['description'] ?? '');    // Optional description
        $start_dt = $_POST['start_datetime'] ?? '';   // Start date/time for collection
        $end_dt = $_POST['end_datetime'] ?? '';       // End date/time for collection

        if ($name && $start_dt && $end_dt) {
            // Insert new collection into database
            $stmt = $conn->prepare("
                INSERT INTO collection (name, description, fk_user_creates)
                VALUES (:name, :desc, :creator)
            ");
            $stmt->execute([
                ':name' => $name,
                ':desc' => $desc,
                ':creator' => $username
            ]);
            $success_message = "Collection '$name' created.";
        } else {
            $error_message = "All required fields must be filled.";
        }
    }

    /* SHARE COLLECTION */
    if ($action === 'share_collection') {
        $friendUsername = $_POST['friend_username'] ?? ''; // Friend to share with
        $collectionId = $_POST['collection_id'] ?? '';     // Collection ID

        if (!$friendUsername || !$collectionId) {
            $error_message = "Friend and collection must be selected.";
        } else {
            // Check if the collection is already shared with this friend
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM hasaccess 
                WHERE pkfk_collection = :id AND pkfk_user = :user
            ");
            $stmt->execute([':id' => $collectionId, ':user' => $friendUsername]);
            
            if ($stmt->fetchColumn()) {
                $error_message = "Collection already shared with $friendUsername.";
            } else {
                // Insert new share record
                $stmt = $conn->prepare("
                    INSERT INTO hasaccess (pkfk_collection, pkfk_user) 
                    VALUES (:id, :user)
                ");
                $stmt->execute([':id' => $collectionId, ':user' => $friendUsername]);
                $success_message = "Collection shared with $friendUsername.";
            }
        }
    }

    /* RENAME COLLECTION */
    if ($action === 'rename_collection') {
        $collectionId = $_POST['collection_id'] ?? '';
        $newName = trim($_POST['new_name'] ?? '');
        if ($collectionId && $newName) {
            // Update collection name in database
            $stmt = $conn->prepare("UPDATE collection SET name = :name WHERE pk_collection = :id");
            $stmt->execute([':name' => $newName, ':id' => $collectionId]);
            $success_message = "Collection renamed to '$newName'.";
        }
    }

    /* DELETE COLLECTION */
    if ($action === 'delete_collection') {
        $collectionId = $_POST['collection_id'] ?? '';
        if ($collectionId) {
            // Delete the collection from database
            $stmt = $conn->prepare("DELETE FROM collection WHERE pk_collection = :id");
            $stmt->execute([':id' => $collectionId]);
            $success_message = "Collection deleted.";
        }
    }

    // Refresh collections after any POST action to show latest data
    $collections = getUserCollections($conn, $username);
}

// Prepare data for the view
$view_data = compact(
    'username',         // Current user
    'is_admin',         // Admin flag
    'collections',      // User's collections
    'user_stations',    // User's stations
    'friends',          // Friends list for sharing
    'success_message',  // Success feedback for UI
    'error_message'     // Error feedback for UI
);

// Load the collections view page
require __DIR__ . '/../pages/collections_view.php';
?>