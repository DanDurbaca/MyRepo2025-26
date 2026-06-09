<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - Mystery Box Shop</title>
    <link rel="stylesheet" href="style.css?<?= time();?>">
</head>
<body>
<header>
    <img src="pictures/Logo.png" alt="Mystery Box Shop Logo">
    <h1>CS:GO Case Shop</h1>
</header>
<?php
/** @var array $arrayOfTranslations */
include_once("commoncode.php");
Melnav("Products");
?>
<div class="container">
    <h1><?= $arrayOfTranslations["WelcomeTextProducts"] ?></h1>

        <div class="AllProducts">

            <?php  

                $connection = new mysqli("localhost","root","","DatabasePouMe708");
                $sqlQuery = $connection -> prepare("SELECT * from Products");
                $sqlQuery->execute();
                $result=$sqlQuery->get_result();

                while ($row=$result->fetch_assoc()) {
            ?>

            <div class="product">
                
            <img src="pictures/<?= $row["ImageLink"] ?>"<?= $row[($language == "EN") ? "ProductNameEN" :"DescriptionGE"] ?>>
                <h3><?= $row[($language == "EN") ? "ProductNameEN" : "ProductNameGE"] ?></h3>
                <p>Price: <?= $row["Price"] ?>.00$</p>
                <p><?= $row[($language == "EN") ? "DescriptionEN" :"DescriptionGE"] ?></p>
                
                <form method="POST">
                    <input type="number" min="1" value="1" name="quantityToBuy">
                    <input type="hidden" value="<?= $row["id"] ?>" name="itemToBuy">
                    <button><?= $arrayOfTranslations["WelcomeTextProducts2"] ?></button>  
                </form>                                  
            </div>
    <?php
                }
            
    ?>
        </div>
    </div>
</div>
</body>
</html>
