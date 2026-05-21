<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyModificationGarage Cart</title>
    <link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
    <style>
        .cartTable {
            width: 90%;
            max-width: 1000px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.1);
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .cartTable th,
        .cartTable td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .cartTable th {
            background: rgba(255, 255, 255, 0.15);
            font-weight: bold;
            color: #ffd700;
        }

        .cartTable tr:last-child td {
            border-bottom: none;
        }

        .cartTable tr:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .removeBtn {
            background: rgba(255, 100, 100, 0.7);
            border: 1px solid rgba(255, 100, 100, 0.9);
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .removeBtn:hover {
            background: rgba(255, 100, 100, 0.9);
            transform: scale(1.05);
        }

        .qtyInput {
            width: 60px;
            padding: 6px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-align: center;
            font-weight: bold;
        }

        .totalSection {
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: right;
        }

        .totalSection h3 {
            color: #ffd700;
            font-size: 24px;
            margin: 0;
        }

        .checkoutBtn {
            display: inline-block;
            background: rgba(100, 200, 100, 0.7);
            border: 1px solid rgba(100, 200, 100, 0.9);
            color: #fff;
            font-weight: bold;
            font-size: 16px;
            padding: 12px 30px;
            border-radius: 8px;
            margin-top: 15px;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .checkoutBtn:hover {
            background: rgba(100, 200, 100, 0.9);
            transform: translateY(-3px);
        }

        .cartHeader {
            text-align: center;
            color: #ffd700;

        }
    </style>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Cart");

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }

    //remove btn
    if (isset($_POST["removeProduct"])) {
        $productRemove = $_POST["removeProduct"];
        unset($_SESSION["cart"][$productRemove]);
    }

    if (isset($_POST["checkoutbtn"])) {
        $sqlQuery = $connection->prepare("SELECT * from clients where username = ?");
        $sqlQuery->bind_param("s", $_SESSION["LoggedUserName"]);
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();
        $row = $result->fetch_assoc();
        $clientid = $row["clientID"];
        $sqlQuery = $connection->prepare("INSERT INTO orders(clientId) values(?)");
        $sqlQuery->bind_param("i", $clientid);
        $sqlQuery->execute();

        $sqlQuery = $connection->prepare("SELECT * from orders ORDER BY orderid DESC LIMIT 1;");
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();
        $row = $result->fetch_assoc();
        $orderid = $row["OrderID"];

        foreach ($_SESSION["cart"] as $itemId => $itemQuantity) {
            $sqlQuery = $connection->prepare("INSERT INTO boghtitem(quantity, productid, orderid) values(?,?,?)");
            $sqlQuery->bind_param("iii", $itemQuantity, $itemId, $orderid);
            $sqlQuery->execute();
        }
        $_SESSION["cart"] = [];
        
    }

    ?>

    <div class="cartHeader">
        <h2><?= $arrayOfTranslations["CartTextH1"] ?></h2>
    </div>

    <?php if (count($_SESSION["cart"]) == 0) { ?>
        <div class="cartContainer">
            <div class="emptyCartMessage">
                <p><?= $arrayOfTranslations["CartEmpty"] ?></p>
                <a href="Products.php?lang=<?= $language ?>" class="continueShopping">
                    <?= ($language == "EN") ? "Continue Shopping" : "Continuar a Comprar" ?>
                </a>
            </div>
        </div>
    <?php } else { ?>
        <table class="cartTable">
            <thead>
                <tr>
                    <th><?= ($language == "EN") ? "Product" : "Produto" ?></th>
                    <th><?= ($language == "EN") ? "Price" : "Preço" ?></th>
                    <th><?= ($language == "EN") ? "Quantity" : "Quantidade" ?></th>
                    <th><?= ($language == "EN") ? "Subtotal" : "Subtotal" ?></th>
                    <th><?= ($language == "EN") ? "Action" : "Ação" ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;

                foreach ($_SESSION["cart"] as $itemId => $itemQuantity) {

                    $connection = new mysqli("localhost", "root", "", "webshopdb");
                    $sqlQuery = $connection->prepare("SELECT * FROM products where productID=?;");
                    $sqlQuery->bind_param("i", $itemId);
                    $sqlQuery->execute();
                    $result = $sqlQuery->get_result();
                    $row = $result->fetch_assoc();

                    $total += $row["productPrice"] * $itemQuantity;

                ?>
                    <tr>
                        <td><?= $row[($language == "EN") ? "productNameEN" : "productNamePT"] ?></td>
                        <td> <?= $row["productPrice"] . "€" ?></td>
                        <td><?= $itemQuantity ?></td>
                        <td><?= $row["productPrice"] * $itemQuantity . "€" ?> </td>

                        <td>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="removeProduct" value="<?= $itemId ?>" class="removeBtn">
                                    <?= ($language == "EN") ? "Remove" : "Remover" ?>
                                </button>
                            </form>
                        </td>
                    </tr>

                <?php } ?>
            </tbody>
        </table>

        <div class="totalSection">
            <h3><?= ($language == "EN") ? "Total: " : "Total: " ?><?= $total ?> €</h3>
            <a href="Products.php?lang=<?= $language ?>" class="continueShopping">
                <?= ($language == "EN") ? "Continue Shopping" : "Continuar a Comprar" ?>
            </a>
            <form method="POST">
                <button type="submit" name="checkoutbtn" class="checkoutBtn">
                    <?= ($language == "EN") ? "Checkout" : "Finalizar Compra" ?>
                </button>
            </form>
        </div>
    <?php } ?>

</body>

</html>