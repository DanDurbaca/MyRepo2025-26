<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <title>Portable Indoor Feedback - Friend Page</title>
    <link rel="stylesheet" href="style.css?<?php print(time()); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- https://www.w3schools.com/css/css_rwd_viewport.asp -->
</head>

<body>
<?php
// Load shared utilities and navigation
include_once("CommonCode.php");
NavigationBar1("FriendPage");

// Require login before managing friends
requireLogin();

// Current logged-in user
$me = getCurrentUser();

// Data containers for the UI
$searchResults = [];
$incomingRequests = [];
$pendingRequests = [];
$friends = [];

// Handle friend-related actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'search') {
        $term = trim($_POST['username'] ?? '');
        if ($term !== '') {
            // Prepare query to find a user by exact username
            $stmt = $connection->prepare("SELECT pk_username, firstName, lastName FROM `user` WHERE pk_username = ?");
            $stmt->bind_param("s", $term);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $searchResults[] = $r;
            }
        }
    }

    if ($action === 'send_request') {
        $to = trim($_POST['to_user'] ?? '');
        if ($to !== '') {
            // Insert request if not exists (me -> to)
            // Prepare query to check if request already exists (me -> to)
            $chk = $connection->prepare("SELECT 1 FROM isfriend WHERE pkfk_user_user = ? AND pkfk_user_friend = ?");
            $chk->bind_param("ss", $me, $to);
            $chk->execute();

            if (!$chk->get_result()->fetch_assoc()) {
                // Prepare insert to create friend request (me -> to)
                $ins = $connection->prepare("INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (?, ?)");
                $ins->bind_param("ss", $me, $to);
                $ins->execute();
            }
        }
    }

    if ($action === 'accept') {
        $from = trim($_POST['from_user'] ?? '');
        if ($from !== '') {
            // Create reciprocal row (me -> from) if not exists
            // Prepare query to check if reciprocal row exists
            $chk = $connection->prepare("SELECT 1 FROM isfriend WHERE pkfk_user_user = ? AND pkfk_user_friend = ?");
            $chk->bind_param("ss", $me, $from);
            $chk->execute();

            if (!$chk->get_result()->fetch_assoc()) {
                // Prepare insert to accept request (me -> from)
                $ins = $connection->prepare("INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (?, ?)");
                $ins->bind_param("ss", $me, $from);
                $ins->execute();
            }
        }
    }

    if ($action === 'decline') {
        $from = trim($_POST['from_user'] ?? '');
        if ($from !== '') {
            // Delete request (from -> me)
            // Prepare delete to remove incoming request (from -> me)
            $del = $connection->prepare("DELETE FROM isfriend WHERE pkfk_user_user = ? AND pkfk_user_friend = ?");
            $del->bind_param("ss", $from, $me);
            $del->execute();
        }
    }

    if ($action === 'end_friendship') {
        $friend = trim($_POST['friend'] ?? '');
        if ($friend !== '') {
            // Delete both directions
            // Prepare delete to remove friendship both directions
            $del = $connection->prepare("
                DELETE FROM isfriend
                WHERE (pkfk_user_user = ? AND pkfk_user_friend = ?)
                   OR (pkfk_user_user = ? AND pkfk_user_friend = ?)
            ");
            $del->bind_param("ssss", $me, $friend, $friend, $me);
            $del->execute();
            
            // Remove collection sharing between these two users
            // Prepare delete to remove shared collections both directions
            $delAccess = $connection->prepare("
                DELETE FROM hasaccess
                WHERE (pkfk_collection IN (SELECT pk_collection FROM collection WHERE fk_user_creates = ?) AND pkfk_user = ?)
                   OR (pkfk_collection IN (SELECT pk_collection FROM collection WHERE fk_user_creates = ?) AND pkfk_user = ?)
            ");
            $delAccess->bind_param("ssss", $me, $friend, $friend, $me); // me -> friend and friend -> me unsharing 
            $delAccess->execute();
        }
    }
}

// Reload lists for display (always)

/* Incoming requests (from -> me) that are NOT mutual */
// Prepare query for incoming requests (not mutual)
$stmt = $connection->prepare("
    SELECT u.pk_username, u.firstName, u.lastName
    FROM isfriend f
    JOIN `user` u ON u.pk_username = f.pkfk_user_user
    WHERE f.pkfk_user_friend = ?
      AND NOT EXISTS (
        SELECT 1 FROM isfriend r
        WHERE r.pkfk_user_user = f.pkfk_user_friend
          AND r.pkfk_user_friend = f.pkfk_user_user
      )
");
$stmt->bind_param("s", $me);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $incomingRequests[] = $r;
}

/* Outgoing pending requests (me -> to) that are NOT mutual */
// Prepare query for outgoing pending requests (not mutual)
$stmt = $connection->prepare("
    SELECT u.pk_username, u.firstName, u.lastName
    FROM isfriend f
    JOIN `user` u ON u.pk_username = f.pkfk_user_friend
    WHERE f.pkfk_user_user = ?
      AND NOT EXISTS (
        SELECT 1 FROM isfriend r
        WHERE r.pkfk_user_user = f.pkfk_user_friend
          AND r.pkfk_user_friend = f.pkfk_user_user
      )
");
$stmt->bind_param("s", $me);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $pendingRequests[] = $r;
}

/* Current friends (mutual) */
// Prepare query for mutual friends list
$stmt = $connection->prepare("
    SELECT u.pk_username, u.firstName, u.lastName
    FROM isfriend f
    JOIN `user` u ON u.pk_username = f.pkfk_user_friend
    WHERE f.pkfk_user_user = ?
      AND EXISTS (
        SELECT 1 FROM isfriend r
        WHERE r.pkfk_user_user = f.pkfk_user_friend
          AND r.pkfk_user_friend = f.pkfk_user_user
      )
");
$stmt->bind_param("s", $me);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $friends[] = $r;
}
?>

<h1><?php print $arrayOfStrings["FriendPage"] ?? 'Friends'; ?></h1>

<section>
    <h2><?php print $arrayOfStrings["ResearchText"] ?? 'Search'; ?></h2>
    <form method="POST">
        <input type="hidden" name="action" value="search" />
        <input type="text" name="username" placeholder="<?php print $arrayOfStrings["EnterUsername"] ?? 'Enter Username'; ?>" required />
        <input type="submit" value="<?php print $arrayOfStrings["ResearchButton"] ?? 'Search'; ?>">
    </form>

    <?php if (!empty($searchResults)) { ?>
        <h3><?php print $arrayOfStrings['SearchResults'] ?? 'Results'; ?></h3>
        <ul>
            <?php foreach ($searchResults as $row) { ?>
                <li>
                    <?php echo htmlspecialchars($row['pk_username'] . ' (' . ($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? '') . ')'); ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="send_request" />
                        <input type="hidden" name="to_user" value="<?php echo htmlspecialchars($row['pk_username']); ?>" />
                        <button type="submit"><?php print $arrayOfStrings['SendFriendRequest'] ?? 'Send Friend Request'; ?></button>
                    </form>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</section>

<section>
    <h2><?php print $arrayOfStrings["CurrentFriendRequests"] ?? 'Your Current Friend Requests:'; ?></h2>
    <?php if (empty($pendingRequests)) { ?>
        <p><?php print $arrayOfStrings["NoPendingOutgoing"] ?? "You have no pending outgoing requests."; ?></p>
    <?php } else { ?>
        <ul>
            <?php foreach ($pendingRequests as $p) { ?>
                <li><?php echo htmlspecialchars($p['pk_username']); ?></li>
            <?php } ?>
        </ul>
    <?php } ?>
</section>

<section>
    <h2><?php print $arrayOfStrings["FriendRequests"] ?? 'Friend Requests'; ?></h2>
    <?php if (empty($incomingRequests)) { ?>
        <p><?php print $arrayOfStrings["NoFriendRequests"] ?? "No incoming requests."; ?></p>
    <?php } else { ?>
        <ul>
            <?php foreach ($incomingRequests as $r) { ?>
                <li>
                    <?php echo htmlspecialchars($r['pk_username'] . ' (' . ($r['firstName'] ?? '') . ' ' . ($r['lastName'] ?? '') . ')'); ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="accept" />
                        <input type="hidden" name="from_user" value="<?php echo htmlspecialchars($r['pk_username']); ?>" />
                        <button type="submit"><?php print $arrayOfStrings['Accept'] ?? 'Accept'; ?></button>
                    </form>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="decline" />
                        <input type="hidden" name="from_user" value="<?php echo htmlspecialchars($r['pk_username']); ?>" />
                        <button type="submit"><?php print $arrayOfStrings['Decline'] ?? 'Decline'; ?></button>
                    </form>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</section>

<section>
    <h2><?php print $arrayOfStrings["CurrentFriends"] ?? 'Your Current Friends:'; ?></h2>
    <?php if (empty($friends)) { ?>
        <p><?php print $arrayOfStrings["NoFriends"] ?? "You have no friends added yet."; ?></p>
    <?php } else { ?>
        <ul>
            <?php foreach ($friends as $f) { ?>
                <li>
                    <?php echo htmlspecialchars($f['pk_username'] . ' (' . ($f['firstName'] ?? '') . ' ' . ($f['lastName'] ?? '') . ')'); ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="end_friendship" />
                        <input type="hidden" name="friend" value="<?php echo htmlspecialchars($f['pk_username']); ?>" />
                        <button type="submit"><?php print $arrayOfStrings['RemoveFriend'] ?? 'Remove Friend'; ?></button>
                    </form>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</section>

</div>
</body>
</html>