<?php
// controller/friends.php
// Handles friend management: adding, removing, rejecting requests, and fetching data for the friends page

require_once __DIR__ . '/../config/database.php'; // Database connection
require_once __DIR__ . '/../includes/auth_check.php'; // Ensure user is logged in

$conn = getDbConnection(); // Get PDO database connection
$username = $_SESSION['username'] ?? ''; // Current logged-in username
$success = ''; // Success feedback message
$error = '';   // Error feedback message

// Helper function: check if a user exists
function userExists($conn, $user) {
    $stmt = $conn->prepare("SELECT 1 FROM user WHERE pk_username = :u"); // Query for username
    $stmt->execute([':u' => $user]);
    return (bool)$stmt->fetch(); // Returns true if user exists
}

// Helper function: check if two users are already friends
function isAlreadyFriend($conn, $me, $them) {
    $stmt = $conn->prepare("SELECT 1 FROM isfriend WHERE pkfk_user_user = :me AND pkfk_user_friend = :them");
    $stmt->execute([':me'=>$me, ':them'=>$them]);
    return (bool)$stmt->fetch(); // True if friendship exists
}

// Handle form submissions for adding/removing/rejecting friends
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; // Action type from form
    $friendUsername = trim($_POST['friend_username'] ?? ''); // Target username

    if (!$friendUsername) {
        $error = "Please enter a username."; // Empty input
    } elseif ($friendUsername === $username) {
        $error = "You cannot add yourself as a friend."; // Prevent self-friend
    } else {
        switch ($action) {
            case 'add_friend':
                // Validate user exists and not already friend
                if (!userExists($conn, $friendUsername)) {
                    $error = "User '$friendUsername' does not exist.";
                } elseif (isAlreadyFriend($conn, $username, $friendUsername)) {
                    $error = "'$friendUsername' is already your friend.";
                } else {
                    // Insert friend request
                    $stmt = $conn->prepare("INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (:me, :them)");
                    $stmt->execute([':me'=>$username, ':them'=>$friendUsername]);
                    $success = "Friend request sent to '$friendUsername'.";
                }
                break;

            case 'reject_friend':
                // Delete incoming friend request
                $stmt = $conn->prepare("DELETE FROM isfriend WHERE pkfk_user_user = :them AND pkfk_user_friend = :me");
                $stmt->execute([':them'=>$friendUsername, ':me'=>$username]);
                $success = "Friend request from '$friendUsername' rejected.";
                break;

            case 'remove_friend':
                // Remove friendship in either direction
                $stmt = $conn->prepare("
                    DELETE FROM isfriend
                    WHERE (pkfk_user_user = :me AND pkfk_user_friend = :them)
                       OR (pkfk_user_user = :them AND pkfk_user_friend = :me)
                ");
                $stmt->execute([':me'=>$username, ':them'=>$friendUsername]);
                $success = "Friend removed.";
                break;
        }
    }
}

// Fetch mutual friends: users where friendship exists in both directions
$stmt = $conn->prepare("
    SELECT u.pk_username, u.firstName, u.lastName, u.email
    FROM user u
    JOIN isfriend f1 ON u.pk_username = f1.pkfk_user_friend
    JOIN isfriend f2 ON f2.pkfk_user_user = f1.pkfk_user_friend
                   AND f2.pkfk_user_friend = f1.pkfk_user_user
    WHERE f1.pkfk_user_user = :me
");
$stmt->execute([':me'=>$username]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC); // Array of current friends

// Fetch pending friend requests: users who added current user but are not yet friends
$stmt = $conn->prepare("
    SELECT u.pk_username, u.firstName, u.lastName
    FROM user u
    JOIN isfriend f ON u.pk_username = f.pkfk_user_user
    WHERE f.pkfk_user_friend = :me
      AND NOT EXISTS (
          SELECT 1 FROM isfriend WHERE pkfk_user_user = :me AND pkfk_user_friend = u.pk_username
      )
");
$stmt->execute([':me'=>$username]);
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC); // Array of incoming friend requests

// Prepare data to send to the view
$view_data = [
    'friends' => $friends,                  // Current friends
    'pending_requests' => $pending_requests, // Incoming friend requests
    'success_message' => $success,           // Any success messages
    'error_message'   => $error,             // Any error messages
];

// Load the view that renders the friends page
require __DIR__ . '/../pages/friends_view.php';
?>