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
    <h1>Your Collections</h1>
    <button onclick="window.location.href = 'collections.create.php'">Create Collection</button>
    <button onclick="window.location.href = 'collections-shared.php'">View Shared Collections</button>

    <ul>
    <?php
    $stmt = $conn->prepare("SELECT * FROM collections WHERE fk_user_creates = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<li>";
        echo "<b>" . htmlspecialchars($row['name']) . "</b>";
        echo " <button onclick=\"window.location.href='collections.display.php?id=" . $row['pk_collection'] . "'\">View</button>";
        echo " <button onclick=\"window.location.href='collections.edit.php?id=" . $row['pk_collection'] . "'\">Edit</button>";
        echo " <button onclick=\"if(confirm('Are you sure you want to delete this collection?')) { window.location.href='collections.delete.php?id=" . $row['pk_collection'] . "'; }\">Delete</button>";
        echo "<br><b>Description: " . htmlspecialchars($row['description']) . "</b>";
        echo "<br><br>(" . htmlspecialchars($row['started_at']) . " - " . htmlspecialchars($row['ended_at']) . ")";
        echo "</li>";
    }
    ?>
    </ul>

</body>

</html>