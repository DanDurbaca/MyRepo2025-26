<?php
include_once "ccode.php";
include_once "navbar.php";

if (!$_SESSION["userLogged"]) {
    header("Location: welcome.php?lang=" . $lang);
    exit;
}

navbar($tArray["forumBtn"]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Mentorship Shop</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <?php
    if (isset($_POST['forumMsg']) && trim($_POST['forumMsg']) !== '') {
        $user = $myMentorshipShopDB->prepare("SELECT ID, username FROM clients WHERE email = ?;");
        $user->bind_param("s", $_SESSION['email']);
        $user->execute();

        $result = $user->get_result();
        $row = $result->fetch_assoc();

        $add = $myMentorshipShopDB->prepare("INSERT INTO forumMsgs (msg, clientID) VALUES (?, ?);");
        $add->bind_param("si", $_POST['forumMsg'], $row['ID']);
        $add->execute();

        header("Location: forum.php?lang=" . $lang);
        exit;
    }

    $get = $myMentorshipShopDB->query("SELECT forumMsgs.msg, clients.username, clients.userType FROM forumMsgs JOIN clients ON forumMsgs.clientID = clients.ID ORDER BY forumMsgs.ID DESC;");
    ?>

    <main class="page">
        <section class="forum-wrap">
            <div class="forum-header">
                <h1><?= $tArray["forumBtn"] ?></h1>
                <p class="muted"> <?= $tArray['forumMsgDiv1']; ?></p>
            </div>

            <form class="forum-form" method="post">
                <label for="forumMsg">
                    <?= htmlspecialchars($tArray["Msg"]) ?>
                </label>
                <textarea id="forumMsg" name="forumMsg" required
                    placeholder="<?= $tArray['forumMsgHelper']; ?>"></textarea>
                <button type="submit">
                    <?= $tArray["SendReg"] ?>
                </button>
            </form>

            <div class="forum-feed">
                <?php if ($get && $get->num_rows > 0) { ?>
                    <?php while ($row = $get->fetch_assoc()) { ?>
                        <article class="forum-msg">
                            <div class="forum-msg-top">
                                <span class="forum-user">
                                    <?php echo htmlspecialchars($row['username'] . ' (' . $row['userType'] . ')'); ?>
                                </span>
                            </div>

                            <div class="forum-text">
                                <?= htmlspecialchars($row['msg']) . "<br>" ?>
                            </div>
                        </article>
                    <?php } ?>
                <?php } else { ?>
                    <div class="forum-empty">
                        <?= $tArray['forumNoMsgs']; ?>
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>

</body>

</html>