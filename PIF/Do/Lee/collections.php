<?php
// Ensure session, translations and DB are available before any output
include_once("comCode.php");

// Require a logged-in user (either normal user or admin) to access collections
if (!(isset($_SESSION["UserLoggedIn"]) || isset($_SESSION["adminLoggedIn"]))) {
    header("Location: register.php");
    exit;
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
        <?php NavigationBar("home"); ?>
    </nav>

    <header>
        
    </header>

    <main class="indexMain">

    <h2><?= htmlspecialchars($text['yourCollections']) ?> </h2> 
    <?php
        $sqlColl = $conn->prepare("select collectionName,collectionId from Collection where userId = ?");
        $sqlColl->bind_param("i", $_SESSION["userID"]);
        $sqlColl->execute();
        $resultColl = $sqlColl->get_result();
            while ($collection = $resultColl->fetch_assoc()) {
                ?>
                <div>
                <?php
                print("<h3>" . $collection["collectionName"] . "<br>" . "</h3>");
                ?>
                </div>
                <?php
                $sqlShare = $conn->prepare("select measurement from CollectionMeasurements where collectionId = ?");
                $sqlShare->bind_param("i", $collection["collectionId"]);
                $sqlShare->execute();
                $resultShare = $sqlShare->get_result();

                    // Render measurements for this collection as a table
                    echo '<div style="overflow:auto">';
                    echo '<table class="measurement-table admin-table" style="min-width:700px">';
                    echo '<thead><tr>';
                    echo '<th>' . htmlspecialchars($text['timeRecorded']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['measureData']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['temperature']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['humidity']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['airPressure']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['lightIntensity']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['airQuality']) . '</th>';
                    echo '</tr></thead><tbody>';

                    while ($rowShare = $resultShare->fetch_assoc()) {
                        $sqlMeasure = $conn->prepare("select * from Measurement where measurementId = ? order by timestamp desc");
                        $sqlMeasure->bind_param("i", $rowShare["measurement"]);
                        $sqlMeasure->execute();
                        $resultMes = $sqlMeasure->get_result();
                            while ($rowMes = $resultMes->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($rowMes["timestamp"]) . '</td>';
                                // include measureData cell to match header columns
                                echo '<td>' . htmlspecialchars($rowMes["measureData"] ?? '') . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["temperature"]) . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["humidity"]) . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["airPressure"]) . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["lightIntensity"]) . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["airQuality"]) . '</td>';
                                echo '</tr>';
                            }
                    }
                    echo '</tbody></table>';
                    echo '</div>';  
                }            
    ?>

    <h2>Show shared collections:</h2>
    <?php
    //Show shared collections
        $sqlColl = $conn->prepare("select collectionId from UserCollections where user = ?");
        $sqlColl->bind_param("i", $_SESSION["userID"]);
        $sqlColl->execute();
        $resultColl = $sqlColl->get_result();
            while ($rowStation = $resultColl->fetch_assoc()) {
                $sqlColName = $conn->prepare("select collectionName from Collection where collectionId = ?");
                $sqlColName->bind_param("i", $rowStation["collectionId"]);
                $sqlColName->execute();
                $resultColName = $sqlColName->get_result();
                $rowStationName = $resultColName->fetch_assoc();
                ?>
                <div>
                <?php
                print("<h3>" . $rowStationName["collectionName"] . "<br>" . "</h3>");
                ?>
                </div>
                <?php
            
                $sqlShare = $conn->prepare("select measurement from CollectionMeasurements where collectionId = ?");
                $sqlShare->bind_param("i", $rowStation["collectionId"]);
                $sqlShare->execute();
                $resultShare = $sqlShare->get_result();

                    // Render measurements for this shared collection as a table (same classes as own collections)
                    echo '<div style="overflow:auto">';
                    echo '<table class="measurement-table admin-table" style="min-width:700px">';
                    echo '<thead><tr>';
                    echo '<th>' . htmlspecialchars($text['timeRecorded']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['measureData']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['temperature']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['humidity']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['airPressure']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['lightIntensity']) . '</th>';
                    echo '<th>' . htmlspecialchars($text['airQuality']) . '</th>';
                    echo '</tr></thead><tbody>';

                    while ($rowShare = $resultShare->fetch_assoc()) {
                        $sqlMeasure = $conn->prepare("select * from Measurement where measurementId = ? order by timestamp desc");
                        $sqlMeasure->bind_param("i", $rowShare["measurement"]);
                        $sqlMeasure->execute();
                        $resultMes = $sqlMeasure->get_result();
                            while ($rowMes = $resultMes->fetch_assoc()) {    
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($rowMes["timestamp"]) . '</td>';
                                // keep a measureData cell (may be empty) to match the header columns
                                echo '<td>' . htmlspecialchars($rowMes["measureData"] ?? '') . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["temperature"]) . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["humidity"]) . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["airPressure"]) . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["lightIntensity"]) . '</td>';
                                echo '<td>' . htmlspecialchars($rowMes["airQuality"]) . '</td>';
                                echo '</tr>';
                            }
                    }  
                    echo '</tbody></table>';
                    echo '</div>';
                }            
    ?>    
    
    <h2>Make a collection: </h2>
    <form method="POST">
        <label for="">Enter Collection name</label>
        <input type="text" name="newCollection">
        <label for="">Enter description</label>
        <input type="text" name="descr">
        <input type="submit" value="Add" name="addCollection">
    </form>

    <?php 
    if (isset($_POST["addCollection"])) {
            $sqlInsert = $conn->prepare("insert into Collection (collectionName,userId,descr) values(?,?,?)");
            $sqlInsert->bind_param("sis", $_POST["newCollection"], $_SESSION["userID"], $_POST["descr"]);
            $sqlInsert->execute();
            echo "Collection created";
            header("Refresh:0");
                                }
    ?>

    <h2>Add measurements to collection</h2>
    <form method="POST">
        <input type="text" placeholder="Choose station" name="stationNameInput">
        <input type="text" placeholder="Choose collection" name="collectionName">
        <label for="">Start from: </label>
        <input type="date" name="startDate">
        <label for="">End on: </label>
        <input type="date" name="endDate">
        <input type="submit" name="addDataColl">
    </form>

    <h2>Change collection name</h2>
    <form method="POST">
        <input type="text" placeholder="Choose Collection" name="collectionChangeInput">
        <input type="text" placeholder="Choose new Name" name="collectionName">
        <input type="submit" name="changeColl">        
    </form>

    <h2>Delete collection</h2>
    <form method="POST">
        <input type="text" placeholder="Choose Collection to delete" name="delChangeInput">
        <input type="submit" name="delColl">        
    </form>

    <h2>Add friend to collection</h2>
    <form method="POST">
        <input type="text" placeholder="Choose Friend to add" name="friendCol">
        <input type="text" placeholder="Collection Name" name="colFriend">
        <input type="submit" name="addFriendCol">        
    </form>

    <h2>Unshare collection from Friend</h2>
    <?php
    //Remove friend from collections
        $sqlSelect = $conn->prepare("Select * from userCollections");
        $sqlSelect->execute();
        $resultName = $sqlSelect->get_result();
        while($sqlName = $resultName->fetch_assoc() ) {
            $sqlCollUser = $conn->prepare("Select userId,collectionName from collection where collectionId = ?");
            $sqlCollUser->bind_param("i", $sqlName["collectionId"]);
            $sqlCollUser->execute();
            $resultNameCol = $sqlCollUser->get_result();
            $sqlNameCol = $resultNameCol->fetch_assoc(); 
                    if ($sqlNameCol["userId"] == $_SESSION["userID"]) {
                        $sqlFriend = $conn->prepare("Select userName from user where userId = ?");
                        $sqlFriend->bind_param("i", $sqlName["user"]);
                        $sqlFriend->execute();
                        $resultFriend = $sqlFriend->get_result();          
                        $sqlNameFriend = $resultFriend->fetch_assoc();                        
                        ?>
                            <h4>Currently sharing the collection: (<?= $sqlNameCol["collectionName"] ?>) with <?= $sqlNameFriend["userName"]?></h4>
                            <form method="POST">
                                <input type="submit" value="<?= htmlspecialchars($text['unshare']) ?>" name="<?= $sqlNameFriend["userName"]?>">
                            </form>
                        <?php
                            if (isset($_POST[$sqlNameFriend["userName"]])) {
                            $sqlRemove = $conn->prepare("delete from userCollections where user = ? and collectionId = ?");
                            $sqlRemove->bind_param("ii", $sqlName["user"], $sqlName["collectionId"]);
                            $sqlRemove->execute();
                            print("Removed");
                        }
                    }
                }
    ?>

    <?php
    //Add data to collection
    if (isset($_POST["addDataColl"])) {
        $sqlSelect = $conn->prepare("Select serialNumber from Station where stationName = ?");
        $sqlSelect->bind_param("s", $_POST["stationNameInput"]);
        $sqlSelect->execute();
        $resultName = $sqlSelect->get_result();
        $sqlName = $resultName->fetch_assoc();

        $sqlSelectColl = $conn->prepare("Select collectionId from Collection where collectionName = ?");
        $sqlSelectColl->bind_param("s", $_POST["collectionName"]);
        $sqlSelectColl->execute();
        $resultCollId = $sqlSelectColl->get_result();
        $sqlCollId = $resultCollId->fetch_assoc();

        $sqlSelectMeas = $conn->prepare("Select measurementId from Measurement where timestamp between ? and ? and station in (?)");
        $sqlSelectMeas->bind_param("ssi", $_POST["startDate"],$_POST["endDate"], $sqlName["serialNumber"]);
        $sqlSelectMeas->execute();
        $resultMeas = $sqlSelectMeas->get_result();
        
        $sqlInsert = $conn->prepare("insert CollectionMeasurements (collectionId,measurement) values(?,?)");
        while($sqlMeas = $resultMeas->fetch_assoc()) {
            $sqlInsert->bind_param("ii", $sqlCollId["collectionId"], $sqlMeas["measurementId"]);
            $sqlInsert->execute();
        }  
    }

    //Change collection name
    if (isset($_POST["changeColl"])) {
        $sqlUpdate = $conn->prepare("update Collection set collectionName = ? where collectionName = ?");
        $sqlUpdate->bind_param("ss", $_POST["collectionName"],$_POST["collectionChangeInput"]);
        $sqlUpdate->execute();
    }

    //Delete collection
    if (isset($_POST["delColl"])) {
        $sqlSelectColl = $conn->prepare("Select collectionId from Collection where collectionName = ?");
        $sqlSelectColl->bind_param("s", $_POST["delChangeInput"]);
        $sqlSelectColl->execute();
        $resultCollId = $sqlSelectColl->get_result();
        $sqlCollId = $resultCollId->fetch_assoc();

        $sqlRemove = $conn->prepare("delete from CollectionMeasurements where collectionId = ?");
        $sqlRemove->bind_param("i", $sqlCollId["collectionId"]);
        $sqlRemove->execute();
        
        $sqlDelete = $conn->prepare("delete from Collection where collectionName=?");
        $sqlDelete->bind_param("s",$_POST["delChangeInput"]);
        $sqlDelete->execute();
        echo "Deleted rows: " . $sqlDelete->affected_rows;
    }

    //add Friend to collection
    if (isset($_POST["addFriendCol"])) {        
        $sqlSelect = $conn->prepare("select userId from User where userName = ?");
        $sqlSelect ->bind_param("s", $_POST["friendCol"]);
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();
        $row = $result->fetch_assoc();

        $sqlColId = $conn->prepare("select collectionId from collection where userId = ? and collectionName = ?");
        $sqlColId->bind_param("ss", $_SESSION["userID"], $_POST["colFriend"]);
        $sqlColId->execute();
        $resultColId = $sqlColId->get_result();
        $rowCol = $resultColId->fetch_assoc();

        $insertFriendCol = $conn->prepare("insert into UserCollections (user,collectionId) values(?,?)");
        $insertFriendCol->bind_param("ii", $row["userId"], $rowCol["collectionId"]);
        $insertFriendCol->execute();
        echo "Collection created";
        header("Refresh:0");
    }
    ?>
    </main> 
    <footer>
        <article></article>
    </footer>
</body>
</html>