<!DOCTYPE html>
<html>

<head>
    <title>Collections</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">
    <?php
    include("../Libraries/conndb.php");
    include("../Libraries/navbar.php");
    createnavbar("Collections");
    ?>
    <h1>Edit Collection</h1>
    <ul>
        <?php
        $stmt = $conn->prepare("SELECT * FROM collections WHERE pk_collection = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { ?>
            <form class="form-card" method="post">
                <div class="form-group">
                    <label>Collection Name</label>
                    <input type="text" value="<?= htmlspecialchars($row['name']); ?>" name="collectionname" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required><?= htmlspecialchars($row['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Station</label>
                    <input type="text" value="<?= htmlspecialchars($row["fk_station_associated"]) ?>" name="station" disabled>
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input name="startdate" type="datetime-local" value="<?= date('Y-m-d\TH:i', strtotime($row['started_at'])); ?>" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input name="enddate" type="datetime-local" value="<?= date('Y-m-d\TH:i', strtotime($row['ended_at'])); ?>" required>
                </div>
                <input type="submit" value="Update Collection">
            </form>
        <?php
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $collectionname = $_POST['collectionname'];
            $description = $_POST["description"];
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $updatecollectionstmt = $conn->prepare("UPDATE collections SET name = ?, description = ?, started_at = ?, ended_at = ? WHERE pk_collection = ?");
            $updatecollectionstmt->bind_param("sssss", $collectionname, $description, $startdate, $enddate, $_GET['id']);
            if ($updatecollectionstmt->execute()) {
                echo "<p>Collection '" . htmlspecialchars($collectionname) . "' updated successfully!</p>";
                echo "<script>window.location.href = 'collections.php';</script>";
            } else {
                echo "<p>Error updating collection: " . htmlspecialchars($conn->error) . "</p>";
            }
        }
        ?>
    </ul>

</body>

</html>