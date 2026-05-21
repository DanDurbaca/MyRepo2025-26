<?php
session_start();
if (empty($_SESSION["userNameSession"])) {
    header("Location: Log-in.php");
    exit;
}

// Database connection
$host = "localhost";
$db = "portableindoorfeedback";
$user = "root";
$pass = "";
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("<p style='color:red'>Connection failed: " . mysqli_connect_error() . "</p>");
}

$currentUser = $_SESSION["userNameSession"];
$error = "";
$success = "";

// Fetch current user info
$sql = "SELECT * FROM user WHERE pk_username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $currentUser);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $backToHomePage = $_POST["homepage"] ?? null;
    $updateProfile  = $_POST["updateProfile"] ?? null;
    $changePassword = $_POST["changePassword"] ?? null;
    $deleteAccount  = $_POST["deleteAccount"] ?? null;


    // Back to homepage - put this first
    if (isset($backToHomePage)) {
        header("location: HomePage.php");
        exit;
    }

    // Update profile info
    if (isset($updateProfile)) {
        $newFirstName = trim($_POST["firstName"]);
        $newLastName = trim($_POST["lastName"]);
        $newEmail = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        
        if (empty($password)) {
            $error = "Error! Please enter your password to make changes.";
        } elseif (!password_verify($password, $userData['password'])) {
            $error = "Error! Incorrect password.";
        } elseif (empty($newFirstName) || empty($newLastName) || empty($newEmail)) {
            $error = "Error! All fields are required.";
        } else {
            // Check if email already exists (excluding current user)
            $check_sql = "SELECT pk_username FROM user WHERE email = ? AND pk_username != ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "ss", $newEmail, $currentUser);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error = "Error! Email already exists.";
            } else {
                // Update user info
                $update_sql = "UPDATE user SET firstName = ?, lastName = ?, email = ? WHERE pk_username = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ssss", $newFirstName, $newLastName, $newEmail, $currentUser);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $success = "Profile updated successfully!";
                    // Refresh user data
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $userData = mysqli_fetch_assoc($result);
                } else {
                    $error = "Error! Could not update profile: " . mysqli_error($conn);
                }
            }
        }
    }

    // Change password
    if (isset($changePassword)) {
        $currentPassword = trim($_POST["currentPassword"]);
        $newPassword = trim($_POST["newPassword"]);
        $confirmPassword = trim($_POST["confirmPassword"]);
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "Error! All password fields are required.";
        } elseif (!password_verify($currentPassword, $userData['password'])) {
            $error = "Error! Current password is incorrect.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Error! New passwords do not match.";
        } elseif (strlen($newPassword) < 6) {
            $error = "Error! New password must be at least 6 characters.";
        } else {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update_sql = "UPDATE user SET password = ? WHERE pk_username = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ss", $hashedPassword, $currentUser);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success = "Password changed successfully!";
                // Refresh user data
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $userData = mysqli_fetch_assoc($result);
            } else {
                $error = "Error! Could not change password: " . mysqli_error($conn);
            }
        }
    }

    // Delete account
    if (isset($deleteAccount)) {
        $password = trim($_POST["deletePassword"]);
        $confirmDelete = $_POST["confirmDelete"];
        
        if (empty($password)) {
            $error = "Error! Please enter your password to delete account.";
        } elseif (!password_verify($password, $userData['password'])) {
            $error = "Error! Incorrect password.";
        } elseif ($confirmDelete !== "DELETE") {
            $error = "Error! Please type DELETE to confirm account deletion.";
        } else {
            // Delete user from database
            $delete_sql = "DELETE FROM user WHERE pk_username = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, "s", $currentUser);
            
            if (mysqli_stmt_execute($delete_stmt)) {
                session_unset();
                session_destroy();
                header("Location: Registeration.php");
                exit;
            } else {
                $error = "Error! Could not delete account: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIF - User Profile</title>
    <link rel="stylesheet" href="user profile page customization.css">
</head>
<body>
    <!-- Separate form for Back button -->
    <form method="POST" action="" style="position: absolute; top: 20px; left: 20px;">
        <input type="submit" class="back-btn" value="← Back to Home" name="homepage">
    </form>
    
    <div class="container">
        <h1>User Profile</h1>
        
        <?php if (!empty($error)) { echo "<div class='error'>$error</div>"; } ?>
        <?php if (!empty($success)) { echo "<div class='success'>$success</div>"; } ?>
        
        <!-- Current Information -->
        <div class="current-info">
            <h3>Current Information:</h3>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['pk_username']); ?></p>
            <p><strong>First Name:</strong> <?php echo htmlspecialchars($userData['firstName']); ?></p>
            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($userData['lastName']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($userData['role']); ?></p>
        </div>
        
        <!-- Update Profile Section - Separate form -->
        <div class="profile-section">
            <h2>Update Profile Information</h2>
            <form method="POST" action="">
                <h3>First Name:</h3>
                <input type="text" name="firstName" value="<?php echo htmlspecialchars($userData['firstName']); ?>" required>
                
                <h3>Last Name:</h3>
                <input type="text" name="lastName" value="<?php echo htmlspecialchars($userData['lastName']); ?>" required>
                
                <h3>Email:</h3>
                <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                
                <h3>Current Password (required for changes):</h3>
                <div class="password-container">
                    <input type="password" name="password" placeholder="Enter your current password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword(this, 'password')">Show</button>
                </div>
                
                <input type="submit" value="Update Profile" name="updateProfile">
            </form>
        </div>
        
        <!-- Change Password Section - Separate form -->
        <div class="profile-section">
            <h2>Change Password</h2>
            <form method="POST" action="">
                <h3>Current Password:</h3>
                <div class="password-container">
                    <input type="password" name="currentPassword" placeholder="Enter current password">
                    <button type="button" class="toggle-password" onclick="togglePassword(this, 'currentPassword')">Show</button>
                </div>
                
                <h3>New Password:</h3>
                <div class="password-container">
                    <input type="password" name="newPassword" placeholder="Enter new password (min 6 characters)">
                    <button type="button" class="toggle-password" onclick="togglePassword(this, 'newPassword')">Show</button>
                </div>
                
                <h3>Confirm New Password:</h3>
                <div class="password-container">
                    <input type="password" name="confirmPassword" placeholder="Confirm new password">
                    <button type="button" class="toggle-password" onclick="togglePassword(this, 'confirmPassword')">Show</button>
                </div>
                
                <input type="submit" value="Change Password" name="changePassword">
            </form>
        </div>
        
        <!-- Delete Account Section - Separate form -->
        <div class="danger-zone">
            <h2>Delete Account</h2>
            
            <div class="warning-text">
                <strong>Warning:</strong> This action cannot be undone. All your data, stations, friends, and collections will be permanently deleted.
            </div>
            
            <form method="POST" action="">
                <h3>Enter your password:</h3>
                <div class="password-container">
                    <input type="password" name="deletePassword" placeholder="Enter your password">
                    <button type="button" class="toggle-password" onclick="togglePassword(this, 'deletePassword')">Show</button>
                </div>
                
                <h3>Type "DELETE" to confirm:</h3>
                <input type="text" name="confirmDelete" placeholder="Type DELETE here">
                
                <input type="submit" class="delete-btn" value="Delete My Account" name="deleteAccount">
            </form>
        </div>
    </div>

    <script>
        function togglePassword(button, fieldName) {
            const form = button.closest('form');
            const passwordInput = form.querySelector(`input[name="${fieldName}"]`);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                button.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                button.textContent = 'Show';
            }
        }
    </script>
</body>
</html>