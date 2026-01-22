<?php
$conn = new mysqli("localhost", "root", "", "MiniWebShop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD" == "GET"]){
    if (isset($_GET["action"])) {
        if ($_GET['action'] == 'getItems') {
            $sql = 'SELECT itemId, itemName, stock FROM Items';
             $result = $conn->query($sql);
             $items = [];
             while ($row = $result->fetch_assoc()) {
                $items[] = $row;
             }
             echo json_encode($items);
        }
        elseif ($_GET['action'] == 'getStock') {
            $itemId = intval($_GET['itemId']);
            $sql = 'SELECT stock FROM Items WHERE itemId = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $itemId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo json_encode(['stock' => $row['stock']]);
            }
            else {
                echo json_encode(['stock' => 0]);
            }
        }
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'placeOrder') {
        $itemId = intval($_POST['itemId']);
        $qty = intval($_POST['qty']);

        if ($qty < 0) {
            echo json_encode(['message' => 'Please enter a valid order']);
            exit;
        }

        $sql = 'SELECT stock FROM Items WHERE itemId = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['message'=> 'We do not sell this!']);
            exit;
        }

        $row = $result->fetch_assoc();
        $available = $row['stock'];

        if ($qty > $available) {
            echo json_encode(['message'=> 'That is more than we sell!']);
        }
        elseif ($qty == $available) {
            $newAvail = 0;
            $sql = 'UPDATE Items SET stock = ? WHERE itemId = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $newAvail, $itemId);
            $stmt->execute();
            echo json_encode(['message'=> 'Order Placed']);
        }
    }
}



$conn->close();
?>