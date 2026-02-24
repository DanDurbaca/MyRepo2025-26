<?php
$servername = "localhost"; 
$username = "root";       
$password = "";             
$dbname = "miniwebshop";        


$conn = mysqli_connect($servername, $username, $password, $dbname);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT itemId , itemName , stock FROM items";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
        print_r($items);
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src= "jquery-3.7.1.min.js"></script>
    <script src="myShop.js?t=<?= time(); ?>"></script>
    <title>Document</title>
</head>
<body>
    
</body>
</html>