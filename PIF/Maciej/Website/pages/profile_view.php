<?php
// pages/profile_view.php
// User profile view page

// Extract user data passed from controller
$user = $view_data['user'] ?? [];                   // Current user information
$success_message = $view_data['success_message'] ?? ''; // Success message after update
$error_message   = $view_data['error_message'] ?? '';   // Error message if any
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="/PIF/Website/assets/css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../includes/header.php'; ?> <!-- Include header/navigation -->

<main class="container">

    <!-- Page heading -->
    <h1>My Profile</h1>

    <!-- Success and error messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Profile update form -->
    <form method="post">

        <!-- Username (readonly) -->
        <label>
            Username
            <input type="text" value="<?= htmlspecialchars($user['pk_username']) ?>" disabled>
        </label>

        <!-- First name -->
        <label>
            First Name
            <input type="text" name="firstName"
                   value="<?= htmlspecialchars($user['firstName']) ?>" required>
        </label>

        <!-- Last name -->
        <label>
            Last Name
            <input type="text" name="lastName"
                   value="<?= htmlspecialchars($user['lastName']) ?>" required>
        </label>

        <!-- Password (optional) -->
        <label>
            New Password (leave blank to keep current)
            <input type="password" name="password">
        </label>

        <!-- Submit button -->
        <button type="submit" class="btn btn-primary btn-lg mt-2">
            Update Profile
        </button>
    </form>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> <!-- Include footer -->
</body>
</html>