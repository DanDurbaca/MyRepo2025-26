<?php include_once("function.php"); ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $arrayOfTranslations['Forum'][$language] ?? 'Forum' ?></title>

    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>
    <?php
    NavigationBar($page="Forum");


    if (!isset($_SESSION['logged_in_user'])) {
        ?>
        <div class="forum-locked" style="padding:24px; max-width:800px; margin:24px auto; border:1px solid #ddd; background:#fff;">
            <h2><?= $arrayOfTranslations['Forum'][$language] ?? 'Community Forum' ?></h2>
            <p><?= htmlspecialchars($arrayOfTranslations['PleaseLogin'][$language] ?? 'You must be logged in to view and post in the forum.') ?></p>
            <p><a class="btn-new-thread" href="login.php?language=<?= $language ?>"><?= $arrayOfTranslations['Login'][$language] ?? 'Login' ?></a></p>
        </div>
        <?php
    } else {

      
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
           
            $isAdmin = (isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'] === true) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
            if ($isAdmin) {
                $deleteId = intval($_POST['delete_message_id']);
                if ($deleteId > 0) {
                    $sqlDel = $mysqli->prepare("DELETE FROM Messages WHERE id = ?");
                    if ($sqlDel) {
                        $sqlDel->bind_param("i", $deleteId);
                        $sqlDel->execute();
                        $sqlDel->close();
                    }
                }
            }

            $redir = 'forum.php';
            if (!empty($language)) {
                $redir .= '?language=' . urlencode($language);
            }
            header('Location: ' . $redir);
            exit;
        }

        if(isset($_POST['newMessage']) && !empty(trim($_POST['newMessage']))) {
            $username = $_SESSION['logged_in_user'];
            $msg = trim($_POST['newMessage']);
            $sqlInsert = $mysqli->prepare("INSERT INTO Messages(messageText, username) VALUES (?, ?)");
            if($sqlInsert) {
                $sqlInsert->bind_param("ss", $msg, $username);
                $sqlInsert->execute();
                $sqlInsert->close();
            }
        }
        ?>

        <div class="forum-container">
            <div class="forum-header">
                <h2><?= $arrayOfTranslations['Forum'][$language] ?? 'Community Forum' ?></h2>
                <div class="forum-actions">   
                    <button class="btn-new-thread" onclick="document.getElementById('new-thread-input').focus(); return false;"><?= $arrayOfTranslations['NewMessage'][$language] ?? 'New Message' ?></button>
                </div>
            </div>

            <form class="new-thread" method="POST">
                <input id="new-thread-input" name="newMessage" type="text" placeholder="<?= $arrayOfTranslations['TypeYourMessage'][$language] ?? 'Type your message here...' ?>" required />
                <input class="btn-new-thread" type="submit" value="<?= $arrayOfTranslations['Send'][$language] ?? 'Send' ?>" />
            </form>

            <div class="thread-list">
                <?php
                $sqlSelect = $mysqli->prepare("SELECT * FROM Messages ORDER BY id DESC");
                if($sqlSelect) {
                    $sqlSelect->execute();
                    $result = $sqlSelect->get_result();
                    while($row = $result->fetch_assoc()) {
                        $safeUser = htmlspecialchars($row['username']);
                        $safeText = htmlspecialchars($row['messageText']);
                        $time = isset($row['created_at']) ? htmlspecialchars($row['created_at']) : '';
                ?>
                    <article class="thread-card">
                        <div class="thread-main">
                            <div class="thread-title"><?= $safeUser ?></div>
                            <div class="thread-excerpt"><?= $safeText ?></div>
                            <?php if($time): ?>
                                <div class="thread-meta"><span><?= $time ?></span></div>
                            <?php endif; ?>
                        </div>
                        <div class="thread-stats">
                            <span class="stat">&nbsp;</span>
                            <?php
                             
                                $isAdmin = (isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'] === true) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
                                if ($isAdmin):
                            ?>
                                <form method="POST" style="display:inline-block; margin-left:8px;">
                                    <input type="hidden" name="delete_message_id" value="<?= intval(
                                        $row['id'] ?? 0
                                    ) ?>">
                                    <button class="btn-delete" type="submit" onclick="return confirm('Are you sure you want to delete this message?');">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php
                    }
                    $sqlSelect->close();
                }
                ?>
            </div>
        </div>

    <?php
    }
    ?>

</body>
</html>