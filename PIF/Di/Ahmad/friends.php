<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) { header("Location: /login.php"); exit(); }
$user = $_SESSION['username'];
$msg = "";

// --- 1. HANDLE POST ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // SEND REQUEST
        if (isset($_POST['send_request'])) {
            $target = $conn->real_escape_string($_POST['target_user']);
            $sql = "INSERT INTO friend_request (sender, receiver) VALUES ('$user', '$target')";
            if ($conn->query($sql)) {
                $msg = "<p style='color: #4ade80;'>✅ Friend request sent to $target!</p>";
            }
        }

        // ACCEPT REQUEST
        if (isset($_POST['accept_friend'])) {
            $sender = $conn->real_escape_string($_POST['sender']);
            
            // Start a transaction to ensure both rows are added and request is deleted
            $conn->begin_transaction();

            // Note: I am using the column names provided in typical schemas for this project. 
            // If this fails, the error message will tell us the correct column names.
            $conn->query("INSERT IGNORE INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES ('$user', '$sender')");
            $conn->query("INSERT IGNORE INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES ('$sender', '$user')");
            
            // Delete the request
            $conn->query("DELETE FROM friend_request WHERE sender = '$sender' AND receiver = '$user'");
            
            $conn->commit();
            $msg = "<p style='color: #4ade80;'>🎉 You are now friends with $sender!</p>";
        }

        // END FRIENDSHIP
        if (isset($_POST['end_friendship'])) {
            $friend = $conn->real_escape_string($_POST['friend']);
            $conn->query("DELETE FROM isfriend WHERE (pkfk_user_user = '$user' AND pkfk_user_friend = '$friend') OR (pkfk_user_user = '$friend' AND pkfk_user_friend = '$user')");
            $msg = "<p style='color: #f87171;'>Friendship ended.</p>";
        }
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $msg = "<p style='color: #f87171;'>⚠️ SQL Error: " . $e->getMessage() . "</p>";
    }
}
?>

<div class="card">
    <?= $msg ?>
    
    <h2>Find People</h2>
    <form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
        <input type="text" name="search" placeholder="Search usernames..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" style="margin:0;">
        <button type="submit">Search</button>
    </form>

    <?php if (isset($_GET['search'])): 
        $search = $conn->real_escape_string($_GET['search']);
        // Find users, excluding yourself
        $results = $conn->query("SELECT pk_username FROM user WHERE pk_username LIKE '%$search%' AND pk_username != '$user'");
        
        while($row = $results->fetch_assoc()): 
            $potential_friend = $row['pk_username'];
            
            // Check if already friends
            $check_friend = $conn->query("SELECT * FROM isfriend WHERE pkfk_user_user = '$user' AND pkfk_user_friend = '$potential_friend'");
            
            // Check if request already sent
            $check_req = $conn->query("SELECT * FROM friend_request WHERE sender = '$user' AND receiver = '$potential_friend'");
            ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#0f172a; border-radius:6px; margin-bottom:8px; border:1px solid #1e293b;">
                <span><?= htmlspecialchars($potential_friend) ?></span>
                
                <?php if ($check_friend->num_rows > 0): ?>
                    <span style="color:#64748b; font-size:0.9em;">Already Friends</span>
                <?php elseif ($check_req->num_rows > 0): ?>
                    <span style="color:#fbbf24; font-size:0.9em;">⏳ Request Sent</span>
                <?php else: ?>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="target_user" value="<?= $potential_friend ?>">
                        <button type="submit" name="send_request" style="padding:5px 15px; font-size:0.85em;">+ Add Friend</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px; margin-top:40px;">
        <section>
            <h3 style="border-bottom:1px solid #334155; padding-bottom:10px;">Friend Requests</h3>
            <?php 
            $reqs = $conn->query("SELECT sender FROM friend_request WHERE receiver = '$user'");
            if ($reqs->num_rows > 0):
                while($r = $reqs->fetch_assoc()): ?>
                    <div style="background:#1e293b; padding:15px; border-radius:8px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                        <strong><?= htmlspecialchars($r['sender']) ?></strong>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="sender" value="<?= $r['sender'] ?>">
                            <button type="submit" name="accept_friend" style="background:#10b981;">Accept</button>
                        </form>
                    </div>
                <?php endwhile;
            else:
                echo "<p style='color:#64748b;'>No pending requests.</p>";
            endif; ?>
        </section>

        <section>
            <h3 style="border-bottom:1px solid #334155; padding-bottom:10px;">My Friends</h3>
            <?php 
            $friends = $conn->query("SELECT pkfk_user_friend FROM isfriend WHERE pkfk_user_user = '$user'");
            if ($friends->num_rows > 0):
                while($f = $friends->fetch_assoc()): ?>
                    <div style="background:#1e293b; padding:15px; border-radius:8px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                        <span><?= htmlspecialchars($f['pkfk_user_friend']) ?></span>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="friend" value="<?= $f['pkfk_user_friend'] ?>">
                            <button type="submit" name="end_friendship" style="background:#ef4444; padding:5px 10px; font-size:0.8em;">Remove</button>
                        </form>
                    </div>
                <?php endwhile;
            else:
                echo "<p style='color:#64748b;'>You haven't added any friends yet.</p>";
            endif; ?>
        </section>
    </div>
</div>

<?php include 'includes/footer.php'; ?>