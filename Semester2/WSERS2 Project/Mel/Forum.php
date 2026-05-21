<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CS:GO Case Shop</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <header>
        <img src="pictures/Logo.png" alt="Logo">
        <h1>CS:GO Case Shop</h1>
    </header>
    <?php
    include_once("commoncode.php");
    Melnav("Forum");


    if (isset($_POST["newMessage"])) {
        $sqlInsert = $connection->prepare("insert into Messages(messageText,username) value(?,?)");
        $msg=htmlspecialchars($_POST["newMessage"]);
        $sqlInsert->bind_param("ss", $msg, $_SESSION["UserLogged"]);
        $sqlInsert->execute();
    }


    ?>
    <div class="container">
        <h1>Forum:</h1>
        <div id="AllPreviousMessages">
            <?php
            $sqlSelect = $connection->prepare("Select * from messages");
            $sqlSelect->execute();
            $result = $sqlSelect->get_result();
            while ($row = $result->fetch_assoc()) {
            ?>
                <div>
                    <?= $arrayOfTranslations["WelcomeForum1"] ?> <b><?= $row["username"] ?></b> <?= $arrayOfTranslations["WelcomeForum2"] ?> <b><?= $row["messageText"] ?></b>
                </div>
            <?php
            }
            ?>

            <div id="NewMessage">
                <form method="POST">
                    <input name="newMessage" placeholder="<?= $arrayOfTranslations["WelcomeForum4"] ?>">
                    <input type="submit" value="<?= $arrayOfTranslations["WelcomeForum3"] ?>">
                </form>
            </div>
        </div>
    </div>
</body>

</html>