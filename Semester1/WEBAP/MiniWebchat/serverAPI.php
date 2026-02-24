<?php
$connection = new mysqli("localhost", "root", "", "MiniWebchat");

if (isset($_POST["UserName"])) {
    $sqlFindUser = $connection->prepare("SELECT * from Users where userName=?");
    $sqlFindUser->bind_param("s", $_POST["UserName"]);
    $sqlFindUser->execute();
    $result = $sqlFindUser->get_result();
    if ($result->num_rows == 0) {
        print("New user in the database... We will create it now");
        $sqlCreateUser = $connection->prepare("INSERT INTO Users(userName) VALUES(?)");
        $sqlCreateUser->bind_param("s", $_POST["UserName"]);
        $sqlCreateUser->execute();
        // user created
        print("<br>User was created");
    } else {
        print("User already in our db");
    }
}

if (isset($_POST["user"], $_POST["message"])) {
    $sqlCreateMessage = $connection->prepare("INSERT INTO Messages(MessageText,userId) VALUES(?,(SELECT userId from Users where userName=?))");
    $sqlCreateMessage->bind_param("ss", $_POST["message"], $_POST["user"]);
    $sqlCreateMessage->execute();
    print("Message saved into the database");
}

if (isset($_GET["lastSeenMessage"])) {
    $sqlSelectMessages = $connection->prepare("SELECT * from Messages natural join Users where MessageId>?");
    $sqlSelectMessages->bind_param("i", $_GET["lastSeenMessage"]);
    $sqlSelectMessages->execute();
    $result = $sqlSelectMessages->get_result();
    while ($row = $result->fetch_assoc()) {
?>
        <div>
            <span><?= $row["MessageId"] ?>.</span><?= $row["userName"] ?> wrote: <?= $row["MessageText"] ?>
        </div>
<?php
    }
}
