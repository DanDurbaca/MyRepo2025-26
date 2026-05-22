<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';
require_login();
$mysqli = db_connect();
$uid = current_user_id();

// Get current language from settings
$res_lang = $mysqli->query("SELECT language FROM env_user_settings WHERE usr_ref='". $mysqli->real_escape_string($uid) ."' LIMIT 1");
$lang = 'en';
if ($res_lang && $res_lang->num_rows) {
    $row = $res_lang->fetch_assoc();
    $lang = $row['language'];
}
$t = get_translations($lang);

$selected_friend = isset($_GET['friend']) ? $mysqli->real_escape_string($_GET['friend']) : '';
$msg = '';

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $to = $mysqli->real_escape_string($_POST['to']);
    $content = $mysqli->real_escape_string($_POST['message']);
    
    // Verify friendship
    $stmt = $mysqli->prepare("SELECT usr_main FROM env_friend WHERE (usr_main=? AND usr_friend=?) OR (usr_main=? AND usr_friend=?) LIMIT 1");
    $stmt->bind_param('ssss', $uid, $to, $to, $uid);
    $stmt->execute();
    $res_check = $stmt->get_result();
    
    if ($res_check && $res_check->num_rows) {
        $stmt2 = $mysqli->prepare("INSERT INTO env_chat_message (msg_from, msg_to, msg_content) VALUES (?, ?, ?)");
        $stmt2->bind_param('sss', $uid, $to, $content);
        $stmt2->execute();
        
        // Create notification
        $notif_title = sprintf($t['notifications_friend_request'], htmlspecialchars($uid));
        $mysqli->query("INSERT INTO env_notification (notif_to, notif_type, notif_title, notif_message, notif_related_user) VALUES ('". $to ."', 'chat', '". $mysqli->real_escape_string($notif_title) ."', '". $mysqli->real_escape_string($content) ."', '". $uid ."')");
        
        $selected_friend = $to;
    } else {
        $msg = 'Cannot send message to this user';
    }
}

// Get user's friends
$friends = [];
$rf = $mysqli->query("SELECT CASE WHEN usr_main='". $mysqli->real_escape_string($uid) ."' THEN usr_friend ELSE usr_main END as friend_name FROM env_friend WHERE usr_main='". $mysqli->real_escape_string($uid) ."' OR usr_friend='". $mysqli->real_escape_string($uid) ."'");
if ($rf) while ($r = $rf->fetch_assoc()) {
    $stmt = $mysqli->prepare("SELECT usr_name, usr_first, usr_last FROM env_user WHERE usr_name=? LIMIT 1");
    $stmt->bind_param('s', $r['friend_name']);
    $stmt->execute();
    $rr = $stmt->get_result();
    if ($rr && $rr->num_rows) $friends[] = $rr->fetch_assoc();
}

// Get messages for selected friend
$messages = [];
if ($selected_friend) {
    // Mark messages as read
    $mysqli->query("UPDATE env_chat_message SET msg_read=1 WHERE msg_from='". $selected_friend ."' AND msg_to='". $uid ."'");
    
    $stmt = $mysqli->prepare("SELECT msg_from, msg_content, msg_created FROM env_chat_message WHERE (msg_from=? AND msg_to=?) OR (msg_from=? AND msg_to=?) ORDER BY msg_created ASC LIMIT 100");
    $stmt->bind_param('ssss', $uid, $selected_friend, $selected_friend, $uid);
    $stmt->execute();
    $res_msgs = $stmt->get_result();
    while ($r = $res_msgs->fetch_assoc()) {
        $messages[] = $r;
    }
}
?>
<?php include 'header.php'; ?>

<h2><?php echo htmlspecialchars($t['chat_heading']); ?></h2>

<?php if (!empty($msg)): ?>
    <div class="notice"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 250px 1fr; gap: 16px; margin-top: 16px;">
    <!-- Friends List -->
    <div style="border-right: 1px solid var(--border-color); padding-right: 16px;">
        <h3><?php echo htmlspecialchars($t['nav_friends']); ?></h3>
        <?php if ($friends): ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($friends as $f): ?>
                    <li style="margin-bottom: 8px;">
                        <a href="?friend=<?php echo htmlspecialchars($f['usr_name']); ?>" 
                           style="display: block; padding: 8px; background: <?php echo $selected_friend === $f['usr_name'] ? 'var(--primary-color)' : 'var(--border-color)'; ?>; color: <?php echo $selected_friend === $f['usr_name'] ? 'white' : 'var(--text-color)'; ?>; text-decoration: none; border-radius: 4px;">
                            <?php echo htmlspecialchars($f['usr_first'] ?: $f['usr_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="muted"><?php echo htmlspecialchars($t['chat_no_friends']); ?></p>
        <?php endif; ?>
    </div>

    <!-- Chat Area -->
    <div>
        <?php if ($selected_friend): ?>
            <h3><?php printf($t['chat_conversation'], htmlspecialchars($selected_friend)); ?></h3>
            
            <div class="chat-container">
                <div class="chat-messages">
                    <?php if (empty($messages)): ?>
                        <p class="muted text-center mt-16"><?php echo htmlspecialchars($t['chat_select_friend']); ?></p>
                    <?php else: ?>
                        <?php foreach ($messages as $msg_row): ?>
                            <div class="chat-message <?php echo $msg_row['msg_from'] === $uid ? 'sent' : 'received'; ?>">
                                <strong><?php echo htmlspecialchars($msg_row['msg_from']); ?></strong><br>
                                <?php echo htmlspecialchars($msg_row['msg_content']); ?>
                                <br><small style="opacity: 0.7;"><?php echo htmlspecialchars($msg_row['msg_created']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="chat-input">
                    <form method="post" style="display: flex; gap: 8px; width: 100%;">
                        <input type="hidden" name="to" value="<?php echo htmlspecialchars($selected_friend); ?>">
                        <input type="text" name="message" placeholder="<?php echo htmlspecialchars($t['chat_type_message']); ?>" required style="flex: 1;">
                        <button class="btn" type="submit" name="send_message"><?php echo htmlspecialchars($t['chat_send']); ?></button>
                    </form>
                </div>
            </div>
            
            <script>
            // Auto-scroll to bottom
            const chatMessages = document.querySelector('.chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
            </script>
        <?php else: ?>
            <p class="muted"><?php echo htmlspecialchars($t['chat_select_friend']); ?></p>
        <?php endif; ?>
    </div>
</div>

</main>
<?php include 'footer.php'; ?>
