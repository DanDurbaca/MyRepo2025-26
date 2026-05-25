<?php
session_start();
require __DIR__ . '/assets/db.php';

if (!isset($_SESSION['username'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/assets/mailer.php';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$username = $_SESSION['username'];
$pdo = getDb();

$errors = $_SESSION['flash_errors'] ?? [];
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_errors'], $_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add_friend') {
            $to = trim($_POST['to'] ?? '');
            if (!$to) {
                $errors[] = 'Username is required.';
            } elseif ($to === $username) {
                $errors[] = 'You cannot add yourself.';
            } else {
                $exists = $pdo->prepare('SELECT 1 FROM user WHERE pk_username = :u');
                $exists->execute([':u' => $to]);
                if (!$exists->fetch()) {
                    $errors[] = 'User not found.';
                } else {
                    $existing = $pdo->prepare('SELECT pkfk_user_user, pkfk_user_friend, isaccepted FROM isfriend WHERE (pkfk_user_user = :me AND pkfk_user_friend = :to) OR (pkfk_user_user = :to AND pkfk_user_friend = :me) LIMIT 1');
                    $existing->execute([':me' => $username, ':to' => $to]);
                    $row = $existing->fetch();
                    if ($row) {
                        if ((int)$row['isaccepted'] === 1) {
                            $errors[] = 'You are already friends.';
                        } elseif ($row['pkfk_user_user'] === $to && $row['pkfk_user_friend'] === $username) {
                            // They already sent you a pending request; accept it automatically
                            $upd = $pdo->prepare('UPDATE isfriend SET isaccepted = 1 WHERE pkfk_user_user = :from AND pkfk_user_friend = :me');
                            $upd->execute([':from' => $to, ':me' => $username]);
                            $_SESSION['flash_success'] = 'Friend request accepted.';
                        } else {
                            $errors[] = 'Request already pending.';
                        }
                    } else {
                        $ins = $pdo->prepare('INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend, isaccepted) VALUES (:u, :f, 0)');
                        $ins->execute([':u' => $username, ':f' => $to]);
                        $_SESSION['flash_success'] = 'Friend request sent.';
                    }
                }
            }
        } elseif ($action === 'accept') {
            $from = trim($_POST['from'] ?? '');
            if (!$from) {
                $errors[] = 'Request user is required.';
            } else {
                $upd = $pdo->prepare('UPDATE isfriend SET isaccepted = 1 WHERE pkfk_user_user = :from AND pkfk_user_friend = :me AND isaccepted = 0');
                $upd->execute([':from' => $from, ':me' => $username]);
                if ($upd->rowCount() === 0) {
                    $errors[] = 'Request not found.';
                } else {
                    $_SESSION['flash_success'] = 'Friend request accepted.';
                }
            }
        } elseif ($action === 'decline') {
            $from = trim($_POST['from'] ?? '');
            if (!$from) {
                $errors[] = 'Request user is required.';
            } else {
                $del = $pdo->prepare('DELETE FROM isfriend WHERE pkfk_user_user = :from AND pkfk_user_friend = :me AND isaccepted = 0');
                $del->execute([':from' => $from, ':me' => $username]);
                $_SESSION['flash_success'] = 'Friend request declined.';
            }
        } elseif ($action === 'cancel_request') {
            $to = trim($_POST['to'] ?? '');
            if ($to) {
                $del = $pdo->prepare('DELETE FROM isfriend WHERE pkfk_user_user = :me AND pkfk_user_friend = :to AND isaccepted = 0');
                $del->execute([':me' => $username, ':to' => $to]);
                $_SESSION['flash_success'] = 'Request cancelled.';
            }
        } elseif ($action === 'unfriend') {
            $who = trim($_POST['username'] ?? '');
            if (!$who) {
                $errors[] = 'Username is required.';
            } else {
                $del = $pdo->prepare('DELETE FROM isfriend WHERE (pkfk_user_user = :me AND pkfk_user_friend = :who) OR (pkfk_user_user = :who AND pkfk_user_friend = :me)');
                $del->execute([':me' => $username, ':who' => $who]);
                $_SESSION['flash_success'] = 'Friend removed.';
            }
        } elseif ($action === 'invite') {
            $email = trim($_POST['email'] ?? '');
            if (!$email) {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email address.';
            } else {
                // Check if user already exists with this email
                $exists = $pdo->prepare('SELECT pk_username FROM user WHERE email = :e');
                $exists->execute([':e' => $email]);
                if ($exists->fetch()) {
                    $errors[] = 'User with this email already exists. Send them a friend request instead.';
                } else {
                    // Check for existing unused invitation
                    $existingInv = $pdo->prepare('SELECT pk_invitation_token FROM invitation WHERE from_username = :me AND email = :e AND used_at IS NULL AND expires_at > NOW()');
                    $existingInv->execute([':me' => $username, ':e' => $email]);
                    if ($existingInv->fetch()) {
                        $errors[] = 'You have already sent an invitation to this email.';
                    } else {
                        // Generate token
                        $token = bin2hex(random_bytes(32));
                        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
                        $ins = $pdo->prepare('INSERT INTO invitation (pk_invitation_token, from_username, email, expires_at) VALUES (:t, :u, :e, :exp)');
                        $ins->execute([':t' => $token, ':u' => $username, ':e' => $email, ':exp' => $expiresAt]);
                        // Send email
                        $userStmt = $pdo->prepare('SELECT firstName, lastName FROM user WHERE pk_username = :u');
                        $userStmt->execute([':u' => $username]);
                        $userData = $userStmt->fetch();
                        $fromName = ($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? '');
                        $fromName = trim($fromName) ?: $username;
                        if (send_invitation_email($email, $fromName, $token)) {
                            $_SESSION['flash_success'] = 'Invitation sent to ' . $email;
                        } else {
                            $errors[] = 'Invitation created but email failed to send. Check logs.';
                        }
                    }
                }
            }
        }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

// Load friends list (accepted)
$friends = [];
$q = $pdo->prepare('SELECT CASE WHEN pkfk_user_user = :me THEN pkfk_user_friend ELSE pkfk_user_user END AS friend
                     FROM isfriend
                     WHERE (pkfk_user_user = :me OR pkfk_user_friend = :me) AND isaccepted = 1
                     ORDER BY friend');
$q->execute([':me' => $username]);
$friends = array_column($q->fetchAll(), 'friend');

// Load requests
$incomingReq = [];
$outgoingReq = [];
$inStmt = $pdo->prepare('SELECT pkfk_user_user AS from_username FROM isfriend WHERE pkfk_user_friend = :me AND isaccepted = 0 ORDER BY pkfk_user_user');
$inStmt->execute([':me' => $username]);
$incomingReq = array_column($inStmt->fetchAll(), 'from_username');

$outStmt = $pdo->prepare('SELECT pkfk_user_friend AS to_username FROM isfriend WHERE pkfk_user_user = :me AND isaccepted = 0 ORDER BY pkfk_user_friend');
$outStmt->execute([':me' => $username]);
$outgoingReq = array_column($outStmt->fetchAll(), 'to_username');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/style.css">
    <title>Friends - Account</title>
</head>
<body>
    <?php include __DIR__ . '/assets/header.php'; ?>

    <main class="container">
        <h1>Friends</h1>

        <?php if ($errors): ?>
            <div class="alert danger">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo h($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <div class="alert success"><?php echo h($success); ?></div>
        <?php endif; ?>

        <section id="send-request">
            <h2 class="section-title">Send Friend Request</h2>
            <div class="card">
                <form method="post" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="hidden" name="action" value="add_friend">
                    <label class="field-label" for="to-username">Username</label>
                    <input class="input-text" id="to-username" name="to" type="text" required>
                    <button class="primary-btn" type="submit">Send Request</button>
                </form>
            </div>
        </section>

        <section id="invite-user">
            <h2 class="section-title">Invite Someone to Join</h2>
            <div class="card">
                <form method="post" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="hidden" name="action" value="invite">
                    <label class="field-label" for="invite-email">Email Address</label>
                    <input class="input-text" id="invite-email" name="email" type="email" placeholder="friend@example.com" required>
                    <button class="primary-btn" type="submit">Send Invitation</button>
                </form>
                <p class="help-text" style="margin-top:8px;">They'll receive an email with a signup link and you'll automatically become friends.</p>
            </div>
        </section>

        <section id="incoming-requests">
            <h2 class="section-title">Incoming Requests</h2>
            <div class="card">
                <?php if (!$incomingReq): ?>
                    <p class="muted">No incoming requests.</p>
                <?php else: ?>
                    <div class="stations-list">
                        <?php foreach ($incomingReq as $from): ?>
                            <div class="friend-row">
                                <span><?php echo h($from); ?></span>
                                <div style="margin-left:auto; display:flex; gap:8px;">
                                    <form method="post">
                                        <input type="hidden" name="action" value="accept">
                                        <input type="hidden" name="from" value="<?php echo h($from); ?>">
                                        <button class="primary-btn" type="submit">Accept</button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="action" value="decline">
                                        <input type="hidden" name="from" value="<?php echo h($from); ?>">
                                        <button class="danger-btn" type="submit">Decline</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="outgoing-requests">
            <h2 class="section-title">Outgoing Requests</h2>
            <div class="card">
                <?php if (!$outgoingReq): ?>
                    <p class="muted">No outgoing requests.</p>
                <?php else: ?>
                    <div class="stations-list">
                        <?php foreach ($outgoingReq as $to): ?>
                            <div class="friend-row">
                                <span><?php echo h($to); ?></span>
                                <form method="post" style="margin-left:auto;">
                                    <input type="hidden" name="action" value="cancel_request">
                                    <input type="hidden" name="to" value="<?php echo h($to); ?>">
                                    <button class="danger-btn" type="submit">Cancel</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="friends-list-section">
            <h2 class="section-title">Your Friends</h2>
            <div class="card">
                <?php if (!$friends): ?>
                    <p class="muted">No friends yet.</p>
                <?php else: ?>
                    <div class="stations-list">
                        <?php foreach ($friends as $f): ?>
                            <div class="friend-row">
                                <span><?php echo h($f); ?></span>
                                <form method="post" style="display:inline-block; margin-left:auto;">
                                    <input type="hidden" name="action" value="unfriend">
                                    <input type="hidden" name="username" value="<?php echo h($f); ?>">
                                    <button class="danger-btn" type="submit" onclick="return confirm('Unfriend <?php echo h($f); ?>?');">Unfriend</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>
  <?php include 'assets/footer.php'; ?>

</body>
</html>
