<?php
// Ensure admin-only access
include_once("comCode.php");

// Redirect non-admin users
if (!isset($_SESSION["adminLoggedIn"])) {
    header("Location: index.php");
    exit;
}

// Initialize message variable
$message = "";

// Handle form submission BEFORE any HTML output
if (isset($_POST['createUser'])) {
    $userName = $_POST["chUserName"];
    $firstName = $_POST["chLastName"];
    $lastName = $_POST["chFirstName"];
    if (!empty($userName && $firstName && $lastName)) {
        $sqlUpdate = $conn->prepare("insert into station (stationName,descr,userId) values(?,?,?)");
        $sqlUpdate->bind_param("ssi", $userName, $firstName, $lastName);
        if ($sqlUpdate->execute()) {
            header("Refresh:0");
            exit;
        }
    } else {
        $message = "Please fill out every space";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./style.css?<?= time(); ?>">
</head>

<body>
    <nav>
        <?php NavigationBar("database"); ?>
    </nav>
    </nav>
    <main class="indexMain">

    <h2><?= htmlspecialchars($text['allStations']) ?></h2>
    <table class="admin-table">
        <tr>
            <th>Serial Number</th>
            <th>Station Name</th>
            <th>Description</th>
            <th>User Id</th>
        </tr>
        <?php
        //Show friends
        $sqlSelect = $conn->prepare("select * from station");
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();
        while ($row = $result->fetch_assoc()) {
            ?>

            <tr>
                <td>
                    <?= $row["serialNumber"] ?>
                </td>
                <td>
                    <?= $row["stationName"] ?>
                </td>
                <td>
                    <?= $row["descr"] ?>
                </td>
                <td>
                    <?= $row["userId"] ?>
                </td>
                <td>
                    <a href="./editStation.php?id=<?= $row["serialNumber"] ?>"><?= htmlspecialchars($text['staAdd']) ?></a>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>

    <h2><?= htmlspecialchars($text['createStation']) ?></h2>
        <form method="POST">
            <div>
                <label for="">Station Name </label>
                <input type="text" name="chUserName" id="">
            </div>

            <div>
                <label for="">Description </label>
                <input type="text" name="chLastName">
            </div>

            <div>
                <label for="">User: ID </label>
                <input type="number" name="chFirstName" placeholder="">
            </div>

            <input type="submit" name="createUser" id="" value="<?= htmlspecialchars($text['createStation']) ?>">
        </form>
        <?php
            if (!empty($message)) {
                echo htmlspecialchars($message);
            }
        ?>
    </main>
</body>

</html>