<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?<?= time() ?>">
    <title>HTSTA Final Project</title>
</head>

<body>
    <nav>
        <?php
        include_once("commonCode.php");
        navBar("Cart");
        
        $connection = new mysqli("localhost", "root", "", "HTSTA_DB");
        ?>
    </nav>
    <main>
        <?php
        // Check if checkout form was sent
        if (isset($_POST["checkoutbtn"])) {
            if (isset($_SESSION["Cart"]) && count($_SESSION["Cart"]) > 0) {
                
                // CRITICAL FIX: Match the EXACT session username variable used in login
                // Your commonCode uses $username = $_SESSION["Username"] ?? "";
                $sessionUser = isset($_SESSION["Username"]) ? $_SESSION["Username"] : "";

                if (!empty($sessionUser)) {
                    $sqlInsertOrder = $connection->prepare("INSERT into Orders (username, status) values (?, 'pending')");
                    $sqlInsertOrder->bind_param("s", $sessionUser);
                    $sqlInsertOrder->execute();

                    $orderID = $connection->insert_id;

                    foreach ($_SESSION["Cart"] as $itemId => $itemQuantity) {
                        $sqlInsertBought = $connection->prepare("INSERT into BoughtItem (orderID, productID, quantity) values (?, ?, ?)");
                        $sqlInsertBought->bind_param("iii", $orderID, $itemId, $itemQuantity);
                        $sqlInsertBought->execute();
                    }

                    // Clear cart ONLY after loop completes successfully
                    $_SESSION["Cart"] = [];
                    print("<p style='color: green; font-weight: bold;'>" . (($language == "EN") ? "Order placed successfully!" : "Encomenda realizada com sucesso!") . "</p>");
                } else {
                    print("<p style='color: red;'>Error: You must be logged in to place an order.</p>");
                }
            } else {
                print("<p style='color: red;'>Your cart is empty.</p>");
            }
        }

        if (!isset($_SESSION["Cart"]) || count($_SESSION["Cart"]) == 0) {
            print $arrayOfTranslations["emptyCart"];
        }

        $total = 0;
        if (count($_SESSION["Cart"]) > 0) {
        ?>
            <h1><?= $arrayOfTranslations["CartTitle"] ?></h1>
            <table>
                <tr>
                    <th><?= $arrayOfTranslations["Item"] ?></th>
                    <th><?= $arrayOfTranslations["Price"] ?></th>
                    <th><?= $arrayOfTranslations["Quantity"] ?></th>
                    <th><?= $arrayOfTranslations["SubTotal"] ?></th>
                    <th><?= $arrayOfTranslations["Action"] ?></th>
                </tr>
                <?php
                foreach ($_SESSION["Cart"] as $itemId => $itemQuantity) {
                    $sqlQuery = $connection->prepare("SELECT * FROM Products where productID = ?;");
                    $sqlQuery->bind_param("i", $itemId);
                    $sqlQuery->execute();
                    $result = $sqlQuery->get_result();
                    $row = $result->fetch_assoc();

                    $total += $row["price"] * $itemQuantity;
                ?>
                    <tr>
                        <td><?= $row[($language == "EN") ? "productEN" : "productPT"] ?></td>
                        <td> <?= $row["price"] . "€" ?></td>
                        <td><?= $itemQuantity ?> </td>
                        <td><?= $row["price"] * $itemQuantity . "€" ?> </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="removeProduct" value="<?= $itemId ?>"> <?= $arrayOfTranslations["RemoveBtn"] ?> </button>
                            </form>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </table>
        <?php } ?>

        <p>
            <a style="text-decoration: none; color: black" href="country1.php?lang=<?= $language ?>">
                <?= ($language == "EN") ? "Continue Shopping (click here!)" : "Continuar a Comprar (clique aqui!)" ?>
            </a>
        </p>
        <h3><?= ($language == "EN") ? "Total: " : "Total: " ?><?= $total ?> €</h3>
        
        <form method="POST">
            <input type="submit" name="checkoutbtn" value="<?= ($language == "EN") ? "Checkout" : "Finalizar Compra" ?>">
        </form>
    </main>
</body>
</html>