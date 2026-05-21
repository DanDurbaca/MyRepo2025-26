<!DOCTYPE html>
<html>

<head>
    <title>Register Station</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">
    <h1>Edit a Station</h1>
    <?php
    include("../Libraries/conndb.php");
    $stmt = $conn->prepare("SELECT * FROM stations WHERE pk_serialNumber = ? AND fk_user_owns = ?");
    $stmt->bind_param("ss", $_GET["serial"], $_SESSION["username"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $station = $result->fetch_assoc();
    ?>
    <form class="form-card" method="post">
        <div class="form-group">
        <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($station['name']) ?>" required>
        <br>
        <label>Description</label>
            <input type="text" name="description" value="<?= htmlspecialchars($station['description']) ?>"><br>
        <button type="submit">Save changes</button>
        </div>
    </form>
    <?php
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $insertststmt = $conn->prepare("UPDATE stations SET name = ?, description = ? WHERE pk_serialNumber = ? AND fk_user_owns = ?");
        $insertststmt->bind_param("ssss", $_POST["name"], $_POST["description"], $_GET["serial"], $_SESSION["username"]);
        $insertststmt->execute();
        $insertststmt->close();
        echo "<script>window.location.href='stations.php';</script>";
    }
    ?>

</body>

</html>