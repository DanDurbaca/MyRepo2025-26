<?php
/*
 * friends/friends.php
 * Purpose: Manage friend relationships: send requests, accept/reject and remove friends.
 * Sections:
 *  - Includes: config and auth check
 *  - DB queries: fetch accepted friends and incoming requests
 *  - Renders: form to add friend, lists of requests and friends with action forms
 */
require "../includes/config.php";
require "../includes/auth_check.php";

$user = $_SESSION['username'];

/* Accepted friends (both directions exist) */
$stmt = $pdo->prepare("
    SELECT f1.pkfk_user_friend
    FROM isfriend f1
    JOIN isfriend f2
      ON f1.pkfk_user_friend = f2.pkfk_user_user
     AND f2.pkfk_user_friend = f1.pkfk_user_user
    WHERE f1.pkfk_user_user = ?
");
$stmt->execute([$user]);
$friends = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* Incoming friend requests */
$stmt = $pdo->prepare("
    SELECT pkfk_user_user
    FROM isfriend
    WHERE pkfk_user_friend = ?
      AND pkfk_user_user NOT IN (
          SELECT pkfk_user_friend
          FROM isfriend
          WHERE pkfk_user_user = ?
      )
");
$stmt->execute([$user, $user]);
$requests = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Friends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Friends</h2>

    <!-- ADD FRIEND -->
    <form method="post" action="add_friend.php" class="mb-4">
        <input name="friend"
               class="form-control mb-2"
               placeholder="Username"
               required>
        <button class="btn btn-primary btn-sm">
            Send friend request
        </button>
    </form>

    <!-- FRIEND REQUESTS -->
    <h5>Friend Requests</h5>
    <?php if (count($requests) === 0): ?>
        <p >No pending requests.</p>
    <?php else: ?>
        <ul class="list-group mb-4">
        <?php foreach ($requests as $r): ?>
            <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($r) ?>

                <div class="d-flex gap-2">
                    <form method="post" action="accept_friend.php">
                        <input type="hidden" name="friend" value="<?= htmlspecialchars($r) ?>">
                        <button class="btn btn-sm btn-success">Accept</button>
                    </form>

                    <form method="post" action="reject_friend.php">
                        <input type="hidden" name="friend" value="<?= htmlspecialchars($r) ?>">
                        <button class="btn btn-sm btn-danger">Reject</button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- FRIEND LIST -->
    <h5>Your Friends</h5>
    <?php if (count($friends) === 0): ?>
        <p >You don’t have any friends yet.</p>
    <?php else: ?>
        <ul class="list-group">
        <?php foreach ($friends as $f): ?>
            <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($f) ?>

                <!-- REMOVE FRIEND (per friend) -->
                <form method="post" action="remove_friend.php"
                      onsubmit="return confirm('Remove this friend?');">
                    <input type="hidden" name="friend" value="<?= htmlspecialchars($f) ?>">
                    <button class="btn btn-sm btn-outline-danger">
                        Remove
                    </button>
                </form>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
