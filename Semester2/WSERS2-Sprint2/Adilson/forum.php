<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("forum");

    if (isset($_POST["newMessage"])) {
        $sqlInsert = $connection->prepare("INSERT INTO Messages(messageText, username) values(?,?)");
        $_POST["newMessage"] = htmlspecialchars($_POST["newMessage"]);
        $sqlInsert->bind_param("ss", $_POST["newMessage"], $_SESSION["LoggedUserName"]);
        $sqlInsert->execute();
    }

    if (isset($_POST["removeMessage"])) {
        $sqldelet = $connection->prepare("DELETE FROM messages where id = ?");
        $sqldelet->bind_param("i", $_POST["removeMessage"]);
        $sqldelet->execute();
    }
    ?>
    <h1>Welcom to our forum</h1>
    <div class="page">
        <div class="two-column">
            <div id="allprevMessage" class="card">
                <?php
                $sqlSELECT = $connection->prepare("SELECT * from messages");
                $sqlSELECT->execute();
                $result = $sqlSELECT->get_result();
                while ($row = $result->fetch_assoc()) {
                    $idm = $row["id"];
                ?>
                    <div class="forumMessage">
                        <div class="forumMessageAuthor"><?= htmlspecialchars($row["username"]) ?> <?= ($language == "EN") ? 'wrote:' : 'escreveu:' ?></div>
                        <div class="forumMessageText"><?= nl2br(htmlspecialchars($row["messageText"])) ?></div>
                        <?php if ($_SESSION["UserLogged"] && $_SESSION["ADMIN"] == 1) { ?>
                            <form method="POST" class="deleteMessageForm">
                                <button type="submit" name="removeMessage" value="<?= $idm ?>">Delete comment</button>
                            </form>
                        <?php } ?>
                    </div>
                <?php
                }
                ?>
            </div>
            <div id="newMessage" class="card">
                <form method="POST">
                    <input name="newMessage" placeholder="Type a new message">
                    <input type="submit" value="Send">
                </form>
            </div>
        </div>
    </div>
</body>

</html>