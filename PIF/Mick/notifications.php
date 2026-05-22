<?php
// notifications.php - User Notifications Management

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

// Mark notification as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notif_id = intval($_POST['notif_id']);
    $mysqli->query("UPDATE env_notification SET notif_read=1 WHERE notif_id=". $notif_id ." AND notif_to='". $mysqli->real_escape_string($uid) ."'");
}

// Delete notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notif'])) {
    $notif_id = intval($_POST['notif_id']);
    $mysqli->query("DELETE FROM env_notification WHERE notif_id=". $notif_id ." AND notif_to='". $mysqli->real_escape_string($uid) ."'");
}

// Get user notifications
$notifications = [];
$res = $mysqli->query("SELECT notif_id, notif_type, notif_title, notif_message, notif_related_user, notif_read, notif_created FROM env_notification WHERE notif_to='". $mysqli->real_escape_string($uid) ."' ORDER BY notif_created DESC LIMIT 50");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Get unread count
$unread_count = 0;
$res_count = $mysqli->query("SELECT COUNT(*) as cnt FROM env_notification WHERE notif_to='". $mysqli->real_escape_string($uid) ."' AND notif_read=0");
if ($res_count) {
    $row = $res_count->fetch_assoc();
    $unread_count = $row['cnt'];
}
?>
<?php include 'header.php'; ?>

<h2><?php echo htmlspecialchars($t['notifications_heading']); ?></h2>

<?php if ($unread_count > 0): ?>
    <div class="notice">
        <?php echo htmlspecialchars($unread_count); ?> unread notifications
    </div>
<?php endif; ?>

<?php if ($notifications): ?>
    <table>
        <tr>
            <th>Type</th>
            <th>Message</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($notifications as $notif): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($notif['notif_type']); ?></strong></td>
                <td>
                    <?php echo htmlspecialchars($notif['notif_title']); ?>
                    <?php if ($notif['notif_message']): ?>
                        <br><small><?php echo htmlspecialchars(substr($notif['notif_message'], 0, 100)); ?></small>
                    <?php endif; ?>
                </td>
                <td><small><?php echo htmlspecialchars($notif['notif_created']); ?></small></td>
                <td><?php echo $notif['notif_read'] ? '✓ Read' : '○ Unread'; ?></td>
                <td>
                    <?php if (!$notif['notif_read']): ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="notif_id" value="<?php echo $notif['notif_id']; ?>">
                            <button class="btn" name="mark_read" style="padding: 4px 8px; font-size: 0.85rem;">Mark Read</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="notif_id" value="<?php echo $notif['notif_id']; ?>">
                        <button class="btn danger" name="delete_notif" style="padding: 4px 8px; font-size: 0.85rem;">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p class="muted"><?php echo htmlspecialchars($t['notifications_no_notifications']); ?></p>
<?php endif; ?>

</main>
<?php include 'footer.php'; ?>
