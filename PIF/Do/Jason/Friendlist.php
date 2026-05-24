<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="MyCss.css?<?=time();?>">
    <title>Friends</title>
    <style>
        body.stations-page .tab-bar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        body.stations-page .tab-btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            background: #e5e7eb;
            color: #111827;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        body.stations-page .tab-btn.active,
        body.stations-page .tab-btn:hover {
            background: #2563eb;
            color: #fff;
        }
        body.stations-page .tab-section { display: none; }
        body.stations-page .tab-section.active { display: block; }
        body.stations-page .friend-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        body.stations-page .friend-card .friend-name {
            font-weight: 600;
            color: #111827;
        }
        body.stations-page .friend-card .friend-sub {
            color: #6b7280;
            font-size: 0.9rem;
        }
        body.stations-page .btn-danger {
            background: #dc2626;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 0.45rem 0.9rem;
            cursor: pointer;
            font-size: 0.88rem;
            font-weight: 600;
        }
        body.stations-page .btn-danger:hover { background: #b91c1c; }
        body.stations-page .btn-success {
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 0.45rem 0.9rem;
            cursor: pointer;
            font-size: 0.88rem;
            font-weight: 600;
        }
        body.stations-page .btn-success:hover { background: #15803d; }
    </style>
</head>
<body class="stations-page">
<?php
include_once("commonphp.php");

// Create friend tables if they don't exist
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS Friendship (
    user_id int not null,
    friend_id int not null,
    primary key (user_id, friend_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS FriendRequests (
    request_ID int not null auto_increment primary key,
    sender_id int not null,
    receiver_id int not null,
    status enum('pending','accepted','refused') not null default 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: index.php');
    exit;
}

$messages = [];
$errors   = [];

// ── HANDLE POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Send a friend request
    if (isset($_POST['send_request'])) {
        $friendId = (int)($_POST['friend_id'] ?? 0);
        if ($friendId <= 0) {
            $errors[] = 'Please choose a user.';
        } elseif ($friendId === (int)$userId) {
            $errors[] = 'You cannot send a request to yourself.';
        } else {
            $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM Friendship WHERE user_id = ? AND friend_id = ?');
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $friendId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $alreadyFriends);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            if ($alreadyFriends) {
                $errors[] = 'This user is already your friend.';
            } else {
                $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM FriendRequests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = "pending"');
                mysqli_stmt_bind_param($stmt, 'iiii', $userId, $friendId, $friendId, $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $existing);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
                if ($existing) {
                    $errors[] = 'A pending request already exists.';
                } else {
                    $stmt = mysqli_prepare($conn, 'INSERT INTO FriendRequests (sender_id, receiver_id) VALUES (?, ?)');
                    mysqli_stmt_bind_param($stmt, 'ii', $userId, $friendId);
                    mysqli_stmt_execute($stmt) ? $messages[] = 'Friend request sent.' : $errors[] = 'Unable to send request.';
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }

    // Accept a friend request
    if (isset($_POST['accept_request'])) {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $stmt = mysqli_prepare($conn, 'SELECT sender_id, receiver_id FROM FriendRequests WHERE request_ID = ? AND receiver_id = ? AND status = "pending"');
        mysqli_stmt_bind_param($stmt, 'ii', $requestId, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $senderId, $receiverId);
        if (mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            $upd = mysqli_prepare($conn, 'UPDATE FriendRequests SET status = "accepted" WHERE request_ID = ?');
            mysqli_stmt_bind_param($upd, 'i', $requestId);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
            $s1 = mysqli_prepare($conn, 'INSERT IGNORE INTO Friendship (user_id, friend_id) VALUES (?, ?)');
            mysqli_stmt_bind_param($s1, 'ii', $senderId, $receiverId);
            mysqli_stmt_execute($s1);
            mysqli_stmt_close($s1);
            $s2 = mysqli_prepare($conn, 'INSERT IGNORE INTO Friendship (user_id, friend_id) VALUES (?, ?)');
            mysqli_stmt_bind_param($s2, 'ii', $receiverId, $senderId);
            mysqli_stmt_execute($s2);
            mysqli_stmt_close($s2);
            $messages[] = 'Friend request accepted.';
        } else {
            mysqli_stmt_close($stmt);
            $errors[] = 'Request not found or already handled.';
        }
    }

    // Refuse a friend request
    if (isset($_POST['refuse_request'])) {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $stmt = mysqli_prepare($conn, 'UPDATE FriendRequests SET status = "refused" WHERE request_ID = ? AND receiver_id = ? AND status = "pending"');
        mysqli_stmt_bind_param($stmt, 'ii', $requestId, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_affected_rows($stmt) > 0 ? $messages[] = 'Request refused.' : $errors[] = 'Request not found.';
        mysqli_stmt_close($stmt);
    }

    // Remove a friend
    if (isset($_POST['remove_friend'])) {
        $friendId = (int)($_POST['friend_id'] ?? 0);
        $d1 = mysqli_prepare($conn, 'DELETE FROM Friendship WHERE user_id = ? AND friend_id = ?');
        mysqli_stmt_bind_param($d1, 'ii', $userId, $friendId);
        mysqli_stmt_execute($d1);
        mysqli_stmt_close($d1);
        $d2 = mysqli_prepare($conn, 'DELETE FROM Friendship WHERE user_id = ? AND friend_id = ?');
        mysqli_stmt_bind_param($d2, 'ii', $friendId, $userId);
        mysqli_stmt_execute($d2);
        mysqli_stmt_close($d2);
        $messages[] = 'Friend removed.';
    }
}

// ── LOAD DATA ────────────────────────────────────────────────────────────────
$incomingRequests = [];
$stmt = mysqli_prepare($conn, 'SELECT fr.request_ID, u.user_ID, u.UName, u.full_name, fr.created_at FROM FriendRequests fr JOIN `User` u ON fr.sender_id = u.user_ID WHERE fr.receiver_id = ? AND fr.status = "pending" ORDER BY fr.created_at');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) { $incomingRequests[] = $row; }
mysqli_stmt_close($stmt);

$outgoingRequests = [];
$stmt = mysqli_prepare($conn, 'SELECT fr.request_ID, u.user_ID, u.UName, u.full_name, fr.created_at FROM FriendRequests fr JOIN `User` u ON fr.receiver_id = u.user_ID WHERE fr.sender_id = ? AND fr.status = "pending" ORDER BY fr.created_at');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) { $outgoingRequests[] = $row; }
mysqli_stmt_close($stmt);

$friends = [];
$stmt = mysqli_prepare($conn, 'SELECT u.user_ID, u.UName, u.full_name FROM Friendship f JOIN `User` u ON f.friend_id = u.user_ID WHERE f.user_id = ? ORDER BY u.UName');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) { $friends[] = $row; }
mysqli_stmt_close($stmt);

$potentialFriends = [];
$stmt = mysqli_prepare($conn, 'SELECT user_ID, UName, full_name FROM `User` WHERE user_ID != ? AND user_ID NOT IN (
    SELECT friend_id FROM Friendship WHERE user_id = ?
    UNION
    SELECT receiver_id FROM FriendRequests WHERE sender_id = ? AND status = "pending"
    UNION
    SELECT sender_id FROM FriendRequests WHERE receiver_id = ? AND status = "pending"
) ORDER BY UName');
mysqli_stmt_bind_param($stmt, 'iiii', $userId, $userId, $userId, $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) { $potentialFriends[] = $row; }
mysqli_stmt_close($stmt);

$pendingCount = count($incomingRequests);
?>

<div class="container">
    <h1 class="Title">Friends</h1>
    <p class="lead">Manage your friends and friend requests.</p>

    <?php foreach ($errors as $e): ?>
        <div class="alert" style="background:#fee2e2;border-color:#fca5a5;color:#991b1b;"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>
    <?php foreach ($messages as $m): ?>
        <div class="alert" style="background:#d1fae5;border-color:#6ee7b7;color:#065f46;"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>

    <!-- Tab buttons -->
    <div class="tab-bar">
        <button class="tab-btn active" onclick="showTab('friends', this)">
            My Friends (<?= count($friends) ?>)
        </button>
        <button class="tab-btn" onclick="showTab('requests', this)">
            Requests<?= $pendingCount > 0 ? " ($pendingCount)" : '' ?>
        </button>
        <button class="tab-btn" onclick="showTab('add', this)">Add Friend</button>
    </div>

    <!-- My Friends -->
    <div id="tab-friends" class="tab-section active">
        <?php if (count($friends) > 0): ?>
            <?php foreach ($friends as $f): ?>
            <div class="friend-card">
                <div>
                    <div class="friend-name"><?= htmlspecialchars($f['UName'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="friend-sub"><?= htmlspecialchars($f['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <form method="post" onsubmit="return confirm('Remove this friend?');" style="margin:0;">
                    <input type="hidden" name="friend_id" value="<?= (int)$f['user_ID'] ?>">
                    <button type="submit" name="remove_friend" class="btn-danger">Remove</button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert">You have no friends yet. Send a friend request to get started.</div>
        <?php endif; ?>
    </div>

    <!-- Requests -->
    <div id="tab-requests" class="tab-section">
        <div class="section-card">
            <h2>Incoming Requests</h2>
            <?php if (count($incomingRequests) > 0): ?>
                <?php foreach ($incomingRequests as $req): ?>
                <div class="friend-card">
                    <div>
                        <div class="friend-name"><?= htmlspecialchars($req['UName'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="friend-sub"><?= htmlspecialchars($req['full_name'], ENT_QUOTES, 'UTF-8') ?> &mdash; <?= htmlspecialchars($req['created_at'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <form method="post" style="display:flex;gap:0.5rem;margin:0;">
                        <input type="hidden" name="request_id" value="<?= (int)$req['request_ID'] ?>">
                        <button type="submit" name="accept_request" class="btn-success">Accept</button>
                        <button type="submit" name="refuse_request" class="btn-danger">Refuse</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert">No incoming requests.</div>
            <?php endif; ?>
        </div>

        <div class="section-card">
            <h2>Outgoing Requests</h2>
            <?php if (count($outgoingRequests) > 0): ?>
                <?php foreach ($outgoingRequests as $req): ?>
                <div class="friend-card">
                    <div>
                        <div class="friend-name"><?= htmlspecialchars($req['UName'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="friend-sub"><?= htmlspecialchars($req['full_name'], ENT_QUOTES, 'UTF-8') ?> &mdash; sent <?= htmlspecialchars($req['created_at'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <span class="note">Pending</span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert">No outgoing requests.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Friend -->
    <div id="tab-add" class="tab-section">
        <div class="section-card">
            <h2>Send Friend Request</h2>
            <?php if (count($potentialFriends) > 0): ?>
            <form method="post">
                <div class="field-row">
                    <label>Choose a user</label>
                    <select name="friend_id" required>
                        <option value="">Select a user</option>
                        <?php foreach ($potentialFriends as $u): ?>
                            <option value="<?= (int)$u['user_ID'] ?>">
                                <?= htmlspecialchars($u['UName'], ENT_QUOTES, 'UTF-8') ?>
                                (<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="button-row">
                    <button type="submit" name="send_request">Send Request</button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert">No users available to add.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function showTab(name, btn) {
        document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        btn.classList.add('active');
    }
</script>
</body>
</html>
