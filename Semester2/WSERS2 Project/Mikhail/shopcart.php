<!DOCTYPE html>
<html>

<head>
	<title>Shopping Cart</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php
	include_once("common.php");
	head("Cart");
	?>
	<main class="register">
        <table class="cart">
            <tr>
                <th><?=$arrayOfTranslations["CartTb1"]?></th>
                <th><?=$arrayOfTranslations["CartTb2"]?></th>
                <th><?=$arrayOfTranslations["CartTb3"]?></th>
            </tr>
		<?php 
        $total=0;
        foreach($_SESSION["Cart"] as $itemID => $itemQuantity){
            $sqlQuery=$connection -> prepare("select * from Products where productid=?");
            $sqlQuery->bind_param("i", $itemID);
		    $sqlQuery->execute();
		    $result=$sqlQuery->get_result();
            $row=$result->fetch_assoc();
            $total+=$itemQuantity*$row["Price"];
        ?>
        <tr>
            <td><?= $row[($language == "EN") ? "ProductNameEN" : "ProductNameRU"]?></td>
            <td><?= $itemQuantity?></td>
            <td><?= $itemQuantity*$row["Price"] ?>$</td>
            <td><form method="POST"><input type="hidden" value="<?= $itemID ?>" name="itemToDelete"></input><input type="submit" value="<?=$arrayOfTranslations["CartTb4"]?>"></form></td>
        </tr>
        <?php 
        }
        ?>
        <tr>
            <td style="font-weight: bold"><?=$arrayOfTranslations["CartTb5"]?></td>
            <td><?= $total ?>$</td>
            <?php
            if (count($_SESSION["Cart"])>0){?>
            <td><form method="POST"><button name="Checkout" value="true"><?=$arrayOfTranslations["CartTb6"]?></button></form></td>
            <?php
            }
            ?>
        </tr>
        </table>
	</main>
	<?php
	foot();
	?>
</body>

</html>