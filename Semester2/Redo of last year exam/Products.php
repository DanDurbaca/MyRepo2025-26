<?php
include_once("commonCode.php");
?>


<div class="WelcomeText">
    <h1>Welcome to the shop</h1>
    <p>Here you can find a variety of products.</p>
</div>


<div class="AllProducts">

    <?php
    //var_dump($_SESSION);
    //var_dump($_POST);
    //print("The hour now is :" . (int)date("H"));

    if ($_SESSION["ShopCategory"] != 0) {
        $selectCat = $connection->prepare("Select * from ShopProducts where categoryId = ?");
        $selectCat->bind_param("i", $_SESSION["ShopCategory"]);
    } else {
        $selectCat = $connection->prepare("Select * from ShopProducts");
    }


    $selectCat->execute();
    $result = $selectCat->get_result();
    while ($row = $result->fetch_assoc()) {
    ?>

        <div class="OneProduct">
            <div><?= $row["ProductName"] ?></div>
            <div><?= $row["Price"] ?> EUR</div>
            <img src=".\images\<?= $row["ImageLink"] ?> ?>" />

            <div>Inventory: <?= $row["Inventory"] ?> </div>
            <form method="POST">
                <input type="hidden" name="productId" value="<?= $row["productId"] ?> ?>" />
                <input type="submit" value="Add to cart" />
            </form>
        </div>

    <?php
    }
    ?>


</div>



</html>