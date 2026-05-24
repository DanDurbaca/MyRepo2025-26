<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) { header("Location: /login.php"); exit(); }
$user = $_SESSION['username'];
$coll_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = "";

// 1. Verify Ownership
$check = $conn->query("SELECT * FROM collection WHERE pk_collection = $coll_id AND fk_user_creates = '$user'");
if ($check->num_rows == 0) {
    echo "<div class='container'><h3>Error: Collection not found or you don't own it.</h3><a href='/collections.php'>Back</a></div>";
    exit();
}
$collection = $check->fetch_assoc();

// 2. Handle Share Action
if (isset($_POST['share_with'])) {
    $friend = $conn->real_escape_string($_POST['friend_username']);
    $conn->query("INSERT IGNORE INTO hasaccess (fk_collection, fk_user_recipient) VALUES ($coll_id, '$friend')");
    $msg = "<p style='color: #4ade80;'>✅ Shared with $friend!</p>";
}

// 3. Handle Unshare Action
if (isset($_POST['revoke_access'])) {
    $friend = $conn->real_escape_string($_POST['friend_username']);
    $conn->query("DELETE FROM hasaccess WHERE fk_collection = $coll_id AND fk_user_recipient = '$friend'");
    $msg = "<p style='color: #f87171;'>🚫 Access revoked for $friend.</p>";
}
?>

<div class="card">
    <a href="/collections.php" style="color:#94a3b8; text-decoration:none;">&larr; Back to Collections</a>
    <h2>Share "<?= htmlspecialchars($collection['name']) ?>"</h2>
    <?= $msg ?>

    <div style="background: #1e293b; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <h3>Grant Access</h3>
        <form method="POST" style="display:flex; gap:10px;">
            <select name="friend_username" required style="flex-grow:1; padding:10px;">
                <option value="" disabled selected>Select a friend...</option>
                <?php 
                // Get friends who DO NOT have access yet
                $sql = "SELECT pkfk_user_friend FROM isfriend 
                        WHERE pkfk_user_user = '$user' 
                        AND pkfk_user_friend NOT IN (SELECT fk_user_recipient FROM hasaccess WHERE fk_collection = $coll_id)";
                $friends = $conn->query($sql);
                
                if ($friends->num_rows > 0) {
                    while($f = $friends->fetch_assoc()) {
                        echo "<option value='{$f['pkfk_user_friend']}'>{$f['pkfk_user_friend']}</option>";
                    }
                } else {
                    echo "<option disabled>No friends available to share with</option>";
                }
                ?>
            </select>
            <button type="submit" name="share_with">Share</button>
        </form>
    </div>

    <h3>Who has access?</h3>
    <?php 
    $access_list = $conn->query("SELECT fk_user_recipient FROM hasaccess WHERE fk_collection = $coll_id");
    if ($access_list->num_rows > 0):
        while($row = $access_list->fetch_assoc()): ?>
            <div style="display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #334155;">
                <span>👤 <?= htmlspecialchars($row['fk_user_recipient']) ?></span>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="friend_username" value="<?= $row['fk_user_recipient'] ?>">
                    <button type="submit" name="revoke_access" style="background:transparent; color:#f87171; border:1px solid #f87171; padding:2px 8px; font-size:0.8em;">Revoke</button>
                </form>
            </div>
        <?php endwhile;
    else: 
        echo "<p style='color:#64748b;'>This collection is private.</p>";
    endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
