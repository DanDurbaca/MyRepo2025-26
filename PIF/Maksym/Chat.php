<?php
include 'CommonCode.php';
requireLogin();

$me = $_SESSION['username'];

// Get friends list (with names) for the sidebar
$friends = getFriendsWithInfo($me);

// Selected chat partner from URL or first friend
$withUser = $_GET['with'] ?? ($friends[0]['pk_username'] ?? '');

// Validate that the selected user is actually a friend
$isFriend = false;
foreach ($friends as $f) {
    if ($f['pk_username'] === $withUser) { $isFriend = true; break; }
}
if (!$isFriend) $withUser = $friends[0]['pk_username'] ?? '';

// Unread counts per friend (for the sidebar badges)
$unreadMap = getUnreadMessagesByFriend($me);
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('chat'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
  <?php NavigationBar('chat'); ?>

  <div class="container">
    <div class="page-title"><?php echo t('chat'); ?></div>
    <div class="page-sub"><?php echo t('select_friend'); ?></div>

    <?php if (count($friends) === 0): ?>
      <div class="card"><div class="empty"><?php echo t('add_friends_first'); ?> <a href="Friends.php" style="color:var(--accent);"><?php echo t('go_to_friends'); ?></a></div></div>
    <?php else: ?>
      <div class="chat-wrap">

        <!-- Friend list sidebar -->
        <div class="chat-sidebar">
          <?php foreach ($friends as $f): ?>
            <div class="chat-friend <?php echo $f['pk_username'] === $withUser ? 'active' : ''; ?>"
                 data-user="<?php echo htmlspecialchars($f['pk_username']); ?>"
                 onclick="selectChat('<?php echo htmlspecialchars($f['pk_username']); ?>', '<?php echo htmlspecialchars(addslashes($f['firstName'] . ' ' . $f['lastName'])); ?>')">
              <span style="font-size:1.2rem;">&#128100;</span>
              <div style="flex:1; min-width:0;">
                <div style="font-size:.85rem; font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($f['firstName'] . ' ' . $f['lastName']); ?></div>
                <div style="font-size:.72rem; color:var(--muted);"><?php echo htmlspecialchars($f['pk_username']); ?></div>
              </div>
              <?php if (!empty($unreadMap[$f['pk_username']])): ?>
                <span class="unread-dot" id="badge-<?php echo htmlspecialchars($f['pk_username']); ?>"><?php echo (int)$unreadMap[$f['pk_username']]; ?></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Chat main area -->
        <div class="chat-main">
          <div class="chat-header" id="chatHeader">
            <?php
            foreach ($friends as $f) {
                if ($f['pk_username'] === $withUser) {
                    echo htmlspecialchars($f['firstName'] . ' ' . $f['lastName']);
                    break;
                }
            }
            ?>
          </div>
          <div class="chat-messages" id="chatMessages">
            <div class="empty" style="padding:2rem; align-self:center;">Loading...</div>
          </div>
          <div class="chat-input-row">
            <input type="text" id="msgInput" placeholder="<?php echo t('type_message'); ?>" autocomplete="off" />
            <button type="button" id="sendBtn"><?php echo t('send'); ?></button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>
    var currentWith    = <?php echo json_encode($withUser); ?>;
    var myUsername     = <?php echo json_encode($me); ?>;
    var noMessagesText = <?php echo json_encode(t('no_messages_yet')); ?>;

    function esc(s) { return $('<span>').text(s || '').html(); }

    function selectChat(username, name) {
      currentWith = username;
      $('.chat-friend').removeClass('active');
      $('.chat-friend[data-user="' + username + '"]').addClass('active');
      $('#chatHeader').text(name);
      $('#badge-' + username).remove();
      loadMessages();
      history.pushState({}, '', 'Chat.php?with=' + encodeURIComponent(username));
    }

    function loadMessages() {
      if (!currentWith) return;
      $.getJSON('Api.php?action=get_messages&with=' + encodeURIComponent(currentWith), function(data) {
        var $msgs = $('#chatMessages');
        $msgs.empty();
        if (!data.messages || data.messages.length === 0) {
          $msgs.html('<div class="empty" style="align-self:center; padding:2rem;">' + noMessagesText + '</div>');
          return;
        }
        var html = '';
        $.each(data.messages, function(i, m) {
          var isMine = m.fk_sender === myUsername;
          html += '<div class="chat-msg ' + (isMine ? 'mine' : 'theirs') + '">' +
                  esc(m.body) +
                  '<div class="chat-msg-time">' + esc(m.sent_at) + '</div></div>';
        });
        $msgs.html(html);
        $msgs[0].scrollTop = $msgs[0].scrollHeight;
      });
    }

    function sendMessage() {
      var body = $('#msgInput').val().trim();
      if (!body || !currentWith) return;
      $('#msgInput').val('');
      $.post('Api.php', { action: 'send_message', to: currentWith, body: body }, function(data) {
        if (data.ok) loadMessages();
      }, 'json');
    }

    $(document).ready(function() {
      if (currentWith) loadMessages();
      $('#sendBtn').on('click', sendMessage);
      $('#msgInput').on('keypress', function(e) { if (e.key === 'Enter') sendMessage(); });
      setInterval(function() { if (currentWith) loadMessages(); }, 3000);
    });
  </script>
</body>
</html>
