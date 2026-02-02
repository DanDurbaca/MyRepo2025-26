<?php
$connection = new mysqli("localhost","root","","MiniWebShop");

if(isset($_GET["giveListOfItems"])){
    $sql = $connection -> prepare("SELECT * FROM Items");
    $sql -> execute();
    $result = $sql -> get_result();
    while($rows = $result -> fetch_assoc()){
        print("<option value='".$rows["itemId"]."'>".$rows["itemName"]."</option>");
    }
}



?>