<?php
// controller/profile.php
// Controller for handling user profile updates (name and password)

require_once __DIR__ . '/../config/database.php';    // Database connection
require_once __DIR__ . '/../includes/auth_check.php'; // Ensure user is logged in

$conn = getDbConnection();        // Initialize PDO database connection
$username = $_SESSION['username']; // Current logged-in username

$success_message = ''; // Success feedback message for UI
$error_message   = ''; // Error feedback message for UI

// Handle POST actions (updating profile data)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? ''); // First name from form
    $lastName  = trim($_POST['lastName'] ?? '');  // Last name from form
    $password  = $_POST['password'] ?? '';        // Optional password update

    // Validate required fields
    if ($firstName === '' || $lastName === '') {
        $error_message = 'First name and last name are required.';
    } else {
        // Prepare parameters for SQL query
        $params = [
            ':firstName' => $firstName,
            ':lastName'  => $lastName,
            ':username'  => $username
        ];

        if ($password !== '') {
            // Update both name and password
            $stmt = $conn->prepare("
                UPDATE user
                SET firstName = :firstName,
                    lastName  = :lastName,
                    password  = :password
                WHERE pk_username = :username
            ");
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT); // Securely hash password
        } else {
            // Update only the name fields if password is empty
            $stmt = $conn->prepare("
                UPDATE user
                SET firstName = :firstName,
                    lastName  = :lastName
                WHERE pk_username = :username
            ");
        }

        $stmt->execute($params); // Execute the update query
        $success_message = 'Profile updated successfully.';
    }
}

// Fetch current user data from database to populate form
$stmt = $conn->prepare("
    SELECT pk_username, firstName, lastName
    FROM user
    WHERE pk_username = :username
");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Single user record as associative array

// Send data to view for rendering
$view_data = [
    'user' => $user,                     // User data for form pre-fill
    'success_message' => $success_message, // Feedback messages
    'error_message' => $error_message
];

require __DIR__ . '/../pages/profile_view.php'; // Load profile view page
?>