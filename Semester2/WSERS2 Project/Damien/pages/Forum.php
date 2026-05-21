<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
	<link rel="stylesheet" href="style.css?<?= time() ?>">
	<meta charset="utf-8">
	<title>Forum</title>

</head>
<body>
    <?php 
    include_once("function.php");
    NavigationBar("Forum");

    if (isset($_POST["newMessage"])){
        $sqlInsert = $connection->prepare("INSERT into Messages(messageText,username) value(?,?)");
        $msg = htmlspecialchars($_POST["newMessage"]);
        $sqlInsert->bind_param("ss",$msg, $_SESSION["Userlogged"]);
        $sqlInsert->execute();
    }
    ?>
    <h1><?= $arrayTranslation["ForumLable"] ?></h1>
    <div id="AllPreviousMessage">
    <?php
    $sqlSelect = $connection->prepare("SELECT * from messages");
    $sqlSelect->execute();
    $result = $sqlSelect ->get_result();
    while($row =$result->fetch_assoc()) {
    ?>
        <div>
        <?= $row["username"] ?> <?= $arrayTranslation["ForumMessage"] ?> <?=$row["messageText"]  ?>
        </div>
    <?php
    }
    ?>
    </div>
    <div id="NewMessage">
        <form method="POST">
            <input name="newMessage" placeholder="<?= $arrayTranslation["MessageLable"] ?>">
            <input type="submit" value="<?= $arrayTranslation["SendBtn"] ?>">
</form>
    </body>