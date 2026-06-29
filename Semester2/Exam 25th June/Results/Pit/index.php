<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    session_start();

    if (isset($_POST["Logout"])) {
        session_unset();
        session_destroy();
        session_start();

        $_SESSION["Country"] = [];
        $_SESSION["UserLogged"] = false;
    }

    if (!isset($_SESSION["CountryName"])) {
        $_SESSION["CountryName"] = [];
    }

    //var_dump($_SESSION);
    if (!isset($_SESSION["UserLogged"])) {
        $_SESSION["UserLogged"] = false;
    }

    $CountryName = trim("CountryName");
    $CountryId = trim("CountryId)");
    $CityName = trim("CityName");
    $PersonName = trim("PersonName");

    $connection = new mysqli("localhost", "root", "", "Ppl");

    $sqlCheck = $connection->prepare("SELECT * FROM Countries WHERE CountryName = ?");
    $sqlCheck->bind_param("s", $CountryName);
    $sqlCheck->execute();
    $resultCheck = $sqlCheck->get_result();


    $sqlCheck = $connection->prepare("SELECT * FROM Cities WHERE CityName = ?");
    $sqlCheck->bind_param("s", $CityName);
    $sqlCheck->execute();
    $resultCheck = $sqlCheck->get_result();

    $sqlCheck = $connection->prepare("SELECT * FROM Ppl WHERE PersonName = ?");
    $sqlCheck->bind_param("s", $PersonName);
    $sqlCheck->execute();
    $resultCheck = $sqlCheck->get_result();
    ?>
    <form name="Country">
        <select name="lang" onchange="this.form.submit()">
            <option value="0">Not Selected</option>
            <option value="1">France</option>
            <option value="2">Germany</option>
            <option value="3">Romania</option>
            <option value="4">Italy</option>
            <option value="5">Spain</option>
            <option value="6">Luxembourg</option>
            <option value="7">Poland</option>
            <option value="8">Netherlands</option>
            <option value="9">Belgium</option>
            <option value="10">Portugal</option>
        </select>
    </form>

    <form name="Cities">
        <select name="lang" onchange="this.form.submit()">
            <option value="0">Not Selected</option>
            <option value="1">Paris</option>
        </select>
    </form>

    <table>
        <tr>
            <th><?= $PersonName["PersonName"] ?? "PersonName" ?></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
</body>

</html>