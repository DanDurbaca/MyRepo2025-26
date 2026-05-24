<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit();
}

$username = $_SESSION['username'];
$msg = "";

// --- HANDLE UPDATE REQUEST ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName  = $conn->real_escape_string($_POST['lastName']);
    $email     = $conn->real_escape_string($_POST['email']);
    $newPassword = $_POST['new_password'];

    // Start building the query
    $sql = "UPDATE user SET firstName = '$firstName', lastName = '$lastName', email = '$email'";

    // If user provided a new password, hash it and add to query
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql .= ", password = '$hashedPassword'";
    }

    $sql .= " WHERE pk_username = '$username'";

    if ($conn->query($sql)) {
        $msg = "<p style='color: #4ade80;'>Account updated successfully!</p>";
    } else {
        $msg = "<p style='color: #f87171;'>Error: " . $conn->error . "</p>";
    }
}

// Fetch current data
$res = $conn->query("SELECT * FROM user WHERE pk_username = '$username'");
$u = $res->fetch_assoc();
?>

<div class="card" style="max-width: 500px; margin: auto;">
    <h2>Edit Account Settings</h2>
    <?= $msg ?>
    
    <form method="POST">
        <label>Username (Fixed)</label>
        <input type="text" value="<?= htmlspecialchars($u['pk_username']) ?>" disabled>

        <label>First Name</label>
        <input type="text" name="firstName" value="<?= htmlspecialchars($u['firstName']) ?>" required>

        <label>Last Name</label>
        <input type="text" name="lastName" value="<?= htmlspecialchars($u['lastName']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>

        <label>New Password (Leave blank to keep current)</label>
        <input type="password" name="new_password" placeholder="••••••••">

        <button type="submit" style="width: 100%; margin-top: 10px;">Update Account</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>