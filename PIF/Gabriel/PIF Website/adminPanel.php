<?php
session_start();

// Check if user is logged in
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
    die("<p style='color:red'>Database connection failed</p>");
}

$currentUser = $_SESSION["userNameSession"];

// Check if user is admin
$role_sql = "SELECT role FROM user WHERE pk_username = ?";
$role_stmt = mysqli_prepare($conn, $role_sql);
mysqli_stmt_bind_param($role_stmt, "s", $currentUser);
mysqli_stmt_execute($role_stmt);
$role_result = mysqli_stmt_get_result($role_stmt);
$user_data = mysqli_fetch_assoc($role_result);

if ($user_data['role'] !== 'Admin') {
    header("Location: HomePage.php");
    exit;
}

$error = "";
$success = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $backToAdmin = $_POST["backToAdmin"] ?? '';
    $editUser = $_POST["editUser"] ?? '';
    $deleteUser = $_POST["deleteUser"] ?? '';
    
    // Back to admin panel - FIXED THIS
    if (isset($backToAdmin) && $backToAdmin === "← Back") {
        header("Location: homePage.php");
        exit;
    }
    
    // Edit user role
    if (!empty($editUser)) {
        $targetUsername = $_POST["targetUsername"];
        $newRole = $_POST["newRole"];
        
        if ($targetUsername === $currentUser) {
            $error = "Error! You cannot change your own role.";
        } else {
            // Start transaction for safety
            mysqli_begin_transaction($conn);
            
            try {
                // If changing from User to Admin, remove station ownership
                if ($newRole === 'Admin') {
                    // First check if user has any stations
                    $check_stations_sql = "SELECT COUNT(*) as station_count FROM station WHERE fk_user_owns = ?";
                    $check_stmt = mysqli_prepare($conn, $check_stations_sql);
                    mysqli_stmt_bind_param($check_stmt, "s", $targetUsername);
                    mysqli_stmt_execute($check_stmt);
                    $check_result = mysqli_stmt_get_result($check_stmt);
                    $station_data = mysqli_fetch_assoc($check_result);
                    $station_count = $station_data['station_count'];
                    
                    if ($station_count > 0) {
                        // Remove user from all stations (make them unassigned)
                        $remove_stations_sql = "UPDATE station SET fk_user_owns = NULL WHERE fk_user_owns = ?";
                        $remove_stmt = mysqli_prepare($conn, $remove_stations_sql);
                        mysqli_stmt_bind_param($remove_stmt, "s", $targetUsername);
                        mysqli_stmt_execute($remove_stmt);
                    }
                }
                
                // Update user role
                $update_sql = "UPDATE user SET role = ? WHERE pk_username = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ss", $newRole, $targetUsername);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    mysqli_commit($conn);
                    
                    // Show appropriate success message
                    if ($newRole === 'Admin' && $station_count > 0) {
                        $success = "User '$targetUsername' promoted to Admin. $station_count station(s) have been unassigned and are now available for other users.";
                    } else if ($newRole === 'Admin') {
                        $success = "User '$targetUsername' promoted to Admin successfully!";
                    } else {
                        $success = "User '$targetUsername' role updated to '$newRole' successfully!";
                    }
                } else {
                    mysqli_rollback($conn);
                    $error = "Error! Could not update user role: " . mysqli_error($conn);
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Error! Transaction failed: " . $e->getMessage();
            }
        }
    }
    
    // Delete user
    if (!empty($deleteUser)) {
        $targetUsername = $_POST["targetUsername"];
        $adminPassword = trim($_POST["adminPassword"]);
        $confirmDelete = $_POST["confirmDelete"] ?? '';
        
        if ($targetUsername === $currentUser) {
            $error = "Error! You cannot delete your own account.";
        } elseif ($confirmDelete !== "DELETE") {
            $error = "Error! Please type DELETE to confirm deletion.";
        } elseif (empty($adminPassword)) {
            $error = "Error! Please enter your admin password to delete user.";
        } else {
            // Verify admin password
            $verify_sql = "SELECT password FROM user WHERE pk_username = ?";
            $verify_stmt = mysqli_prepare($conn, $verify_sql);
            mysqli_stmt_bind_param($verify_stmt, "s", $currentUser);
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            $verify_data = mysqli_fetch_assoc($verify_result);
            
            if (!password_verify($adminPassword, $verify_data['password'])) {
                $error = "Error! Incorrect admin password.";
            } else {
                // Delete user
                $delete_sql = "DELETE FROM user WHERE pk_username = ?";
                $delete_stmt = mysqli_prepare($conn, $delete_sql);
                mysqli_stmt_bind_param($delete_stmt, "s", $targetUsername);
                
                if (mysqli_stmt_execute($delete_stmt)) {
                    $success = "User '$targetUsername' deleted successfully!";
                } else {
                    $error = "Error! Could not delete user: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Get all users
$users_sql = "SELECT pk_username, firstName, lastName, email, role FROM user ORDER BY role DESC, pk_username";
$users_result = mysqli_query($conn, $users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIF - Admin User Management</title>
    <link rel="stylesheet" href="admin panel customization.css">
</head>
<body>
    <div class="container">
        <!-- Back button - SEPARATE FORM to avoid conflicts -->
        <form method="POST" action="" class="back-form">
            <input type="submit" class="back-btn" value="← Back" name="backToAdmin">
        </form>
        
        <h1>Admin User Management</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <?php
        mysqli_data_seek($users_result, 0);
        $totalUsers = mysqli_num_rows($users_result);
        $adminCount = 0;
        $userCount = 0;
        
        while ($user = mysqli_fetch_assoc($users_result)) {
            if ($user['role'] === 'Admin') {
                $adminCount++;
            } else {
                $userCount++;
            }
        }
        mysqli_data_seek($users_result, 0);
        ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $adminCount; ?></div>
                <div class="stat-label">Admins</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $userCount; ?></div>
                <div class="stat-label">Regular Users</div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="users-table">
            <div class="table-header">
                <div>Username</div>
                <div>Name</div>
                <div>Email</div>
                <div>Role</div>
                <div>Actions</div>
            </div>
            
            <?php if (mysqli_num_rows($users_result) > 0): ?>
                <?php while ($user = mysqli_fetch_assoc($users_result)): 
                    $isCurrentUser = ($user['pk_username'] === $currentUser);
                ?>
                    <div class="user-row <?php echo $isCurrentUser ? 'current-user' : ''; ?>">
                        <div>
                            <strong><?php echo htmlspecialchars($user['pk_username']); ?></strong>
                            <?php if ($isCurrentUser): ?>
                                <div style="font-size: 12px; color: #666;">(You)</div>
                            <?php endif; ?>
                        </div>
                        <div><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></div>
                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                        <div>
                            <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </div>
                        <div class="action-buttons">
                            <?php if (!$isCurrentUser): ?>
                                <button class="btn-edit" onclick="openEditModal('<?php echo $user['pk_username']; ?>', '<?php echo $user['role']; ?>')">
                                    Edit Role
                                </button>
                                <button class="btn-delete" onclick="openDeleteModal('<?php echo $user['pk_username']; ?>', '<?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName'], ENT_QUOTES); ?>')">
                                    Delete
                                </button>
                            <?php else: ?>
                                <span style="color: #666; font-size: 14px;">Current User</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="padding: 20px; text-align: center; color: #666;">
                    No users found in the database.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Role Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit User Role</h2>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="targetUsername" id="editTargetUsername">
                
                <div class="form-group">
                    <label for="newRole">New Role:</label>
                    <select name="newRole" id="newRole" required>
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                
                <div class="warning-text">
                    Changing a user to Admin will give them full administrative access.
                </div>
                
                <div class="form-group">
                    <input type="submit" value="Update Role" name="editUser" class="edit-btn-submit">
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete User Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>Delete User</h2>
            
            <div class="warning-text">
                <strong>Warning:</strong> This action cannot be undone. All user data, stations, friends, and collections will be permanently deleted.
            </div>
            
            <p id="deleteMessage">Are you sure you want to delete this user?</p>
            
            <form method="POST" action="" id="deleteForm">
                <input type="hidden" name="targetUsername" id="deleteTargetUsername">
                
                <div class="form-group">
                    <label for="confirmDelete">Type "DELETE" to confirm:</label>
                    <input type="text" name="confirmDelete" placeholder="Type DELETE here" required>
                </div>
                
                <div class="form-group">
                    <label for="adminPassword">Enter your admin password:</label>
                    <input type="password" name="adminPassword" placeholder="Your admin password" required>
                </div>
                
                <div class="form-group">
                    <input type="submit" value="Delete User" name="deleteUser">
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Edit Modal Functions
        function openEditModal(username, currentRole) {
            document.getElementById('editTargetUsername').value = username;
            document.getElementById('newRole').value = currentRole;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Delete Modal Functions: small box that shows up when click edit
        function openDeleteModal(username, fullName) {
            document.getElementById('deleteTargetUsername').value = username;
            document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete user "<strong>${fullName}</strong>" (${username})?`;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target == editModal) {
                closeEditModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>