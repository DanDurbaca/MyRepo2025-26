<?php
// login.php
// User login controller

// Start session
session_start(); // Initialize PHP session to store login info

// Database connection
require_once __DIR__ . '/config/database.php'; // Provides getDbConnection()

// Error message holder
$error = ''; // Store login validation errors


// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve login inputs from form
    $username = $_POST['username'] ?? ''; // Username entered by user
    $password = $_POST['password'] ?? ''; // Password entered by user

    // Validate inputs
    if ($username && $password) {

        // Open database connection
        $conn = getDbConnection();

        // Fetch user record by username
        $stmt = $conn->prepare(
            "SELECT pk_username, password, role, firstName, theme
             FROM user
             WHERE pk_username = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

        $_SESSION['theme'] = $user['theme'] ?? 'light'; // Store user theme preference in session (default to 'light' if not set)


        // Check password (plain text comparison)
        if ($user && $user['password'] === $password) {

            // Store user session data
            $_SESSION['username']  = $user['pk_username']; // Store username in session
            $_SESSION['role']      = $user['role'];        // Store role (Admin/User)
            $_SESSION['firstName'] = $user['firstName'];   // Store first name

            // Redirect based on user role
            if ($user['role'] === 'Admin') {
                header('Location: admin/dashboard.php'); // Admin dashboard
            } else {
                header('Location: controller/dashboard.php'); // Regular user dashboard
            }
            exit;

        } else {
            $error = 'Invalid username or password'; // Authentication failed
        }

    } else {
        $error = 'Please enter username and password'; // Form validation failed
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Portable Indoor Feedback</title>

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
<div class="container card">
    <h1>Portable Indoor Feedback</h1>
    <p>Please log in</p>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="username">Username</label><br>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account?</p>
        <a href="signup.php" class="signup-link">Create an account</a>
    </div>
</div>
</body>
</html>