<!DOCTYPE html>
<html lang="en">

<head>
    <title>Measurements</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body>
    <?php
    include("../Libraries/conndb.php");
    include("../Libraries/navbar.php");
    createnavbar("Collections");
    ?>
    <form class="form-card" method="POST">
        <div class="form-group">
            <label>Collection Name</label>
            <input type="text" placeholder="Collection name" name="collectionname" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Description" required></textarea>
        </div>
        <div class="form-group">
            <label>Station</label>
            <?php
            $selectstationsstmt = $conn->prepare("SELECT * FROM stations WHERE fk_user_owns = ?");
            $selectstationsstmt->bind_param("s", $_SESSION['username']);
            $selectstationsstmt->execute();
            $result = $selectstationsstmt->get_result();
            ?>
            <select name="stationslist" id="stationslist" required>
                <?php
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['pk_serialNumber'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Start Date</label>
            <input name="startdate" type="datetime-local" required>
        </div>
        <div class="form-group">
            <label>End Date</label>
            <input name="enddate" type="datetime-local" required>
        </div>
        <input type="submit" value="Create Collection">
    </form>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $station = $_POST['stationslist'];
        $collectionname = $_POST['collectionname'];
        $description = $_POST["description"];
        $station = $_POST['stationslist'];
        $startdate = $_POST['startdate'];
        $enddate = $_POST['enddate'];

        $insertcollectionstmt = $conn->prepare("INSERT INTO collections (name, description, started_at, ended_at, fk_user_creates, fk_station_associated) VALUES (?, ?, ?, ?, ?, ?)");
        $insertcollectionstmt->bind_param("ssssss", $collectionname, $description, $startdate, $enddate, $_SESSION['username'], $station);
        if ($insertcollectionstmt->execute()) {
            echo "<p>Collection '" . htmlspecialchars($collectionname) . "' created successfully!</p>";
            echo "<script>window.location.href = 'collections.php';</script>";
        } else {
            echo "<p>Error creating collection: " . htmlspecialchars($conn->error) . "</p>";
        }
    }
    ?>
</body>

</html>