<?php
$connection = new mysqli("localhost", "root", "", "MiniWebShop");

if (isset($_GET["itemId"])) {

    $fruitId = (int) $_GET["itemId"];

    $stmt = $connection->prepare("SELECT stock FROM Items WHERE itemId = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo $row["stock"];
    } else {
        echo "-1";
    }
    exit;
}


$stmt = $connection->prepare(
    "SELECT itemId, itemName, stock FROM items"
);

$stmt->execute();
$result = $stmt->get_result();

// Output <option> elements
while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row["itemId"]}'>{$row["itemName"]}</option>";
}

if (isset($_POST["itemId"], $_POST["stock"])) {
    
    $itemId = (int) $_POST["itemId"];
    $stock = (int) $_POST["stock"];

    if ($stock < 0) {
        echo "Please enter a valid order";
        exit;
    }

    $stmt = $connection->prepare("SELECT stock  FROM Items WHERE itemId = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo "This item does not exist";
        exit;
    }

    $row = $res->fetch_assoc();
    $available = (int) $row["stock"];

    if ($stock > $available) {
        echo "Out of stock";
        exit;
    }

    if ($stock == $available) {
        $newAvail = 0;
        $upd = $connection->prepare("UPDATE Items SET stock = ? WHERE itemId = ?");
        $upd->bind_param("ii", $newAvail, $fruitId);
        $upd->execute();

        echo "Ordered placed successfully";
        exit;
    }
    $newAvail = $available - $stock;
    $upd = $connection->prepare("UPDATE Items SET stock = ? WHERE itemId = ?");
    $upd->bind_param("ii", $newAvail, $fruitId);
    $upd->execute();

    echo "Order placed successfully";
    exit;


}