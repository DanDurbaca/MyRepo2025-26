<?php
include("commonCode.php");

if (count($_SESSION["shopCart"]) == 0) {
    header("Location: Products.php");
}

// display ALL categories:

$selectCat = $connection->prepare("Select * from Categories");
$selectCat->execute();

$result = $selectCat->get_result();
while ($row = $result->fetch_assoc()) {
    $boolDisplayCategory = false;
    $numItemsInThisCategory = 0;
    foreach ($_SESSION["shopCart"] as $key => $value) {

        $selectItemCategory = $connection->prepare("Select * from ShopProducts where productId = ?");
        $selectItemCategory->bind_param("i", $key);
        $selectItemCategory->execute();
        // ONLY one result possible
        $resultProd = $selectItemCategory->get_result();
        if ($resultProd->num_rows == 1) {
            $rowProd = $resultProd->fetch_assoc();
            // I have the full details of the product that is in my shopcart ! INCLUDING its category !
            if ($rowProd["categoryId"] == $row["categoryId"]) {
                // I found ONE item in the shoping cart that belongs to THIS current to be displayed category !
                $boolDisplayCategory = true;
                $numItemsInThisCategory += $value;
            }
        }
    }

    if ($boolDisplayCategory) {
?>
        <div class="Category">
            <h2><?= $row["categoryName"] ?></h2>
            <p>Cart items in this category: <?= $numItemsInThisCategory ?></p>
        </div>


        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $sumTotalForCategory = 0;
                foreach ($_SESSION["shopCart"] as $key => $value) {

                    $selectItemCategory = $connection->prepare("Select * from ShopProducts where productId = ?");
                    $selectItemCategory->bind_param("i", $key);
                    $selectItemCategory->execute();
                    // ONLY one result possible
                    $resultProd = $selectItemCategory->get_result();
                    if ($resultProd->num_rows == 1) {
                        $rowProd = $resultProd->fetch_assoc();
                        // I have the full details of the product that is in my shopcart ! INCLUDING its category !
                        if ($rowProd["categoryId"] == $row["categoryId"]) {
                ?>
                            <tr>
                                <td><?= $rowProd["ProductName"] ?></td>
                                <td><?= $value  ?></td>
                                <?php $sumTotalForCategory += $value * $rowProd["Price"] ?>
                                <td><?= $value * $rowProd["Price"] ?> </td>
                            </tr>

                <?php
                        }
                    }
                }
                ?>

                <tr>
                    <td colspan="2">Total Price for <?= $row["categoryName"] ?> :</td>
                    <td><?= $sumTotalForCategory ?></td>
                </tr>

            </tbody>
        </table>
<?php
    }
}
?>