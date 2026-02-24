<?php 
// we need to create a list of countries only when we are asked for it
$conn = new mysqli("localhost","root","","MiniWebShop");

if (isset($_GET["giveListOFItems"])) {
    //build a list of countries and print them out:
    $sqlSelect = $conn->prepare("SELECT * from Items");
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    while ($row = $result->fetch_assoc()) {
        print("<option value='".$row['itemId']."'>".$row['itemName']."</option>");
    }
}

if (isset($_GET["giveItems"])) {
    //build a list of countries and print them out:
    $sqlSelect = $conn->prepare("SELECT * from Items");
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    while ($row = $result->fetch_assoc()) {
        print("<tr> <td>".$row['itemName']."</td> <td>".$row['stock']."</td></tr>");
    }
}

// if ($_GET["checkItem"]) {
//     $sqlSelect = $conn->prepare("SELECT itemId from Items where itemName=?");
//     $sqlSelect->bind_param("i",$_GET["checkItem"]);
//     $sqlSelect->execute();
//     $result = $sqlSelect->get_result();
//     while ($row = $result->fetch_assoc()) {
//     print(1);
//     }
// }

if ($_GET["checkStock"]) {
    $sqlSelect = $conn->prepare("SELECT * from items where itemId=?");
    $sqlSelect->bind_param("i",$_GET["checkItem"]);
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    while ($row = $result->fetch_assoc()) {
    print($row['stock']);
    }
}

if ($_GET["writeOrder"]) {
    $sqlSelect = $conn->prepare("INSERT INTO `orders` (`itemId`, `quantity`) VALUES ('?', '?');");
    $sqlSelect->bind_param("i",$_GET["checkItem"]);
    $sqlSelect->execute();
    print("Ordered placed successfully”.");

}
?>