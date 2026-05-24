<?php
// signup.php
// User registration controller

// Session start
session_start(); // Start PHP session to store user login info

// Database connection
require_once __DIR__ . '/config/database.php'; // Provides getDbConnection()

// Error message holder
$error = ''; // Stores any validation or database errors


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and sanitize form inputs
    $username  = trim($_POST['username'] ?? '');   // Remove extra spaces
    $password  = trim($_POST['password'] ?? '');
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    // Validate required fields
    if ($username && $password && $firstName && $lastName && $email) {

        // Open database connection
        $conn = getDbConnection();

        // Check if username already exists
        $stmt = $conn->prepare("SELECT pk_username FROM user WHERE pk_username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Username already exists"; // Display error if duplicate
        } else {

            // Insert new user (plain text password)
            $stmt = $conn->prepare(
                "INSERT INTO user (pk_username, firstName, lastName, password, email, role)
                 VALUES (?, ?, ?, ?, ?, 'User')"
            );

            if ($stmt->execute([$username, $firstName, $lastName, $password, $email])) {
                header('Location: login.php'); // Redirect to login on success
                exit;
            } else {
                $error = "Failed to create account"; // DB insertion failed
            }
        }

    } else {
        $error = "Please fill in all fields"; // Validation failed
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Portable Indoor Feedback</title>

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
<div class="container">

    <!-- Page title -->
    <h1>Sign Up</h1>

    <!-- Error display -->
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p> <!-- Sanitize output -->
    <?php endif; ?>

    <!-- Sign up form -->
    <form method="post" action="">

        <label>First Name</label><br>
        <input type="text" name="firstName" required><br><br>

        <label>Last Name</label><br>
        <input type="text" name="lastName" required><br><br>

        <label>Email</label><br>
        <input type="email" name="email" required><br><br>

        <label>Username</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Sign Up</button>
    </form>

    <!-- Login link -->
    <p>Already have an account? <a href="login.php">Login here</a></p>

</div>
</body>
</html>