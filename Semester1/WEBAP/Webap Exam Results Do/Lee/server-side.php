<?php 
$connection = new mysqli("localhost","root","","MiniWebShop");
    if(isset($_POST["display"])) {
    $displayUser = $connection->prepare("select * from items");
    $displayUser->execute();
    $result = $displayUser->get_result();
    while ($row = $result->fetch_assoc()) {
        print("<option value=".$row["itemId"].">".$row["itemName"]."</option>");
    }
    }

    if(isset($_POST["displayItems"])) {
    $displayUser = $connection->prepare("select * from items");
    $displayUser->execute();
    $result = $displayUser->get_result();
        while ($row = $result->fetch_assoc()) {
            print("<tr><td>".$row["itemName"]."</td><td>".$row["stock"]."</td></tr>");
            //print("<p>Item: ". $row["itemName"]." | Quantity: ".$row["stock"]."</p>");
        }
    }

    if(isset($_POST["ItemName"]) && isset($_POST["quantity"])) {
        $orderItem = $connection->prepare("select * from items where itemId = ?");
        $orderItem->bind_param("i", $_POST["ItemName"]);
        $orderItem->execute();
        $result = $orderItem->get_result();
        $row = $result->fetch_assoc();
        if ($row["itemId"] == "") {
            print("This item does not exist");
        }
        else {
            $displayUser = $connection->prepare("select * from items where itemId = ?");
            $displayUser->bind_param("i", $_POST["ItemName"]);
            $displayUser->execute();
            $result = $displayUser->get_result();
            $row = $result->fetch_assoc();

            if ($row["stock"] < $_POST["quantity"]) {
                print("Out of stock");
            }
            else {
            $itemStock = $connection->prepare("update items set stock= (?-?) where itemId = ?");
            $itemStock->bind_param("iii",$row["stock"],$_POST["quantity"],$_POST["ItemName"]);
            $itemStock->execute();

            $order = $connection->prepare("insert into orders (itemId,quantity) values(?,?)");
            $order->bind_param("ii", $_POST["ItemName"],$_POST["quantity"]);
            $order->execute();
            print("Ordered placed successfully");
            }
            }
    }

    if(isset($_POST["displayOrder"])) {
    $displayOrder = $connection->prepare("select * from orders");
    $displayOrder->execute();
    $result = $displayOrder->get_result();
    while ($row = $result->fetch_assoc()) {
        $display = $connection->prepare("select itemName from items where itemId = ?");
        $display->bind_param("i", $row["itemId"]);
        $display->execute();
        $result = $display->get_result();
        while ($name = $result->fetch_assoc()) {
            print("<table><th>Item</th><th>Quantity ordered</th></table><tr><td>".$name["itemName"]."</td><td>".$row["quantity"]."</td></tr>");
        }
        //Yeah I think the way I did it is replacing the ordered data always in the html

        
    }
    }
?>