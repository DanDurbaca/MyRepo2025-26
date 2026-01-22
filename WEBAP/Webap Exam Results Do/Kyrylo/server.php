<?php
$conn = new mysqli("localhost", "root", "", "MiniWebShop");
if ($conn->connect_error) {
    die("Database connection error");
}

$action = $_GET["action"] ?? "";

if ($action == "getItems") {

    $result = $conn->query("SELECT itemId, itemName FROM Items");

    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row["itemId"] . "'>" . $row["itemName"] . "</option>";
    }
    exit;
}

if ($action == "showItems") {

    $result = $conn->query("SELECT itemName, stock FROM Items");
    echo "<table> <th>Item</th> <th>Quantity</th>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>" . "<td>". $row["itemName"] ."</td>". "<td>" . $row["stock"]. "</td>" ."</tr>";
    }
    echo "</table>";
    exit;
}

/* if ($action == "Order") {
        $sqlCreateUser = $connection->prepare("INSERT INTO Orders (itemId) VALUES(?) (quantity) VALUES(?)");
        $sqlCreateUser->bind_param("si",  $_GET["getItems"], $_GET["getItems"]);
        $sqlCreateUser->execute();
} */
?>