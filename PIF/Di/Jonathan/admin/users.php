<?php
// Admin user management UI: create/promote/demote/delete user accounts
$pageTitle = 'Manage Users';
require_once __DIR__ . '/_header.php';
?>

<div class="container">
    <h1>Manage Users</h1>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Create New User</h3>
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type">
                    <option value="User">User</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <?php echo csrf_input(); ?>
            <button class="btn" type="submit" name="create_user">Create User</button>
        </form>
    </div>

    <div class="card">
        <h3>All Users</h3>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Handle user creation
                $admin_msg = '';
                $admin_msg_type = 'info';

                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
                    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                        $admin_msg = 'Invalid CSRF token.';
                        $admin_msg_type = 'danger';
                    } else {
                        $username = trim($_POST['username']);
                        $fullname = trim($_POST['fullname']);
                        $email = trim($_POST['email']);
                        $password_raw = $_POST['password'] ?? '';
                        $user_type = $_POST['user_type'];

                        if (!preg_match('/^[A-Za-z0-9_\-]{3,64}$/', $username)) {
                            $admin_msg = 'Invalid username. Use 3-64 letters, numbers, underscore or dash.';
                            $admin_msg_type = 'danger';
                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $admin_msg = 'Invalid email address.';
                            $admin_msg_type = 'danger';
                        } elseif (strlen($password_raw) < 8) {
                            $admin_msg = 'Password must be at least 8 characters.';
                            $admin_msg_type = 'danger';
                        } else {
                            $password = password_hash($password_raw, PASSWORD_DEFAULT);
                            $parts = preg_split('/\s+/', $fullname, 2);
                            $firstName = $parts[0];
                            $lastName = isset($parts[1]) ? $parts[1] : '';

                            $stmt = $pdo->prepare("INSERT INTO `user` (pk_username, firstName, lastName, password, email, role) VALUES (?, ?, ?, ?, ?, ?)");
                            try {
                                $stmt->execute([$username, $firstName, $lastName, $password, $email, $user_type]);
                                $admin_msg = 'User created successfully!';
                                $admin_msg_type = 'success';
                            } catch (PDOException $e) {
                                error_log('Admin create user error: ' . $e->getMessage());
                                $admin_msg = 'User creation failed. See server logs.';
                                $admin_msg_type = 'danger';
                            }
                        }
                    }
                }

                // Handle promote/demote/delete actions
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
                    $token = $_POST['csrf_token'] ?? '';
                    if (!validate_csrf($token)) {
                        $admin_msg = 'Invalid CSRF token.';
                        $admin_msg_type = 'danger';
                    } else {
                        $action = $_POST['action'];
                        $username = '';

                        if ($action === 'promote') {
                            $username = $_POST['username_promote'] ?? '';
                            if ($username) {
                                $pdo->prepare("UPDATE `user` SET role = 'Admin' WHERE pk_username = ?")->execute([$username]);
                                $admin_msg = "User '$username' promoted to Admin.";
                                $admin_msg_type = 'success';
                            }
                        } elseif ($action === 'demote') {
                            $username = $_POST['username_demote'] ?? '';
                            if ($username) {
                                $pdo->prepare("UPDATE `user` SET role = 'User' WHERE pk_username = ?")->execute([$username]);
                                $admin_msg = "User '$username' demoted to User.";
                                $admin_msg_type = 'success';
                            }
                        } elseif ($action === 'delete') {
                            $username = $_POST['username_delete'] ?? '';
                            if ($username) {
                                $pdo->prepare("DELETE FROM `user` WHERE pk_username = ?")->execute([$username]);
                                $admin_msg = "User '$username' deleted.";
                                $admin_msg_type = 'success';
                            }
                        }
                    }
                }

                if (!empty($admin_msg)): ?>
                    <tr><td colspan="5"><div class="alert alert-<?php echo $admin_msg_type; ?>"><?php echo htmlspecialchars($admin_msg); ?></div></td></tr>
                <?php endif;

                // List all users
                $stmt = $pdo->query("SELECT pk_username, firstName, lastName, email, role FROM `user` ORDER BY pk_username");
                while ($user = $stmt->fetch()) {
                    $full = trim($user['firstName'] . ' ' . $user['lastName']);
                    $u = htmlspecialchars($user['pk_username']);
                    $roleLabel = htmlspecialchars($user['role']);

                    $actionButtons = '';
                    if ($user['role'] === 'Admin') {
                        $actionButtons .= "<form method='post' style='display:inline;'>" . csrf_input() . "<input type='hidden' name='action' value='demote'><input type='hidden' name='username_demote' value='" . $u . "'><button class='btn btn-small' type='submit'>Demote</button></form> ";
                    } else {
                        $actionButtons .= "<form method='post' style='display:inline;'>" . csrf_input() . "<input type='hidden' name='action' value='promote'><input type='hidden' name='username_promote' value='" . $u . "'><button class='btn btn-small' type='submit'>Promote</button></form> ";
                    }
                    $actionButtons .= "<form method='post' style='display:inline;'>" . csrf_input() . "<input type='hidden' name='action' value='delete'><input type='hidden' name='username_delete' value='" . $u . "'><button class='btn btn-danger btn-small' type='submit' onclick='return confirm(\"Delete user " . $u . "?\")'>Delete</button></form>";

                    echo "<tr>";
                    echo "<td><strong>$u</strong></td>";
                    echo "<td>" . htmlspecialchars($full) . "</td>";
                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                    echo "<td>$roleLabel</td>";
                    echo "<td>$actionButtons</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>