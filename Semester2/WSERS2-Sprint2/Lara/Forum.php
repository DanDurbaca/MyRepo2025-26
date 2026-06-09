<?php
include_once("CommonCode.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["newMessage"])) {
    $msg = trim($_POST["newMessage"]);
    if (!empty($msg)) {
        $sqlInsert = $connection->prepare("INSERT INTO Messages (messageText, username) VALUES (?, ?)");
        $sqlInsert->bind_param("ss", $msg, $_SESSION["Username"]);
        $sqlInsert->execute();

        header("Location: Forum.php?lang=" . $language);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $arrayOfTranslations["ForumTitle"] ?? "Guild Forum" ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Press+Start+2P&family=VT323&display=swap" rel="stylesheet">
</head>

<body>

    <?php
    includeCSS("Forum.css");
    NavigationBar($arrayOfTranslations["ForumTitle"] ?? "Forum");
    ?>

    <h2 class="cart-title"><?= strtoupper($arrayOfTranslations["ForumTitle"] ?? "GUILD FORUM") ?></h2>

    <div class="cart-wrapper">

        <div id="AllPreviousMessages">
            <?php
            $sqlSelect = $connection->prepare("SELECT * FROM Messages");
            $sqlSelect->execute();
            $result = $sqlSelect->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                    <div class="forum-message">
                        <span class="message-user"><?= htmlspecialchars($row["username"]) ?>:</span>
                        <span class="message-text"><?= htmlspecialchars($row["messageText"]) ?></span>
                    </div>
            <?php
                }
            } else {
                echo "<p style='text-align:center; font-family:VT323; font-size:1.5rem;'>" .
                    ($arrayOfTranslations["ForumNoMessages"] ?? "No scrolls found... be the first to speak!") .
                    "</p>";
            }
            ?>
        </div>

        <div id="NewMessage">
            <?php if (isset($_SESSION["UserLogged"]) && $_SESSION["UserLogged"] === true): ?>
                <form method="POST" class="forum-input-container">
                    <input type="text" name="newMessage" class="forum-input"
                        placeholder="<?= $arrayOfTranslations["ForumPlaceholder"] ?? "Share your wisdom..." ?>"
                        required autocomplete="off">
                    <input type="submit" class="forum-submit"
                        value="<?= $arrayOfTranslations["ForumPostBtn"] ?? "POST" ?>">
                </form>
            <?php else: ?>
                <p style="text-align:center; font-family:VT323; color:#ffbcff; font-size:1.4rem;">
                    <?= $arrayOfTranslations["ForumLoginRequired"] ?? "Only registered guild members may post. Please Login." ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>