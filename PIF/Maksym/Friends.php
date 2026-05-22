<?php
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];
$success_message = '';
$error_message = '';
$lastInviteToken = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_request'])) {
        $to = trim($_POST['to_username'] ?? '');
        list($ok, $msg) = sendFriendRequest($username, $to);
        if ($ok) $success_message = $msg; else $error_message = $msg;

    } elseif (isset($_POST['cancel_request'])) {
        $to = trim($_POST['to_username'] ?? '');
        if (cancelFriendRequest($username, $to)) {
            $success_message = 'Request canceled';
        } else {
            $error_message = 'Could not cancel request';
        }

    } elseif (isset($_POST['accept_request'])) {
        $from = trim($_POST['from_username'] ?? '');
        if (acceptFriendRequest($username, $from)) {
            $success_message = 'Friend request accepted';
        } else {
            $error_message = 'Could not accept request';
        }

    } elseif (isset($_POST['decline_request'])) {
        $from = trim($_POST['from_username'] ?? '');
        if (declineFriendRequest($username, $from)) {
            $success_message = 'Friend request declined';
        } else {
            $error_message = 'Could not decline request';
        }

    } elseif (isset($_POST['remove_friend'])) {
        $friendUsername = trim($_POST['friend_username'] ?? '');
        if (removeFriend($username, $friendUsername)) {
            $success_message = 'Friend removed (shared collections unshared)';
        } else {
            $error_message = 'Could not remove friend';
        }

    } elseif (isset($_POST['gen_invite'])) {
        $token = createInvite($username);
        if ($token) {
            $lastInviteToken = $token;
            $success_message = 'Invite link generated';
        } else {
            $error_message = 'Could not generate invite link';
        }
    }
}

$friends  = getFriends($username);
$sent     = getSentRequests($username);
$incoming = getIncomingRequests($username);
$invites  = getRecentInvites($username, 5);

// Build invite URL prefix (scheme + host + dir)
$scheme    = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$inviteBase = $scheme . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/Registration.php?invite=';
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('friends'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
  <?php NavigationBar('friends'); ?>

  <div class="container">
    <div class="page-title"><?php echo t('friends'); ?></div>
    <div class="page-sub"><?php echo t('friends_desc'); ?></div>

    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="grid-2" style="margin-bottom:1.5rem;">

      <!-- Add friend card -->
      <div class="card">
        <div class="card-title"><?php echo t('add_friend'); ?></div>
        <form method="POST" style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:flex-end;">
          <input type="text" name="to_username" placeholder="<?php echo t('username'); ?>" maxlength="50" required style="flex:1; min-width:160px;" />
          <button type="submit" name="send_request"><?php echo t('send_request'); ?></button>
        </form>
      </div>

      <!-- Invite link card -->
      <div class="card">
        <div class="card-title"><?php echo t('invite_link'); ?></div>
        <p style="color:var(--muted); font-size:.82rem; margin-bottom:.8rem;"><?php echo t('invite_desc'); ?></p>
        <form method="POST">
          <button type="submit" name="gen_invite"><?php echo t('generate_invite'); ?></button>
        </form>

        <?php if ($lastInviteToken): ?>
          <div style="margin-top:1rem;">
            <label>Share this link (valid 7 days):</label>
            <input type="text" readonly value="<?php echo htmlspecialchars($inviteBase . $lastInviteToken); ?>" onclick="this.select();" style="width:100%;" />
          </div>
        <?php endif; ?>

        <?php if (!empty($invites)): ?>
          <div style="margin-top:1rem;">
            <?php foreach (array_slice($invites, 0, 3) as $inv): ?>
              <div style="font-size:.78rem; color:var(--muted); margin-top:.4rem; display:flex; justify-content:space-between; gap:.5rem;">
                <span><?php echo $inv['used_by'] ? t('used_by') . ' ' . htmlspecialchars($inv['used_by']) : t('pending'); ?></span>
                <span><?php echo htmlspecialchars(substr($inv['created_at'], 0, 10)); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="tabs">
        <div id="tabFriendsBtn" class="tab-btn active"><?php echo t('friends'); ?> (<?php echo count($friends); ?>)</div>
        <div id="tabSentBtn" class="tab-btn"><?php echo t('sent_requests'); ?> (<?php echo count($sent); ?>)</div>
        <div id="tabIncomingBtn" class="tab-btn"><?php echo t('incoming_requests'); ?> (<?php echo count($incoming); ?>)</div>
      </div>

      <div id="panelFriends">
        <div class="list">
          <?php if (count($friends) === 0): ?>
            <div class="empty"><?php echo t('no_friends'); ?></div>
          <?php endif; ?>
          <?php foreach ($friends as $friend): ?>
            <div class="list-item">
              <div><?php echo htmlspecialchars($friend); ?></div>
              <div>
                <a href="Chat.php?with=<?php echo urlencode($friend); ?>"><button type="button" class="btn-sm"><?php echo t('chat'); ?></button></a>
                <form method="POST" style="display:inline; margin:0;">
                  <input type="hidden" name="friend_username" value="<?php echo htmlspecialchars($friend); ?>" />
                  <button type="submit" name="remove_friend" class="danger btn-sm" onclick="return confirm('Remove this friend? Shared collections will be unshared.');"><?php echo t('remove_friend'); ?></button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div id="panelSent" class="hidden">
        <div class="list">
          <?php if (count($sent) === 0): ?>
            <div class="empty"><?php echo t('no_friends'); ?></div>
          <?php endif; ?>
          <?php foreach ($sent as $to): ?>
            <div class="list-item">
              <div><?php echo htmlspecialchars($to); ?></div>
              <form method="POST" style="margin:0;">
                <input type="hidden" name="to_username" value="<?php echo htmlspecialchars($to); ?>" />
                <button type="submit" name="cancel_request" class="danger btn-sm"><?php echo t('cancel'); ?></button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div id="panelIncoming" class="hidden">
        <div class="list">
          <?php if (count($incoming) === 0): ?>
            <div class="empty"><?php echo t('no_friends'); ?></div>
          <?php endif; ?>
          <?php foreach ($incoming as $from): ?>
            <div class="list-item">
              <div><?php echo htmlspecialchars($from); ?></div>
              <div>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="from_username" value="<?php echo htmlspecialchars($from); ?>" />
                  <button type="submit" name="accept_request" class="btn-sm"><?php echo t('accept'); ?></button>
                </form>
                <form method="POST" style="display:inline; margin-left:.3rem;">
                  <input type="hidden" name="from_username" value="<?php echo htmlspecialchars($from); ?>" />
                  <button type="submit" name="decline_request" class="danger btn-sm"><?php echo t('decline'); ?></button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      function activate(tab) {
        $('#tabFriendsBtn, #tabSentBtn, #tabIncomingBtn').removeClass('active');
        $('#panelFriends, #panelSent, #panelIncoming').addClass('hidden');
        if (tab === 'friends')  { $('#tabFriendsBtn').addClass('active');  $('#panelFriends').removeClass('hidden'); }
        if (tab === 'sent')     { $('#tabSentBtn').addClass('active');     $('#panelSent').removeClass('hidden'); }
        if (tab === 'incoming') { $('#tabIncomingBtn').addClass('active'); $('#panelIncoming').removeClass('hidden'); }
      }
      $('#tabFriendsBtn').on('click',  function() { activate('friends'); });
      $('#tabSentBtn').on('click',     function() { activate('sent'); });
      $('#tabIncomingBtn').on('click', function() { activate('incoming'); });
    });
  </script>
</body>
</html>
