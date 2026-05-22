<?php
// Api.php for PIF — small AJAX endpoint for notifications, chat polling, theme/lang
include 'CommonCode.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Theme + language can be set even when not logged in (cookie only)
if ($action === 'set_theme') {
    $theme = isset($_POST['theme']) && in_array($_POST['theme'], ['dark','light']) ? $_POST['theme'] : 'dark';
    setcookie('pif_theme', $theme, time() + 60*60*24*365, '/');
    if (isset($_SESSION['username'])) {
        $_SESSION['theme'] = $theme;
        $stmt = $conn->prepare("UPDATE user SET theme = ? WHERE pk_username = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $theme, $_SESSION['username']);
            $stmt->execute();
            $stmt->close();
        }
    }
    echo json_encode(['ok' => true]);
    exit();
}

if ($action === 'set_lang') {
    $lang = isset($_POST['lang']) && in_array($_POST['lang'], ['en','uk','lb']) ? $_POST['lang'] : 'en';
    setcookie('pif_lang', $lang, time() + 60*60*24*365, '/');
    if (isset($_SESSION['username'])) {
        $_SESSION['language'] = $lang;
        $stmt = $conn->prepare("UPDATE user SET language = ? WHERE pk_username = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $lang, $_SESSION['username']);
            $stmt->execute();
            $stmt->close();
        }
    }
    echo json_encode(['ok' => true]);
    exit();
}

// Everything below requires login
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}
$me = $_SESSION['username'];

// Get notifications
if ($action === 'notifications') {
    echo json_encode(['items' => getNotifications($me, 30)]);
    exit();
}

// Mark all notifications read
if ($action === 'mark_notifs_read') {
    markAllNotificationsRead($me);
    echo json_encode(['ok' => true]);
    exit();
}

// Get chat messages with a friend
if ($action === 'get_messages') {
    $friend = $_GET['with'] ?? '';
    if ($friend === '') { echo json_encode(['messages' => []]); exit(); }
    echo json_encode(['messages' => getChatMessages($me, $friend)]);
    exit();
}

// Send chat message
if ($action === 'send_message') {
    $to   = trim($_POST['to']   ?? '');
    $body = trim($_POST['body'] ?? '');
    if ($to === '' || $body === '') { echo json_encode(['error' => 'empty']); exit(); }
    list($ok, $err) = sendChatMessage($me, $to, $body);
    if ($ok) echo json_encode(['ok' => true]);
    else     echo json_encode(['error' => $err]);
    exit();
}

// Unread message count (used by header polling)
if ($action === 'unread_count') {
    echo json_encode(['count' => getUnreadMessageCount($me)]);
    exit();
}

// Unread per friend
if ($action === 'unread_by_friend') {
    echo json_encode(['unread' => getUnreadMessagesByFriend($me)]);
    exit();
}

// Dashboard live update
if ($action === 'get_dashboard') {
    $stations        = fetchStationsWithLatestMeasurement($me);
    $friendCount     = count(getFriends($me));
    $collectionCount = count(getUserCollections($me));
    $sharedCount     = count(getCollectionsSharedWithUser($me));
    $pendingCount    = count(getIncomingRequests($me));

    $chartSeries = [];
    if (!empty($stations)) {
        $chartSeries = fetchLast24hForStation($stations[0]['pk_serialNumber']);
    }

    $chartData = array_map(function($r) {
        return [
            't'     => substr($r['timestamp'], 0, 16),
            'temp'  => (float)$r['temperature'],
            'hum'   => (float)$r['humidity'],
            'press' => (float)$r['pressure'],
            'lux'   => (float)$r['light'],
            'aqi'   => (float)$r['gas']
        ];
    }, $chartSeries);

    echo json_encode([
        'stations'        => $stations,
        'friendCount'     => $friendCount,
        'collectionCount' => $collectionCount,
        'sharedCount'     => $sharedCount,
        'pendingCount'    => $pendingCount,
        'chartData'       => $chartData
    ]);
    exit();
}

echo json_encode(['error' => 'unknown action']);
?>
