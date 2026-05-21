<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css?<?= time() ?>">
    <title>HTSTA Final Project</title>
</head>

<body>
    <nav>
        <?php
        include_once("commonCode.php");
        navBar("Cart");
        ?>
    </nav>
    <main>
        <?php

        if (!isset($_SESSION["Cart"])) {
            $_SESSION["Cart"] = [];
            $counter = 0;
        }
        if (isset($_POST["removeProduct"])) {
            $productRemove = $_POST["removeProduct"];
            unset($_SESSION["Cart"][$productRemove]);
        }
        $total = 0;
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
                $connection = new mysqli("localhost", "root", "", "HTSTA_DB");
                $sqlQuery = $connection->prepare("SELECT * FROM Products where productID=?;");
                $sqlQuery->bind_param("i", $itemId);
                $sqlQuery->execute();
                $result = $sqlQuery->get_result();
                $row = $result->fetch_assoc();

                $total += $row["price"] * $itemQuantity;

                global $itemQuantity, $counter;
                $counter += $itemQuantity;
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
        <h3><?= ($language == "EN") ? "Total: " : "Total: " ?><?= $total ?> €</h3>
        <p>
            <a style="text-decoration: none; color: black"  href="country1.php?lang=<?= $language ?>">
                <?= ($language == "EN") ? "Continue Shopping (click here!)" : "Continuar a Comprar (clique aqui!)" ?>
            </a>
        <p>
        <form method="POST">
            <button type="submit" name="checkoutbtn">
                <?= ($language == "EN") ? "Checkout" : "Finalizar Compra" ?>
            </button>
        </form>
</body>
</main>

</html>