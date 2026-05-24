<!DOCTYPE html>
<html>

<head>
    <title>Register Station</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">
    <h1>Register a Station</h1>

    <form class="form-card" method="post">
        <div class="form-group">
        <label>Serial Number</label>
            <input type="text" name="serial_number" required minlength="7">
        <label>Name</label>
            <input type="text" name="name" required>
        <label>Description</label>
            <input type="text" name="description">
        <button type="submit">Register</button>
        </div>
    </form>
    <?php
    include("../Libraries/conndb.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $insertststmt = $conn->prepare("INSERT INTO stations (pk_serialNumber, name, description, fk_user_owns) VALUES (?, ?, ?, ?)");
        $insertststmt->bind_param("ssss", $_POST["serial_number"], $_POST["name"], $_POST["description"], $_SESSION["username"]);
        $insertststmt->execute();
        $insertststmt->close();
        echo "<script>window.location.href = 'stations.php';</script>";
    }
    ?>
    <p>(Could) Register via QR Code</p>

</body>

</html>