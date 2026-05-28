<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
requireLogin();

header("Content-Type: application/json; charset=UTF-8");

$currentUserId = (int)$_SESSION["user_id"];
$pendingFriendRequests = 0;
$unreadChatCount = 0;

if (hasTable($conn, "friend_requests")) {
  $stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS total
    FROM friend_requests
    WHERE receiver_user_id = ? AND status = 'pending'
  ");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $currentUserId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $pendingFriendRequests = (int)($row["total"] ?? 0);
  }
}

if (
  hasTable($conn, "chat_rooms") &&
  hasTable($conn, "chat_room_members") &&
  hasTable($conn, "chat_messages") &&
  hasColumn($conn, "chat_room_members", "last_read_message_id")
) {
  $stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS total
    FROM (
      SELECT crm.room_id
      FROM chat_room_members crm
      INNER JOIN chat_messages cm ON cm.room_id = crm.room_id
      WHERE crm.user_id = ?
        AND cm.user_id <> ?
        AND (crm.last_read_message_id IS NULL OR cm.message_id > crm.last_read_message_id)
      GROUP BY crm.room_id
    ) unread_rooms
  ");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $currentUserId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $unreadChatCount = (int)($row["total"] ?? 0);
  }
}

echo json_encode([
  "ok" => true,
  "pending_friend_requests" => $pendingFriendRequests,
  "unread_chats" => $unreadChatCount,
]);
