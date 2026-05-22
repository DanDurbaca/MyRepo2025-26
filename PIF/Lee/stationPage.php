<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store</title>
    <link rel="stylesheet" href="./style.css?<?= time(); ?>">
</head>

<body>
    <nav>
        <?php
        include("comCode.php");
        NavigationBar("game");
        ?>
    </nav>

    <main class="indexMain">
        <h2><?= htmlspecialchars($text['yourStations']) ?></h2>
        <article class="art">
            <h3><?= htmlspecialchars($text['filterDate']) ?></h3>
            <form method="GET">
                <div class="form-row">
                    <label for="filterID" class="muted"><?= htmlspecialchars($text['filterDate']) ?></label>
                    <select name="filter" id="filterID">
                        <option value="new">Filter Data by newest</option>
                        <option value="old">Filter Data by oldest</option>
                    </select>
                    <div class="form-actions">
                        <input type="submit" value="Apply Filter">
                    </div>
                </div>
            </form>
            
            <?php
            $stationCon = $conn->prepare("select * from Station where userId = ?");
            $stationCon->bind_param("i", $_SESSION["userID"]);
            $stationCon->execute();
            $result = $stationCon->get_result();

            if ($result->num_rows === 0) {
                echo '<p>' . htmlspecialchars($text['yourStations']) . ': ' . htmlspecialchars($text['dataLabel']) . ' None</p>';
            } else {
                while ($station = $result->fetch_assoc()) {
                    ?>
                    <table class="admin-table station-table">
                        <tr>
                            <thead>
                                <th colspan="2">
                                    <h3><?= htmlspecialchars($station["stationName"]) ?></h3>
                                </th>
                            </thead>
                        </tr>
                        <tr>
                            <td colspan="2"><?= htmlspecialchars($station["descr"]) ?></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="muted">Serial number: <?= htmlspecialchars($station["serialNumber"]) ?></div>
                            </td>
                            <td style="text-align:right">
                                <form method="POST">
                                    <div class="form-row">
                                        <div class="form-actions">
                                            <input type="submit" name="edit_<?= htmlspecialchars($station["serialNumber"]) ?>" value="<?= htmlspecialchars($text['staAdd']) ?>">
                                        </div>
                                        <p class="muted"><?= htmlspecialchars($text['dataLabel']) ?></p>
                                    </div>
                                    <?php
                                    // choose order and render measurements as a table for readability
                                    $order = (isset($_GET["filter"]) && $_GET["filter"] == "new") ? 'desc' : 'asc';
                                    $sqlStation = $conn->prepare("select * from Measurement where station= ? order by timestamp " . $order);
                                    $sqlStation->bind_param("i", $station["serialNumber"]);
                                    $sqlStation->execute();
                                    $resultStation = $sqlStation->get_result();

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
                                    ?>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?php
                                if (isset($_POST["edit_" . $station["serialNumber"]])) {
                                    ?>
                                    <form method="POST">
                                        <div class="form-row">
                                            <input type="text" name="newName" placeholder="<?= $station["stationName"] ?>">
                                            <input type="text" name="newDesc" placeholder="<?= $station["descr"] ?>">
                                            <div class="form-actions">
                                                <input type="submit" name="save_<?= htmlspecialchars($station["serialNumber"]) ?>" value="<?= htmlspecialchars($text['changeStation']) ?>">
                                            </div>
                                        </div>
                                    </form>
                                    <?php
                                }
                                if (isset($_POST["save_" . $station["serialNumber"]]) && isset($_POST["newName"]) && isset($_POST["newDesc"])) {
                                    $sqlUpdate = $conn->prepare("update Station set stationName = ?, descr = ?  where serialNumber = ?");
                                    $sqlUpdate->bind_param("ssi", $_POST["newName"], $_POST["newDesc"], $station["serialNumber"]);
                                    $sqlUpdate->execute();
                                    echo "Station data edited";
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
            }
            ?>
        </article>
    <h2><?= htmlspecialchars($text['addStation']) ?></h2>
        <form method="POST" class="form-row">
            <input type="number" name="newName" placeholder="Serial Number">
            <div class="form-actions">
                <input type="submit" name="linkStation" value="<?= htmlspecialchars($text['addStation']) ?>">
            </div>
        </form>
        <?php 
        if (isset($_POST["linkStation"])) {
            $sqlUpdate = $conn->prepare("update Station set userId = ? where serialNumber = ?");
            $sqlUpdate->bind_param("ii", $_SESSION["userID"], $_POST["newName"]);
            $sqlUpdate->execute(); 

            //JQUERY to be inserted for refreshing stations on
        }
        ?>
    </main>
    <footer>
        <article></article>
    </footer>
</body>

</html>