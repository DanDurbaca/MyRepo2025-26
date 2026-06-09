<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Forum");
    ?>

    <h1>Forum</h1>

    <?php
    if (isset($_SESSION['logged_in_user']) && isset($_POST["postMessage"]) && trim($_POST["postMessage"]) != "") {
        $sqlInsertPost = $connection->prepare("INSERT INTO Forum (postUsername, postMessage, postDate) VALUES (?, ?, NOW())");
        $sqlInsertPost->bind_param("ss", $_SESSION['logged_in_user'], $_POST["postMessage"]);
        $sqlInsertPost->execute();
        print("Your message was posted!<br><br>");
    }

    if (isset($_SESSION['logged_in_user'])) {
        ?>
        <form method="POST">
            <div>Write a message:</div>
            <textarea name="postMessage" rows="4" cols="50"></textarea>
            <br>
            <input type="submit" value="Post">
        </form>
        <br>
        <?php
    } else {
        print("<p>You must be logged in to post a message.</p>");
    }
    ?>

    <h2>Posts</h2>

    <?php
    $sqlSelectPosts = $connection->prepare("SELECT postUsername, postMessage, postDate FROM Forum ORDER BY postDate DESC");
    $sqlSelectPosts->execute();
    $sqlResultPosts = $sqlSelectPosts->get_result();

    if ($sqlResultPosts->num_rows == 0) {
        print("<p>No posts yet. Be the first to write something!</p>");
    }

    while ($onePost = $sqlResultPosts->fetch_assoc()) {
        ?>
        <div class="forum-post">
            <strong><?= htmlspecialchars($onePost["postUsername"]) ?></strong> - <?= htmlspecialchars($onePost["postDate"]) ?>
            <p><?= htmlspecialchars($onePost["postMessage"]) ?></p>
        </div>
        <?php
    }
    ?>

</body>

</html>
