<?php
include_once("comCode.php");

function selectStation() {
    global $conn;
    $sqlSelect = $conn->prepare("select * from station where serialNumber = ?");
    $sqlSelect->bind_param("i", $_GET["id"]);
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    return $row = $result->fetch_assoc();
}

$row = selectStation();

// Initialize message variables
$message = "";

//Change Name
if (isset($_POST['chUserNameCon'])) {
    $name = $_POST["chUserName"];
    if (!empty($name)) {
        $sqlUpdate = $conn->prepare("Update Station set stationName = ? where serialNumber = ?");
        $sqlUpdate->bind_param("si", $name, $row["serialNumber"]);
        if ($sqlUpdate->execute()) {
            $message = "Name updated successfully!";
            $row = selectStation();
            header("Refresh:0");
            exit;
        }
    } else {
        $message = "Please enter a name.";
    }
}

//Change Description
if (isset($_POST['chNameCon'])) {
    $firstName = $_POST["chFirstName"];
    if (!empty($firstName)) {
        $sqlUpdate = $conn->prepare("Update Station set descr = ? where serialNumber = ?");
        $sqlUpdate->bind_param("si", $firstName, $row["serialNumber"]);
        if ($sqlUpdate->execute()) {
            $message = "Description updated successfully!";
            $row = selectStation();
            header("Refresh:0");
            exit;
        }
    } else {
        $message = "Please enter a new description.";
    }
}

//Change Owner
if (isset($_POST['chEmail'])) {
    $email = $_POST["chEmailValue"];
    if (!empty($email)) {
        $sqlUpdate = $conn->prepare("Update Station set userId = ? where serialNumber = ?");
        $sqlUpdate->bind_param("si", $email, $row["serialNumber"]);
        if ($sqlUpdate->execute()) {
            $message = "Owner updated successfully!";
            $row = selectStation();
            header("Refresh:0");
            exit;
        }
    } else {
        $message = "Please enter a user ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css?">
    <title>Home</title>
</head>

<body>

    <nav>
        <?php
        NavigationBar("home");
        ?>
    </nav>

    <header>
    </header>

    <main class="indexMain">
        <h1>User: <?= $row["stationName"] ?></h1>
        <form method="POST">
            <div class="form-row">
                <label for=""><?= htmlspecialchars($text['changeStationName']) ?>:</label>
                <input type="text" name="chUserName" id="" placeholder="<?= $row["stationName"] ?>">
                <input type="submit" name="chUserNameCon" id="" value="<?= htmlspecialchars($text['change']) ?>">
            </div>

            <div class="form-row">
                <label for=""><?= htmlspecialchars($text['changeDescription']) ?>:</label>
                <input type="text" name="chFirstName" placeholder="<?= $row["descr"] ?>">
                <input type="submit" name="chNameCon" id="" value="<?= htmlspecialchars($text['change']) ?>">
            </div>

            <div class="form-row">
                <label for=""><?= htmlspecialchars($text['changeStationOwner']) ?>:</label>
                <input type="number" name="chEmailValue" placeholder="<?= $row["userId"] ?>">
                <input type="submit" name="chEmail" id="" value="<?= htmlspecialchars($text['change']) ?>">
            </div>
        </form>

                                <form method="POST">
                                    <label for="filterID" class="muted">Sort measurements</label>
                                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                                        <select name="filter" id="filterID">
                                            <option value="new">Filter Data by newest</option>
                                            <option value="old">Filter Data by oldest</option>
                                        </select>
                                        <input type="submit" value="Apply Filter">
                                    </div>
                                </form>
        <?php
        if (!empty($message)) {
            echo htmlspecialchars($message);
        }
        ?>
        <table>
                <tr>
                    <th>
                        <h3><?= $row["stationName"] ?>
                    </th>
                </tr>
                <tr>
                    <th><?= $row["descr"] ?></th>
                </tr>
                <tr>
                    <th><?= "Serial number: " . $row["serialNumber"] ?>
                        <form method="POST">
                            <input type="submit" name=<?= $row["serialNumber"] ?> valu
                            e="<?= $text["staAdd"] ?>">
                            <p>Data:</p>

                            <?php
                            if (isset($_POST["filter"])) {
                                // choose order
                                $order = ($_POST["filter"] == "new") ? 'desc' : 'asc';
                                $sqlStation = $conn->prepare("select * from Measurement where station = ? order by timestamp " . $order);
                                $sqlStation->bind_param("i", $row["serialNumber"]);
                                $sqlStation->execute();
                                $resultStation = $sqlStation->get_result();

                                // render results as a table for readability
                                echo '<div style="overflow:auto">';
                                echo '<table class="measurement-table admin-table" style="min-width:700px">';
                                echo '<thead><tr><th>Recorded</th><th>Temperature</th><th>Humidity</th><th>Air Pressure</th><th>Light</th><th>Air Quality</th></tr></thead><tbody>';
                                while ($rowStation = $resultStation->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($rowStation["timestamp"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($rowStation["temperature"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($rowStation["humidity"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($rowStation["airPressure"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($rowStation["lightIntensity"]) . '</td>';
                                    echo '<td>' . htmlspecialchars($rowStation["airQuality"]) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                                echo '</div>';
                            }
                            ?>

    </main>
    <footer>
        <article></article>
    </footer>
</body>

</html>