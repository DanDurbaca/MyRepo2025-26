<?php
// admin/users.php
// Controller for managing users: handles create, update, delete actions

// Include database connection and authentication check
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Only allow Admin users; redirect others
if (($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

// Connect to the database
$conn = getDbConnection();

// Initialize message variables
$success = '';
$error = '';

// Fetch all users from the database, ordered by username
$users = $conn->query(
    "SELECT pk_username, firstName, lastName, email, role
     FROM user
     ORDER BY pk_username"
)->fetchAll();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create a new user
    if ($action === 'create') {
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            // Insert the new user into the database
            $stmt = $conn->prepare(
                "INSERT INTO user (pk_username, password, firstName, lastName, email, role)
                 VALUES (:u, :p, :f, :l, :e, :r)"
            );
            $stmt->execute([
                'u' => $_POST['username'],
                'p' => $_POST['password'], // stored as plain text per requirements
                'f' => $_POST['firstName'] ?? null,
                'l' => $_POST['lastName'] ?? null,
                'e' => $_POST['email'] ?? null,
                'r' => $_POST['role']
            ]);
            $success = "User created.";
        } else {
            $error = "Username and password required.";
        }
    }

    // Update an existing user
    if ($action === 'update') {
        // Update user fields except password
        $stmt = $conn->prepare(
            "UPDATE user
             SET firstName = :f,
                 lastName = :l,
                 email = :e,
                 role = :r
             WHERE pk_username = :u"
        );
        $stmt->execute([
            'f' => $_POST['firstName'],
            'l' => $_POST['lastName'],
            'e' => $_POST['email'],
            'r' => $_POST['role'],
            'u' => $_POST['username']
        ]);
        $success = "User updated.";
    }

    // Delete a user
    if ($action === 'delete') {
        $stmt = $conn->prepare(
            "DELETE FROM user WHERE pk_username = :u"
        );
        $stmt->execute(['u' => $_POST['username']]);
        $success = "User deleted.";
    }

    // Redirect after form submission to prevent duplicate POST
    header("Location: users.php");
    exit;
}

// Load the view that displays the user management page
require __DIR__ . '/users_view.php';
?>