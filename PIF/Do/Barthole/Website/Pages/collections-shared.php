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
    <h1>Your shared collections</h1>

    <ul>
        <?php
        $stmt = $conn->prepare("SELECT c.* FROM collections c JOIN hasaccess h ON c.pk_collection = h.pkfk_collection WHERE h.pkfk_user = ?");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {?>
            <li>
                <b><?php echo htmlspecialchars($row['name']); ?></b>
                <button onclick="window.location.href='collections.display.php?id=<?php echo $row['pk_collection']; ?>'">View</button>
                <br><b>Description: <?php echo htmlspecialchars($row['description']); ?></b>
                <br><br>(<?php echo htmlspecialchars($row['started_at']); ?> - <?php echo htmlspecialchars($row['ended_at']); ?>)
            </li>    
    <?php } ?>
    </ul>

</body>

</html>