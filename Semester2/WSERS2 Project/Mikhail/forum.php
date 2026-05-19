<!DOCTYPE html>
<html>

<head>
    <title>Forum</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include_once("common.php");
    head("Forum");
    if(isset($_POST["newMessage"])){
        $sqlQuery=$connection->prepare("insert into messages(messagetext, username) values(?,?)");
        $msg=htmlspecialchars($_POST["newMessage"]);
        $sqlQuery->bind_param("ss", $msg, $_SESSION["UserLogged"]);
        $sqlQuery->execute();
    }
    ?>
    <main class="home">
        <h1><?=$arrayOfTranslations["ForumH1"]?></h1>
        <div id="AllPreviousMessages">
            <?php 
            $sqlQuery=$connection->prepare("select * from messages");
            $sqlQuery->execute();
            $result=$sqlQuery->get_result();
            while($row=$result->fetch_assoc()){
                ?>
                <?=$arrayOfTranslations["ForumMs1"]?> <?= $row["username"] ?> <?=$arrayOfTranslations["ForumMs2"]?> <?= $row["messageText"] ?><?php if ($_SESSION["IsAdmin"]) {?><form method="POST"><input type="hidden" value="<?= $row["messageid"] ?>" name="messagetodelete"></input><input type="submit" value="<?=$arrayOfTranslations["CartTb4"]?>"></form><?php }?><br>
                <?php
            }
            ?>
        </div>
        <div id="NewMessage">
            <form method="POST">
                <input name="newMessage" placeholder="<?=$arrayOfTranslations["ForumIn1"]?>">
                <input type="submit" value="<?=$arrayOfTranslations["ForumIn2"]?>">
            </form>
        </div>
        </div>
    </main>
    <?php
    foot();
    ?>
</body>

</html>