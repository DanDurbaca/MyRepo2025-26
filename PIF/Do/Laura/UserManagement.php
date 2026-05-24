<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - User Management</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
<?php
// Load shared utilities and navigation
include 'CommonCode.php';
NavigationBar1("admin");
// Restrict page access to admins only
requireAdmin();

// Initialize message state and action type
$message = "";
$action = $_POST['action'] ?? '';
$currentAdmin = $_SESSION['User'] ?? '';

// Handle delete user action
if ($action === 'delete_user') {
    $username = $_POST['username'] ?? '';
    
    if ($username === $currentAdmin) {
        $message = "CannotDeleteOwnAccount";
    } elseif ($username !== '') {
        // Prepare delete to remove a user by username
        $stmt = $connection->prepare("DELETE FROM user WHERE pk_username = ?");
        $stmt->bind_param("s", $username);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "UserDeleted";
        } else {
            $message = "UserDeleteFailed";
        }
    }
}

// Handle update user action
if ($action === 'update_user') {
    $username = $_POST['username'] ?? '';
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'User';
    $password = $_POST['password'] ?? '';
    
    if ($username !== '') {
        // ensure the email isn't used by another user (avoid UNIQUE constraint error)
        $canUpdate = true;
        if ($email !== '') {
            // Prepare query to ensure email is unique
            $checkEmail = $connection->prepare("SELECT pk_username FROM user WHERE email = ? AND pk_username != ?");
            $checkEmail->bind_param("ss", $email, $username);
            $checkEmail->execute();
            $emailRes = $checkEmail->get_result();
            if ($emailRes && $emailRes->num_rows > 0) {
                // preferred: use translation key if available, otherwise fallback to English text
                if (isset($arrayOfStrings['RegisterEmailExists'])) {
                    $message = 'RegisterEmailExists';
                } else {
                    $message = 'Email already registered. Use a different email.';
                }
                $canUpdate = false;
            }
        }

        if ($canUpdate) {
            if ($password !== '') {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                // Prepare update to change user fields including password
                $stmt = $connection->prepare("UPDATE user SET firstName = ?, lastName = ?, email = ?, role = ?, password = ? WHERE pk_username = ?");
                $stmt->bind_param("ssssss", $firstName, $lastName, $email, $role, $hashedPassword, $username);
            } else {
                // Prepare update to change user fields without password
                $stmt = $connection->prepare("UPDATE user SET firstName = ?, lastName = ?, email = ?, role = ? WHERE pk_username = ?");
                $stmt->bind_param("sssss", $firstName, $lastName, $email, $role, $username);
            }

            if ($stmt->execute()) {
                $message = "UserUpdated";
            } else {
                $message = "UserUpdateFailed";
            }
        }
    }
}

// Get all users for the admin table
$allUsers = [];
// Prepare query to load all users for the table
$stmt = $connection->prepare("SELECT pk_username, firstName, lastName, email, role FROM user ORDER BY pk_username ASC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allUsers[] = $row;
}

?>
<h1><?php print $arrayOfStrings["UserManagementTitle"] ?></h1>

<?php if ($message !== "") { ?>
    <p><?php echo htmlspecialchars($arrayOfStrings[$message] ?? $message); ?></p>
<?php } ?>

<p>
    <a href="Admin.php"><?php print $arrayOfStrings["Cancel"] ?></a>
</p>

<table>
    <tr>
        <th><?php print $arrayOfStrings["TableUsername"] ?></th>
        <th><?php print $arrayOfStrings["TableFirstName"] ?></th>
        <th><?php print $arrayOfStrings["TableLastName"] ?></th>
        <th><?php print $arrayOfStrings["TableEmail"] ?></th>
        <th><?php print $arrayOfStrings["TableRole"] ?></th>
        <th><?php print $arrayOfStrings["TablePassword"] ?></th>
        <th><?php print $arrayOfStrings["TableActions"] ?></th>
    </tr>
    <?php foreach ($allUsers as $user) { ?>
        <tr>
            <td><?php echo htmlspecialchars($user['pk_username']); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['pk_username']); ?>">
                    <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>">
            </td>
            <td>
                    <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>">
            </td>
            <td>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </td>
            <td>
                    <select name="role">
                        <option value="User" <?php echo ($user['role'] === 'User') ? 'selected' : ''; ?>>User</option>
                        <option value="Admin" <?php echo ($user['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
            </td>
            <td>
                    <input type="password" name="password" placeholder="<?php print $arrayOfStrings["LeaveEmptyToKeep"] ?>">
            </td>
            <td>
                    <button type="submit"><?php print $arrayOfStrings["Update"] ?></button>
                </form>
                <?php if ($user['pk_username'] !== $currentAdmin) { ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['pk_username']); ?>">
                        <button type="submit"><?php print $arrayOfStrings["Delete"] ?></button>
                    </form>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</table>

