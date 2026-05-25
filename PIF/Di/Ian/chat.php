<?php
session_start();
require __DIR__ . '/assets/db.php';

if (!isset($_SESSION['username'])) {
    header('Location: /login.php');
    exit;
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$username = $_SESSION['username'];
$pdo = getDb();

$error = '';
$success = '';
$chatWith = '';
$chatDisplay = '';
$messages = [];
$friends = [];

// Load friends list
$friendsStmt = $pdo->prepare('SELECT CASE WHEN pkfk_user_user = :me THEN pkfk_user_friend ELSE pkfk_user_user END AS friend
                               FROM isfriend
                               WHERE (pkfk_user_user = :me OR pkfk_user_friend = :me) AND isaccepted = 1
                               ORDER BY friend');
$friendsStmt->execute([':me' => $username]);
$friends = array_column($friendsStmt->fetchAll(), 'friend');

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send_message') {
        $to = trim($_POST['to'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (!$to || !$message) {
            $error = 'Recipient and message are required.';
        } elseif (!in_array($to, $friends)) {
            $error = 'You can only message friends.';
        } else {
            try {
                $insertStmt = $pdo->prepare('INSERT INTO chat_message (from_username, to_username, message) VALUES (:from, :to, :msg)');
                $insertStmt->execute([':from' => $username, ':to' => $to, ':msg' => $message]);
                $chatWith = $to;
                // Redirect after successful send to avoid duplicate submissions on reload
                header('Location: /chat.php?with=' . urlencode($to));
                exit;
            } catch (PDOException $e) {
                $error = 'Failed to send message.';
            }
        }
    } elseif ($action === 'mark_read') {
        $with = trim($_POST['with'] ?? '');
        if ($with && in_array($with, $friends)) {
            $updateStmt = $pdo->prepare('UPDATE chat_message SET read_at = NOW() WHERE from_username = :from AND to_username = :me AND read_at IS NULL');
            $updateStmt->execute([':from' => $with, ':me' => $username]);
        }
        $chatWith = $with;
    }
}

// Get chat partner from URL or POST
if (!$chatWith && isset($_GET['with'])) {
    $chatWith = trim($_GET['with']);
}

// Validate chat partner
if ($chatWith && !in_array($chatWith, $friends)) {
    $error = 'Invalid chat partner.';
    $chatWith = '';
}

// Resolve display name (first name if available)
if ($chatWith) {
    $nameStmt = $pdo->prepare('SELECT firstName, lastName FROM user WHERE pk_username = :u');
    $nameStmt->execute([':u' => $chatWith]);
    if ($row = $nameStmt->fetch()) {
        $first = trim($row['firstName'] ?? '');
        $chatDisplay = $first ?: $chatWith;
    } else {
        $chatDisplay = $chatWith;
    }
}

// Load messages for selected conversation
if ($chatWith) {
    $messagesStmt = $pdo->prepare('SELECT pk_message_id, from_username, to_username, message, sent_at, read_at 
                                    FROM chat_message 
                                    WHERE (from_username = :me AND to_username = :friend) 
                                       OR (from_username = :friend AND to_username = :me)
                                    ORDER BY sent_at ASC');
    $messagesStmt->execute([':me' => $username, ':friend' => $chatWith]);
    $messages = $messagesStmt->fetchAll();
    
    // Auto-mark incoming messages as read
    $markReadStmt = $pdo->prepare('UPDATE chat_message SET read_at = NOW() WHERE from_username = :friend AND to_username = :me AND read_at IS NULL');
    $markReadStmt->execute([':friend' => $chatWith, ':me' => $username]);
}

// Get unread counts per friend
$unreadCounts = [];
foreach ($friends as $friend) {
    $unreadStmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM chat_message WHERE from_username = :friend AND to_username = :me AND read_at IS NULL');
    $unreadStmt->execute([':friend' => $friend, ':me' => $username]);
    $count = $unreadStmt->fetch()['cnt'] ?? 0;
    if ($count > 0) {
        $unreadCounts[$friend] = $count;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/style.css">
    <title>Chat</title>
    <style>
        .chat-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 20px;
            height: calc(100vh - 150px);
            max-width: 1400px;
            margin: 0 auto;
        }
        .chat-sidebar {
            background: #ffffff;
            border-radius: 6px;
            border: 1px solid var(--card-border);
            overflow-y: auto;
        }
        html[data-theme="dark"] .chat-sidebar {
            background: #2a2a2e;
        }
        .chat-friend-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--card-border);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.15s;
        }
        .chat-friend-item:hover {
            background: #f9fafc;
        }
        html[data-theme="dark"] .chat-friend-item:hover {
            background: #333340;
        }
        .chat-friend-item.active {
            background: #e8f4e8;
            border-left: 3px solid var(--green);
        }
        html[data-theme="dark"] .chat-friend-item.active {
            background: #2a3a2a;
        }
        .unread-badge {
            background: var(--red);
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }
        .chat-main {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border-radius: 6px;
            border: 1px solid var(--card-border);
            overflow: hidden;
        }
        html[data-theme="dark"] .chat-main {
            background: #2a2a2e;
        }
        .chat-header {
            padding: 16px;
            border-bottom: 1px solid var(--card-border);
            font-weight: 600;
            font-size: 16px;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .chat-message {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.4;
        }
        .chat-message.sent {
            align-self: flex-end;
            background: var(--green);
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }
        .chat-message.received {
            align-self: flex-start;
            background: #f0f0f5;
            color: var(--text);
            border-bottom-left-radius: 4px;
        }
        html[data-theme="dark"] .chat-message.received {
            background: #333340;
        }
        .chat-timestamp {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 4px;
        }
        .chat-input-area {
            padding: 16px;
            border-top: 1px solid var(--card-border);
            display: flex;
            gap: 8px;
        }
        .chat-input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid var(--card-border);
            border-radius: 20px;
            font-size: 14px;
            background: var(--bg);
            color: var(--text);
        }
        .chat-send-btn {
            padding: 10px 20px;
            border-radius: 20px;
        }
        .chat-empty {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .chat-layout {
                grid-template-columns: 1fr;
                height: auto;
            }
            .chat-sidebar {
                max-height: 200px;
            }
            .chat-main {
                height: 500px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/assets/header.php'; ?>

    <main class="container" style="max-width: 1400px; width: 100%; padding: 20px;">
        <h1>Chat with Friends</h1>

        <?php if ($error): ?>
            <div class="alert danger"><?php echo h($error); ?></div>
        <?php endif; ?>

        <?php if (!$friends): ?>
            <div class="card">
                <p class="muted">You have no friends yet. <a href="/friends.php">Add friends</a> to start chatting.</p>
            </div>
        <?php else: ?>
            <div class="chat-layout">
                <!-- Sidebar: Friends List -->
                <div class="chat-sidebar">
                    <?php foreach ($friends as $friend): ?>
                        <a href="/chat.php?with=<?php echo urlencode($friend); ?>" 
                           class="chat-friend-item <?php echo $chatWith === $friend ? 'active' : ''; ?>"
                           style="text-decoration: none; color: inherit; display: flex;">
                            <span><?php echo h($friend); ?></span>
                            <?php if (isset($unreadCounts[$friend])): ?>
                                <span class="unread-badge"><?php echo $unreadCounts[$friend]; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Main: Chat Area -->
                <div class="chat-main">
                    <?php if (!$chatWith): ?>
                        <div class="chat-empty">
                            <p>Select a friend to start chatting</p>
                        </div>
                    <?php else: ?>
                        <div class="chat-header">
                            Chat with <?php echo h($chatDisplay ?: $chatWith); ?>
                        </div>

                        <div class="chat-messages" id="chatMessages">
                            <?php if (empty($messages)): ?>
                                <p class="muted" style="text-align: center;">No messages yet. Start the conversation!</p>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="chat-message <?php echo $msg['from_username'] === $username ? 'sent' : 'received'; ?>">
                                        <div><?php echo nl2br(h($msg['message'])); ?></div>
                                        <div class="chat-timestamp">
                                            <?php echo date('M j, g:i A', strtotime($msg['sent_at'])); ?>
                                            <?php if ($msg['from_username'] === $username && $msg['read_at']): ?>
                                                <span title="Read">✓✓</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <form method="post" class="chat-input-area">
                            <input type="hidden" name="action" value="send_message">
                            <input type="hidden" name="to" value="<?php echo h($chatWith); ?>">
                            <input type="text" 
                                   name="message" 
                                   class="chat-input" 
                                   placeholder="Type a message..." 
                                   required 
                                   autofocus
                                   maxlength="1000">
                            <button type="submit" class="primary-btn chat-send-btn">Send</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Auto-scroll to bottom of messages
        const messagesDiv = document.getElementById('chatMessages');
        if (messagesDiv) {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // Prevent refresh while user is typing
        let isTyping = false;
        const inputEl = document.querySelector('.chat-input');
        if (inputEl) {
            inputEl.addEventListener('input', () => {
                isTyping = inputEl.value.trim().length > 0;
            });
            inputEl.addEventListener('focus', () => { isTyping = true; });
            inputEl.addEventListener('blur', () => {
                // Only clear typing flag if the field is empty
                if (inputEl.value.trim().length === 0) {
                    isTyping = false;
                }
            });
        }

        // Auto-refresh messages every 5 seconds if in a chat and not typing
        <?php if ($chatWith): ?>
        setInterval(() => {
            if (!isTyping) {
                window.location.reload();
            }
        }, 5000);
        <?php endif; ?>
    </script>

    <?php include 'assets/footer.php'; ?>
</body>
</html>
