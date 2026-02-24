<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
$host = "localhost";
$Uname = "root";
$pswd="";
$dbName="MiniWebShop";

$conn = mysqli_connect($host, $UName, $pswd, $dbName);

$sqlSelect = $conn ->prepare ("Select * from Items");
$sqlSelect -> execute();
$result = $sqlSelect -> get_result();
?>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    if (isset($_GET['action'])) {
        
        if ($_GET['action'] == 'getItems') {
           
            $sql = "SELECT itemId, itemName FROM Items";
            $result = $conn->query($sql);
          
            $items = [];
        
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            echo json_encode($fruits);
   
        } elseif ($_GET['action'] == 'getAvailability') {
          
            $itemId = intval($_GET['itemId']);
           
            $sql = "SELECT availability FROM Items WHERE itemId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $itemId);  
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo json_encode(['stock' => $row['stock']]);
            } else {
                echo json_encode(['stock' => 0]);
            }
        }
    } }elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['action']) && $_POST['action'] == 'placeOrder') {
        
        $itemId = intval($_POST['itemId']);
        $qty = intval($_POST['qty']);

       
        if ($qty < 0) {
            echo json_encode(['message' => 'Please enter a valid order']);
            exit;  
        }

       
        $sql = "SELECT quantity FROM Items WHERE itemId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

     
        if ($result->num_rows == 0) {
            echo json_encode(['message' => 'Unknown given fruit']);
            exit;
        }

        
        $row = $result->fetch_assoc();
        $available = $row['quantity'];

       
        if ($qty > $available) {
            
            echo json_encode(['message' => 'We are unable to honor that request']);
        } elseif ($qty == $available) {
          
            $newAvail = 0;
            $sql = "UPDATE Items SET quantity = ? WHERE itemId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $newAvail, $itemId);
            $stmt->execute();
            echo json_encode(['message' => 'You have emptied our stock']);
        } else {
            
            $newAvail = $available - $qty;
            $sql = "UPDATE Items SET availability = ? WHERE itemId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $newAvail, $fruitId);
            $stmt->execute();
            echo json_encode(['message' => 'Order placed successfully']);
        }
    }
}
?>
</body>
</html>