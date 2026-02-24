<?php
$connection = new mysqli("localhost","root","","miniwebshop");

if(isset($_GET["getItemList"])){
    $sql = $connection -> prepare("SELECT * FROM items");
    $sql -> execute();
    $result = $sql -> get_result();
    while($rows = $result -> fetch_assoc()){
        print("<option value='".$rows["itemId"]."'>".$rows["itemName"]."</option>");
    }
}
if(isset($_GET["getItemQuantity"])){
    $sql = $connection -> prepare("SELECT * FROM items");
    $sql -> execute();
    $result = $sql -> get_result();
    print("<table>
            <tr>
            <th>Item</th>
            <th>Quantity</th>
            </tr>");
    while($rows = $result -> fetch_assoc()){
        print("<tr><td>".$rows["itemName"]."</td><td>".$rows["stock"]."</td></tr>");
    }
    print("</table>");
}

if(isset($_GET["selectItem"])){
    $sql = $connection -> prepare("SELECT * FROM items WHERE itemId = ?");
    $sql -> bind_param("i",$_GET["selectItem"]);
    $sql -> execute();
    $result = $sql -> get_result();
    if(mysqli_num_rows($result)>0){
        if(isset($_GET["createOrderQuan"])){
            $orderedNum = $_GET["createOrderQuan"];
            $rows = $result -> fetch_assoc();
            if($orderedNum >= $rows["stock"]){
                print("Out of stock");
            }
            else{
                $selectedItem = $_GET["selectItem"];
                print("Order placed succesfully");
                $sql = $connection -> prepare("INSERT INTO orders(itemId,quantity) VALUES(?,?)");
                $sql -> bind_param("ii",$selectedItem,$orderedNum);
                $sql -> execute();

                $stockRemaining = $rows["stock"] - $orderedNum;
                $sql = $connection -> prepare("UPDATE items SET stock = ? WHERE itemId = ?");
                $sql -> bind_param("ii",$stockRemaining,$selectedItem);
                $sql -> execute();
                exit();
            }
        }
    }
    else{
        print("This item does not exist");
    }
}

if(isset($_GET["getOrders"])){
    $sql = $connection -> prepare("SELECT * FROM orders JOIN items ON orders.itemId = items.itemId");
    $sql -> execute();
    $result = $sql -> get_result();
    print("<table>
            <tr>
            <th>Item</th>
            <th>Quantity</th>
            </tr>");
    while($rows = $result -> fetch_assoc()){
        print("<tr><td>".$rows["itemName"]."</td><td>".$rows["quantity"]."</td></tr>");
    }
    print("</table>");

}
?>