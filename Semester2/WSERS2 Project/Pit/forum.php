<?php
include_once("nav.php");

if (empty($_SESSION["UserLogged"])) {
    header("Location: profile.php?lang=" . $language);
    exit;
}

$isAdmin = !empty($_SESSION["IsAdmin"]) && (
    $_SESSION["IsAdmin"] === true ||
    $_SESSION["IsAdmin"] === 1 ||
    $_SESSION["IsAdmin"] === "1" ||
    strtolower((string)$_SESSION["IsAdmin"]) === "true" ||
    strtolower((string)$_SESSION["IsAdmin"]) === "admin"
);

if (isset($_POST["deleteMessageId"]) && $isAdmin) {
    $messageId = intval($_POST["deleteMessageId"]);

    $sqlGetMessage = $connection->prepare("SELECT Username FROM messages WHERE id = ?");
    $sqlGetMessage->bind_param("i", $messageId);
    $sqlGetMessage->execute();
    $messageResult = $sqlGetMessage->get_result();

    if ($messageRow = $messageResult->fetch_assoc()) {
        $deletedUsername = $messageRow["Username"];

        if ($deletedUsername !== "System") {
            $sqlDelete = $connection->prepare("DELETE FROM messages WHERE id = ?");
            $sqlDelete->bind_param("i", $messageId);
            $sqlDelete->execute();

            $systemUsername = $_SESSION["Username"];
            $systemMessage = "[SYSTEM] A message from " . $deletedUsername . " was deleted by an administrator.";

            $sqlSystemMessage = $connection->prepare(
                "INSERT INTO messages (messageText, Username) VALUES (?, ?)"
            );
            $sqlSystemMessage->bind_param("ss", $systemMessage, $systemUsername);
            $sqlSystemMessage->execute();
        }
    }

    header("Location: forum.php?lang=" . $language);
    exit;
}

if (isset($_POST["newMessage"]) && !empty($_SESSION["Username"])) {
    $message = trim($_POST["newMessage"]);
    $username = trim($_SESSION["Username"]);

    if ($message !== "") {
        $sqlInsert = $connection->prepare(
            "INSERT INTO messages (messageText, Username) VALUES (?, ?)"
        );
        $sqlInsert->bind_param("ss", $message, $username);

        if ($sqlInsert->execute()) {
            header("Location: forum.php?lang=" . $language);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="style.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($arrayOfTranslations["ForumBtn"] ?? "Forum", ENT_QUOTES, 'UTF-8') ?>
    </title>
</head>

<body>
    <?php NavigationBar($arrayOfTranslations["ForumBtn"] ?? "Forum"); ?>

    <h1>
        <?= htmlspecialchars($arrayOfTranslations["ForumTitle"] ?? "Forum", ENT_QUOTES, 'UTF-8') ?>
    </h1>

    <div id="AllPreviousMessages">
        <?php
        $sqlSelect = $connection->prepare("SELECT * FROM messages ORDER BY id ASC");
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();

        while ($row = $result->fetch_assoc()) {
            $messageUsername = $row["Username"];
            $isSystemMessage = str_starts_with($row["messageText"], "[SYSTEM]");
        ?>
            <div class="forum-message <?= $isSystemMessage ? 'system-message' : '' ?>">
                <div class="forum-message-text">
                    <?php if ($isSystemMessage) { ?>
                        <strong>System:</strong>
                        <?= htmlspecialchars(str_replace("[SYSTEM] ", "", $row["messageText"]), ENT_QUOTES, 'UTF-8') ?>
                    <?php } else { ?>
                        <?= htmlspecialchars($arrayOfTranslations["ForumUser"] ?? "User", ENT_QUOTES, 'UTF-8') ?>
                        <?= htmlspecialchars($messageUsername, ENT_QUOTES, 'UTF-8') ?>
                        <?= htmlspecialchars($arrayOfTranslations["ForumWrote"] ?? "wrote", ENT_QUOTES, 'UTF-8') ?>:
                        <?= htmlspecialchars($row["messageText"], ENT_QUOTES, 'UTF-8') ?>
                    <?php } ?>
                </div>

                <?php if ($isAdmin && !$isSystemMessage) { ?>
                    <form method="POST" class="delete-message-form">
                        <input
                            type="hidden"
                            name="deleteMessageId"
                            value="<?= intval($row["id"]) ?>">

                        <button type="submit" class="delete-message-btn">
                            Delete
                        </button>
                    </form>
                <?php } ?>
            </div>
        <?php
        }
        ?>
    </div>

    <div id="NewMessage">
        <form method="POST">
            <input
                type="text"
                name="newMessage"
                required
                maxlength="255"
                placeholder="<?= htmlspecialchars($arrayOfTranslations["ForumPlaceholder"] ?? "Type a message", ENT_QUOTES, 'UTF-8') ?>">

            <input
                type="submit"
                value="<?= htmlspecialchars($arrayOfTranslations["ForumSendBtn"] ?? "Send", ENT_QUOTES, 'UTF-8') ?>">
        </form>
    </div>
</body>

</html>