<?php

    $connection = new mysqli("localhost", "root", "", "MiniWebShop");

if(isset($_GET["giveListOfItems"])){
    $sql = $connection -> prepare("SELECT * FROM Items");
    $sql -> execute();
    $result = $sql -> get_result();
    while($rows = $result -> fetch_assoc()){
        print("<option value='".$rows["itemId"]."'>".$rows["itemName"]."</option>");
    }
}

if(isset($_GET["giveListOfEverything"])){
    $sql = $connection -> prepare("SELECT * FROM Items");
    $sql -> execute();
    $result = $sql -> get_result();
    while($rows = $result -> fetch_assoc()){
        print("<ts>".$rows["itemName"]." | \t Quantity: ".$rows["stock"]."</ts><br>");
    }
}

if(isset($_GET["giveNumberOfItems"])){
    $sql = $connection -> prepare("SELECT * FROM Items WHERE itemId = ?");
    $sql -> bind_param("i",$_GET["giveNumberOfItems"]);
    $sql -> execute();
    $result = $sql -> get_result();
    if(mysqli_num_rows($result)>0){
        while($rows = $result -> fetch_assoc()){
        if($rows["stock"]>0){
            print($rows["stock"]);
        }
        else{
            print("Out of Stock");
        }
    }
    }
}

if(isset($_GET["createOrder"])){
    $orderQual = $_GET["createOrder"];
    if (isset($_GET["orderedItemAvailibility"])) {
    $sql = $connection -> prepare("SELECT * FROM Items WHERE itemId = ?");
    $sql -> bind_param("i",$_GET["orderedItemAvailibility"]);
    $sql -> execute();
    $result = $sql -> get_result();
    $rows = $result -> fetch_assoc();
    if($orderQual<0){
            print("Enter a valid order");
        }
        elseif($orderQual>$rows["stock"]){
            print("error, too many items");
        }
        elseif($orderQual==$rows["stock"]){
            print("success, out of stock.");
            $id = $_GET["orderedItemAvailibility"];
            $stock = 0;
            exit();
        }
        elseif($orderQual<$rows["stock"]){
            print("Order placed successfully");
            $id = $_GET["orderedItemAvailibility"];
            exit();
        }
    }
}
?>