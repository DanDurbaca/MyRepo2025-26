<?php
/*
 * register.php
 * Purpose: Create new user accounts and persist them to the database.
 * Sections:
 *  - Includes: config for DB connection and session handling
 *  - POST handling: insert a new `user` record and hash the password
 *  - Renders: registration form
 */
require "includes/config.php";

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    // Basic validation
    if (strlen($username) < 3) {
        $error = "Username must be at least 3 characters";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT pk_username FROM user WHERE pk_username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Username already taken";
        } else {
            // Insert new user
            $stmt = $pdo->prepare("
                INSERT INTO user (pk_username, firstName, lastName, email, password)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $username,
                trim($_POST['firstName']),
                trim($_POST['lastName']),
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ]);

            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register – PIF</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center" style="min-height:100vh">

<div class="card p-4" style="width:400px">
    <h4 class="mb-3 text-center">Create account</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input class="form-control mb-2" name="username" placeholder="Username (min 3 chars)" required>
        <input class="form-control mb-2" name="firstName" placeholder="First name" required>
        <input class="form-control mb-2" name="lastName" placeholder="Last name" required>
        <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
        <input class="form-control mb-2" type="password" name="password" placeholder="Password (min 6 chars)" required minlength="6">
        <input class="form-control mb-3" type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button class="btn btn-primary w-100">Register</button>
    </form>
    
    <p class="mt-3 text-center">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>
