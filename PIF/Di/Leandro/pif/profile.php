<?php
/*
 * profile.php
 * Purpose: Allow the logged-in user to view and update their profile information.
 * Sections:
 *  - Includes: config and auth check to ensure the user is logged in
 *  - POST handling: update user details in the database
 *  - Data fetching: load current user data and render a simple profile form
 */
require "includes/config.php";
require "includes/auth_check.php";

$message = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
    } else {
        $stmt = $pdo->prepare("
            UPDATE user
            SET firstName=?, lastName=?, email=?
            WHERE pk_username=?
        ");
        $stmt->execute([
            trim($_POST['firstName']),
            trim($_POST['lastName']),
            $email,
            $_SESSION['username']
        ]);
        $message = "Profile updated successfully!";
    }
}

$stmt = $pdo->prepare("SELECT * FROM user WHERE pk_username=?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - PIF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>
<body>
<?php include "includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>My Profile</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4" style="max-width: 500px;">
        <div class="mb-3">
            <label class="form-label">First Name</label>
            <input class="form-control" name="firstName" value="<?= htmlspecialchars($user['firstName']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input class="form-control" name="lastName" value="<?= htmlspecialchars($user['lastName']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <button class="btn btn-primary">Save Changes</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>
