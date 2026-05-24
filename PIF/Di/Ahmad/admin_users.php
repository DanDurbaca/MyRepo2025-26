<?php 
include 'includes/header.php'; 

// Security Check
$user_role = isset($_SESSION['role']) ? trim($_SESSION['role']) : '';
if (!isset($_SESSION['username']) || strcasecmp($user_role, 'admin') !== 0) {
    echo "<div class='card'><h2>Access Denied</h2></div>";
    include 'includes/footer.php';
    exit();
}

$msg = "";

// Handle Role Toggle (Admin <-> User)
if (isset($_POST['toggle_role'])) {
    $u = $conn->real_escape_string($_POST['target_user']);
    $new_role = (strcasecmp($_POST['current_role'], 'admin') === 0) ? 'user' : 'admin';
    $conn->query("UPDATE user SET role = '$new_role' WHERE pk_username = '$u'");
    $msg = "<p style='color: #38bdf8;'>Updated $u to $new_role</p>";
}

// Handle User Deletion
if (isset($_POST['delete_user'])) {
    $u = $conn->real_escape_string($_POST['target_user']);
    if ($u !== $_SESSION['username']) {
        $conn->query("DELETE FROM user WHERE pk_username = '$u'");
        $msg = "<p style='color: #f87171;'>User $u deleted.</p>";
    }
}
?>

<div class="card">
    <h2>👥 Admin: User Management</h2>
    <?= $msg ?>
    <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="text-align:left; border-bottom: 2px solid #334155; color:#94a3b8;">
                <th style="padding:12px;">Username</th>
                <th style="padding:12px;">Current Role</th>
                <th style="padding:12px; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $res = $conn->query("SELECT pk_username, role FROM user");
            while($row = $res->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #1e293b;">
                    <td style="padding:12px;"><strong><?= htmlspecialchars($row['pk_username']) ?></strong></td>
                    <td style="padding:12px;"><?= htmlspecialchars($row['role']) ?></td>
                    <td style="padding:12px; text-align:right;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="target_user" value="<?= $row['pk_username'] ?>">
                            <input type="hidden" name="current_role" value="<?= $row['role'] ?>">
                            <button type="submit" name="toggle_role" style="background:#475569; font-size:0.8em;">Toggle Role</button>
                        </form>
                        <?php if($row['pk_username'] !== $_SESSION['username']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete user?');">
                                <input type="hidden" name="target_user" value="<?= $row['pk_username'] ?>">
                                <button type="submit" name="delete_user" style="background:#ef4444; font-size:0.8em; margin-left:5px;">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
