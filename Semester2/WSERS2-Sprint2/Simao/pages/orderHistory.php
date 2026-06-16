<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?<?= time() ?>">
    <title>Order History</title>
</head>

<body>
    <nav>
        <?php
        include_once("commonCode.php");
        navBar("History");

        if (!isset($_SESSION["UserLogged"]) || $_SESSION["UserLogged"] == false) {
            print("<h1>Please login to see your order history.</h1>");
            die();
        }

        $currentUser = $_SESSION["Username"];
        $connection = new mysqli("localhost", "root", "", "HTSTA_DB");
        ?>
    </nav>

    <main>
        <h1><?= ($language == "EN") ? "Your Order History" : "Histórico de Encomendas" ?></h1>

        <?php
        // Fetch all orders matching this username
        $sqlUserOrders = $connection->prepare("SELECT * FROM Orders WHERE username = ? ORDER BY orderID DESC");
        $sqlUserOrders->bind_param("s", $currentUser);
        $sqlUserOrders->execute();
        $ordersResult = $sqlUserOrders->get_result();

        if ($ordersResult->num_rows > 0) {
            while ($orderRow = $ordersResult->fetch_assoc()) {
                $currentOrderID = $orderRow["orderID"];
                $orderStatus = $orderRow["status"];

                print("<div style='border: 1px solid #ccc; margin-bottom: 20px; padding: 15px; background-color: #fcfcfc;'>");
                print("<h3>" . (($language == "EN") ? "Order" : "Encomenda") . " #$currentOrderID</h3>");
                print("<p><strong>Status:</strong> <span style='font-weight: bold; color: " . ($orderStatus == "pending" ? "orange" : "green") . ";'>$orderStatus</span></p>");

                // Fetch the items inside this order
                $sqlItems = $connection->prepare("
                    SELECT BoughtItem.quantity, Products.productEN, Products.productPT, Products.price 
                    FROM BoughtItem 
                    JOIN Products ON BoughtItem.productID = Products.productID 
                    WHERE BoughtItem.orderID = ?
                ");
                $sqlItems->bind_param("i", $currentOrderID);
                $sqlItems->execute();
                $itemsResult = $sqlItems->get_result();

                print("<table border='1' style='width:100%; border-collapse: collapse; margin-top: 10px;'>");
                print("<tr>
                        <th>" . $arrayOfTranslations["Item"] . "</th>
                        <th>" . $arrayOfTranslations["Price"] . "</th>
                        <th>" . $arrayOfTranslations["Quantity"] . "</th>
                        <th>" . $arrayOfTranslations["SubTotal"] . "</th>
                       </tr>");

                $orderTotal = 0;
                while ($itemRow = $itemsResult->fetch_assoc()) {
                    $productName = ($language == "EN") ? $itemRow["productEN"] : $itemRow["productPT"];
                    $subtotal = $itemRow["price"] * $itemRow["quantity"];
                    $orderTotal += $subtotal;

                    print("<tr>");
                    print("<td>" . htmlspecialchars($productName) . "</td>");
                    print("<td>" . $itemRow["price"] . "€</td>");
                    print("<td>" . $itemRow["quantity"] . "</td>");
                    print("<td>" . $subtotal . "€</td>");
                    print("</tr>");
                }
                print("<tr><td colspan='3' align='right'><strong>Total:</strong></td><td><strong>" . $orderTotal . "€</strong></td></tr>");
                print("</table>");
                print("</div>");
            }
        } else {
            print("<p>" . (($language == "EN") ? "You haven't placed any orders yet." : "Ainda não efetuou nenhuma encomenda.") . "</p>");
        }
        ?>
    </main>
</body>

</html>