<?php
// Friends page: manage friend requests, accept/decline, and notify users by email
$pageTitle = 'Friends';
require_once '../config.php';
require_once __DIR__ . '/../_header.php';
require_once __DIR__ . '/../inc/csrf.php';

// Check login
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}
$username = $_SESSION['username'];

    // Handle form submissions
    $message = '';
    $messageType = 'info';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            if (isset($_POST['add_friend'])) {
                if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                    $message = 'Invalid CSRF token.';
                    $messageType = 'danger';
                } else {
                    $friend_username = trim($_POST['friend_username'] ?? '');
                    if ($friend_username === '') {
                        $message = 'Please enter a username.';
                        $messageType = 'warning';
                    } elseif ($friend_username === $username) {
                        $message = 'You cannot add yourself.';
                        $messageType = 'warning';
                    } else {
                        $stmt = $pdo->prepare("SELECT pk_username FROM `user` WHERE pk_username = ?");
                        $stmt->execute([$friend_username]);
                        $friend = $stmt->fetch();
                        if (!$friend) {
                            $message = 'User not found.';
                            $messageType = 'warning';
                        } else {
                            // Use canonical username casing from DB to avoid case mismatches later.
                            $friend_username = $friend['pk_username'];
                            $chk = $pdo->prepare("SELECT 1 FROM isfriend WHERE pkfk_user_user = ? AND pkfk_user_friend = ? LIMIT 1");
                            $chk->execute([$username, $friend_username]);
                            if ($chk->fetch()) {
                                $message = 'You are already friends with this user.';
                                $messageType = 'info';
                            } else {
                                $rq = $pdo->prepare("SELECT id, status FROM friend_request WHERE from_user = ? AND to_user = ? LIMIT 1");
                                $rq->execute([$username, $friend_username]);
                                $existing = $rq->fetch();
                                if ($existing && $existing['status'] === 'pending') {
                                    $message = 'Friend request already sent.';
                                    $messageType = 'info';
                                } else {
                                    if ($existing) {
                                        $pdo->prepare("UPDATE friend_request SET status = 'pending', created_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$existing['id']]);
                                    } else {
                                        $ins = $pdo->prepare("INSERT INTO friend_request (from_user, to_user, status) VALUES (?, ?, 'pending')");
                                        $ins->execute([$username, $friend_username]);
                                    }
                                    require_once __DIR__ . '/../inc/mail.php';
                                    $acceptUrl = sprintf('%s/user/friends.php', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
                                    $body = "<p>User <strong>" . htmlspecialchars($username) . "</strong> has sent you a friend request.</p><p>Visit <a href=\"" . $acceptUrl . "\">your Friends page</a> to accept or decline.</p>";
                                    $stmt2 = $pdo->prepare("SELECT email, firstName FROM `user` WHERE pk_username = ? LIMIT 1");
                                    $stmt2->execute([$friend_username]);
                                    $u = $stmt2->fetch();
                                    if ($u && !empty($u['email'])) {
                                        send_mail($u['email'], 'Friend request', $body);
                                    }
                                    $message = 'Friend request sent.';
                                    $messageType = 'success';
                                }
                            }
                        }
                    }
                }
            } elseif (isset($_POST['accept_request'])) {
                if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                    $message = 'Invalid CSRF token.';
                    $messageType = 'danger';
                } else {
                    $req_id = intval($_POST['request_id'] ?? 0);
                    if ($req_id <= 0) {
                        $message = 'Invalid request.';
                        $messageType = 'danger';
                    } else {
                        $rq = $pdo->prepare("SELECT from_user FROM friend_request WHERE id = ? AND to_user = ? AND status = 'pending' LIMIT 1");
                        $rq->execute([$req_id, $username]);
                        $row = $rq->fetch();
                        if (!$row) {
                            $message = 'Request not found or already handled.';
                            $messageType = 'warning';
                        } else {
                            $from = $row['from_user'];
                            $pdo->prepare("INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (?, ?) ON DUPLICATE KEY UPDATE pkfk_user_user = pkfk_user_user")->execute([$username, $from]);
                            $pdo->prepare("INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (?, ?) ON DUPLICATE KEY UPDATE pkfk_user_user = pkfk_user_user")->execute([$from, $username]);
                            $pdo->prepare("UPDATE friend_request SET status = 'accepted' WHERE id = ?")->execute([$req_id]);
                            require_once __DIR__ . '/../inc/mail.php';
                            $stmt3 = $pdo->prepare('SELECT email, firstName FROM `user` WHERE pk_username = ? LIMIT 1');
                            $stmt3->execute([$from]);
                            $ruser = $stmt3->fetch();
                            if ($ruser && !empty($ruser['email'])) {
                                $body = "<p>Your friend request to <strong>" . htmlspecialchars($username) . "</strong> was accepted.</p><p>Visit <a href=\"" . sprintf('%s/user/friends.php', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')) . "\">your Friends page</a> to see your friends.</p>";
                                send_mail($ruser['email'], 'Friend request accepted', $body);
                            }
                            $message = 'Friend request accepted.';
                            $messageType = 'success';
                        }
                    }
                }
            } elseif (isset($_POST['decline_request'])) {
                if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                    $message = 'Invalid CSRF token.';
                    $messageType = 'danger';
                } else {
                    $req_id = intval($_POST['request_id'] ?? 0);
                    if ($req_id <= 0) {
                        $message = 'Invalid request.';
                        $messageType = 'danger';
                    } else {
                        $rq = $pdo->prepare("SELECT from_user FROM friend_request WHERE id = ? AND to_user = ? AND status = 'pending' LIMIT 1");
                        $rq->execute([$req_id, $username]);
                        $row = $rq->fetch();
                        if (!$row) {
                            $message = 'Request not found or already handled.';
                            $messageType = 'warning';
                        } else {
                            $pdo->prepare("UPDATE friend_request SET status = 'declined' WHERE id = ?")->execute([$req_id]);
                            $message = 'Friend request declined.';
                            $messageType = 'info';
                        }
                    }
                }
            } elseif (isset($_POST['remove_friend'])) {
                if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                    $message = 'Invalid CSRF token.';
                    $messageType = 'danger';
                } else {
                    $friend_username = $_POST['remove_friend'];
                    // Delete friendship (both directions)
                    $pdo->prepare("DELETE FROM isfriend WHERE (pkfk_user_user = ? AND pkfk_user_friend = ?) OR (pkfk_user_user = ? AND pkfk_user_friend = ?)")->execute([$username, $friend_username, $friend_username, $username]);

                    // Automatically unshare collections between these two users:
                    // 1) Remove access entries where current user had shared collections with friend
                    $del1 = $pdo->prepare("DELETE h FROM hasaccess h JOIN collection c ON h.pkfk_collection = c.pk_collection WHERE h.pkfk_user = ? AND c.fk_user_creates = ?");
                    $del1->execute([$friend_username, $username]);
                    // 2) Remove access entries where friend had shared collections with current user
                    $del2 = $pdo->prepare("DELETE h FROM hasaccess h JOIN collection c ON h.pkfk_collection = c.pk_collection WHERE h.pkfk_user = ? AND c.fk_user_creates = ?");
                    $del2->execute([$username, $friend_username]);

                    $message = 'Friend removed.';
                    $messageType = 'success';
                }
            }
        } catch (Exception $e) {
            $message = 'Server error while processing request. Please try again later.';
            $messageType = 'danger';
            error_log('Friends error: ' . $e->getMessage());
        }
    }
    ?>
    <div class="container">
        <h1>Friends</h1>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Add Friend</h3>
            <form method="post">
                <?php echo csrf_input(); ?>
                <div class="form-group">
                    <label for="friend_username">Friend's Username</label>
                    <input type="text" id="friend_username" name="friend_username" required>
                </div>
                <button type="submit" name="add_friend" class="btn">Send Friend Request</button>
            </form>
        </div>

        <div class="card">
            <h3>Incoming Requests</h3>
            <?php
            $rq = $pdo->prepare("SELECT id, from_user, created_at FROM friend_request WHERE to_user = ? AND status = 'pending' ORDER BY created_at DESC");
            $rq->execute([$username]);
            $requests = $rq->fetchAll();
            if (empty($requests)) {
                echo '<p>No pending requests.</p>';
            } else {
                echo '<table><thead><tr><th>From</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
                foreach ($requests as $r) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['from_user']); ?></td>
                        <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                <button type="submit" name="accept_request" class="btn btn-small">Accept</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                <button type="submit" name="decline_request" class="btn btn-danger btn-small">Decline</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
                echo '</tbody></table>';
            }
            ?>
        </div>

        <div class="card">
            <h3>Your Friends</h3>
            <?php
            $stmt = $pdo->prepare("SELECT u.pk_username FROM `user` u JOIN isfriend f ON u.pk_username = f.pkfk_user_friend WHERE f.pkfk_user_user = ?");
            $stmt->execute([$username]);
            $friends = $stmt->fetchAll();
            if (empty($friends)) {
                echo '<p>No friends yet.</p>';
            } else {
                echo '<table><thead><tr><th>Username</th><th>Actions</th></tr></thead><tbody>';
                foreach ($friends as $friend) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($friend['pk_username']); ?></td>
                        <td>
                            <button class="btn btn-danger btn-small" onclick="confirmRemoveFriend('<?php echo addslashes($friend['pk_username']); ?>')">Remove</button>
                        </td>
                    </tr>
                    <?php
                }
                echo '</tbody></table>';
            }
            ?>
        </div>

        <div class="card">
            <h3>Shared Collections</h3>
            <?php
            $stmt = $pdo->prepare("SELECT c.pk_collection, c.name, c.fk_user_creates AS shared_by FROM collection c JOIN hasaccess h ON c.pk_collection = h.pkfk_collection WHERE h.pkfk_user = ?");
            $stmt->execute([$username]);
            $shared = $stmt->fetchAll();
            if (empty($shared)) {
                echo '<p>No shared collections.</p>';
            } else {
                echo '<table><thead><tr><th>Collection</th><th>Shared By</th></tr></thead><tbody>';
                foreach ($shared as $s) {
                    ?>
                    <tr>
                        <td><a href="view_collection.php?id=<?php echo urlencode($s['pk_collection']); ?>"><?php echo htmlspecialchars($s['name']); ?></a></td>
                        <td><?php echo htmlspecialchars($s['shared_by']); ?></td>
                    </tr>
                    <?php
                }
                echo '</tbody></table>';
            }
            ?>
        </div>
    </div>

    <form id="removeFriendForm" method="post" style="display: none;">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="remove_friend" id="removeFriendUsername">
    </form>

    <script>
    // Show confirmation dialog and submit form to remove a friend (also unshares collections)
    function confirmRemoveFriend(username) {
        Swal.fire({
            title: 'Remove Friend?',
            text: `Are you sure you want to remove ${username} as a friend? This will also unshare any collections.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('removeFriendUsername').value = username;
                document.getElementById('removeFriendForm').submit();
            }
        });
    }
    </script>
</body>
</html>