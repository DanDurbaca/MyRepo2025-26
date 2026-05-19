<!DOCTYPE html>
<html lang="en">

<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($arrayOfTranslations["ProfileBtn"] ?? "Profile", ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <?php
    include_once("nav.php");
    NavigationBar($arrayOfTranslations["ProfileBtn"] ?? "Profile");
    ?>
    <!-- <div class="Login">
        <button type="button">Login</button>
    </div> -->

    <h1><?= $arrayOfTranslations["ProfileLogin"] ?>:</h1>

    <?php
    $bShowForm = true;

    $connection = new mysqli("localhost", "root", "", "4PageWebsite");

    if (isset($_POST["Username"], $_POST["psw"])) {
        $bShowForm = false;

        $username = trim($_POST["Username"]);
        $password = $_POST["psw"];

        $sqlQuery = $connection->prepare("SELECT Username, UserPassword, UserType FROM Clients WHERE Username = ?");
        $sqlQuery->bind_param("s", $username);
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row["UserPassword"])) {
                $_SESSION["UserLogged"] = true;
                $_SESSION["Username"] = $row["Username"];
                $_SESSION["IsAdmin"] = ($row["UserType"] === "admin");

                header("Location: index.php");
                exit;
            } else {
                $bShowForm = true;
                echo htmlspecialchars($arrayOfTranslations["ProfileError"], ENT_QUOTES, 'UTF-8');
            }
        } else {
            $bShowForm = true;
            echo htmlspecialchars($arrayOfTranslations["ProfileUserNotExist"], ENT_QUOTES, 'UTF-8');
        }
    }


    if ($bShowForm) {
    ?>
        <form method="POST">
            <div> <?= $arrayOfTranslations["ProfileUsername"] ?>: </div>
            <input type="text" name="Username"><br>
            <div> <?= $arrayOfTranslations["ProfilePassword"] ?>: </div>
            <input type="password" name="psw"><br><br>
            <input type="submit" value="Login">
        </form>
    <?php
    }
    ?>
</body>

</html>