<?php
/*
 * login.php
 * Purpose: Authenticate users and establish session variables.
 * Sections:
 *  - Includes: database/config (starts session)
 *  - POST handling: fetch user, verify password, set `$_SESSION['username']` and `$_SESSION['role']`
 *  - Renders: HTML login form and error messages
 */
require "includes/config.php";

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE pk_username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['username'] = $user['pk_username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login – PIF</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center" style="min-height:100vh">

<div class="card p-4" style="width:350px">
    <h4 class="mb-3 text-center">Login</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <input class="form-control mb-2" name="username" placeholder="Username" required>
        <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
        <button class="btn btn-primary w-100">Login</button>
    </form>

    <p class="mt-3 text-center">
        No account? <a href="register.php">Register</a>
    </p>
</div>

</body>
</html>
