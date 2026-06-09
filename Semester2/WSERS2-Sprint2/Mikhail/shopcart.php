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
	<main class="admin">
        <h1><?=$arrayOfTranslations["CartH1"]?></h1>
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
            <td><?= $itemQuantity*$row["Price"] ?>€</td>
            <td><form method="POST"><input type="hidden" value="<?= $itemID ?>" name="itemToDelete"></input><input type="submit" value="<?=$arrayOfTranslations["CartTb4"]?>"></form></td>
        </tr>
        <?php 
        }
        ?>
        <tr>
            <td style="font-weight: bold"><?=$arrayOfTranslations["CartTb5"]?></td>
            <td><?= $total ?>€</td>
            <?php
            if (count($_SESSION["Cart"])>0){?>
            <td><form method="POST"><button name="Checkout" value="true"><?=$arrayOfTranslations["CartTb6"]?></button></form></td>
            <?php
            }
            ?>
        </tr>
        </table>
        <br>
        <br>
        <h1><?=$arrayOfTranslations["CartH2"]?></h1>
        <table class="cart">
            <tr>
                <th><?=$arrayOfTranslations["CartTb7"]?></th>
                <th><?=$arrayOfTranslations["CartTb8"]?></th>
                <th><?=$arrayOfTranslations["CartTb9"]?></th>
            </tr>
		<?php 
        $sqlQuery=$connection -> prepare("select o.orderid, o.statusEN, o.statusRU from orders o join clients c on o.username=c.username where o.username=? order by orderid desc;");
		$sqlQuery->bind_param("s", $_SESSION["UserLogged"]);
        $sqlQuery->execute();
		$result=$sqlQuery->get_result();
		while ($row=$result->fetch_assoc()) {
            ?>
            <tr>
                <td><?= $row["orderid"]?></td>
                <td><table><?php 
                $sqlSubQuery=$connection -> prepare("select p.ProductNameEN, p.ProductNameRU, bi.quantity from orders o join boughtitem bi on o.orderid=bi.orderid join products p on bi.productid=p.productid where o.orderid=?;");
                $sqlSubQuery->bind_param("i", $row["orderid"]);
                $sqlSubQuery->execute();
                $subResult=$sqlSubQuery->get_result();
                while ($subRow=$subResult->fetch_assoc()){
                ?>
                <tr>
                    <td><?= $subRow[($language == "EN") ? "ProductNameEN" : "ProductNameRU"] ?></td>
                    <td><?= $subRow["quantity"] ?></td>
                </tr>
                <?php
                }
                ?></table></td>
                <td><?= $row[($language == "EN") ? "statusEN" : "statusRU"] ?></td>
            </tr>
            <?php 
        }
        ?>
        </table>
	</main>

</body>

</html>