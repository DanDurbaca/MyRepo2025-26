<!DOCTYPE html>
<html>

<head>
    <title>Best Holiday Destinations</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include_once("common.php");
    head("Login");
    ?>
    <main class="register">
        <h1><?= $arrayOfTranslations["LoginH1"] ?></h1>
        <br>
        <?php
        $showForm = true;
        if (isset($_POST["Username"], $_POST["psw"])) {
            $showForm = false;
            $success = false;
            $admin = false;
            if ($_POST["psw"] == null || $_POST["Username"] == null) {
                $showForm = true;
                print($arrayOfTranslations["LoginOut1"]);
            } else {
		        $sqlQuery = $connection -> prepare("select * from Clients");
		        $sqlQuery->execute();
		        $result=$sqlQuery->get_result();
                while ($row=$result->fetch_assoc()) {
                    if ($row["Username"] == $_POST["Username"] && password_verify($_POST["psw"], $row["HashPassword"])){
                        (trim($row["UserType"]) == "admin") ? $admin = true : "";
                        $success = true;
                        break;
                    }
                }
                if ($success) {
                    print($arrayOfTranslations["LoginOut2"]);
                    $_SESSION["UserLogged"] = $_POST["Username"];
                    $_SESSION["IsAdmin"] = $admin;
                    header("Refresh:0; url=home.php?lang=".$language);
                } else {
                    print($arrayOfTranslations["LoginOut3"]);
                    $showForm = true;
                }
            }
        }
        if ($showForm) {
        ?>
            <form method="POST">
                <label><?= $arrayOfTranslations["LoginLabel1"] ?></label>
                <input type="test" name="Username">
                <br>
                <br>
                <label><?= $arrayOfTranslations["LoginLabel2"] ?></label>
                <input type="password" name="psw">
                <br>
                <br>
                <input type="submit" value="<?= $arrayOfTranslations["LoginLabel3"] ?>">
            </form>
        <?php
        }
        ?>
    </main>
    <?php
    foot();
    ?>
</body>

</html>