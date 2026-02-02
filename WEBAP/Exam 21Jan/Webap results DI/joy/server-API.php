<?php
if(isset($_GET["giveListOfItems"])){
$connection = new mysqli("localhost", "root", "", "MiniWebShop");
$sqlSelect = $connection->prepare("SELECT * FROM items");
$result = $sqlSelect->get_result();
while($row = $result->fetch_assoc()){
            print("<option value='".$row["itemID"]."'>".$row["itemName"]."</option>");
        }
}

?>