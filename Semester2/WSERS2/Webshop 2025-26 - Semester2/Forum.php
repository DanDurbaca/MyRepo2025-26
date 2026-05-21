<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Forum");

    if (isset($_POST["newMessage"])) {
        $sqlInsert = $connection->prepare("INSERT into Messages(messageText,username) values(?,?)");
        $sqlInsert->bind_param("ss", $_POST["newMessage"], $_SESSION["Username"]);
        $sqlInsert->execute();
    }


    ?>
    <h1>Welcome to our forum messaging space</h1>
    <div id="AllPreviousMessages">
        <?php
        $sqlSelect = $connection->prepare("SELECT * from messages");
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();
        while ($row = $result->fetch_assoc()) {
        ?>
            <div>
                User <?= $row["username"] ?> wrote:<?= $row["messageText"] ?>
            </div>
        <?php
        }
        ?>
    </div>
    <div id="NewMessage">
        <form method="POST">
            <input name="newMessage" placeholder="type a new message">
            <input type="submit" value="Send message">
        </form>

    </div>
</body>

</html>