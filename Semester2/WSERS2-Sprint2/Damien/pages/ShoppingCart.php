<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
	<link rel="stylesheet" href="style.css?<?= time() ?>">
	<meta charset="utf-8">
	<title>Products</title>

</head>

<body class="ProductBG">
	<?php
	include_once("function.php");
	NavigationBar("ShoppingCart");
	?>
	<h1><?= $arrayTranslation["CartLable"] ?></h1>
	<table>
		<tr>
			<th><?= $arrayTranslation["ItemLable"] ?></th>
			<th><?= $arrayTranslation["QUANTITYLABLE"] ?></th>
			<th><?= $arrayTranslation["PriceID"] ?></th>
		</tr>
		<?php
		$total=0;
		foreach ($_SESSION["Cart"] as $itemID => $itemQuantity){
		$sqlQuery=$connection->prepare("select * from products where ProductNameEN = ?");
		$sqlQuery->bind_param("s", $itemID);
        $sqlQuery->execute();
        $result=$sqlQuery->get_result();
		$price = $result->fetch_assoc()["Price"];
		$total+= $itemQuantity*floatval($price);

		?>
			<tr>
				<td> <?= $itemID ?></td>
				<td> <?= $itemQuantity ?> </td>
				<td> <?= $price ?> </td>
				<td>
					<form method="post">
						<input type="hidden" name="itemToDelete" value="<?= $itemID ?>">
						<input type="submit" name="removeFromCart" value="<?= $arrayTranslation["DeleteBtn"] ?>">
					</form>
				</td>
			</tr>
		<?php
		}
		?>
		<tr>
			<td>Total:</td>
			<td></td>
			<td><?= $total?>$</td>
			<?php
            if (count($_SESSION["Cart"])>0){?>
            <td><form method="POST"><button name="Checkout" value="true"><?= $arrayTranslation["CheckoutBtn"] ?></button></form></td>
            <?php
            }
            ?>
		</tr>
	</table>
	<br>
        <br>
        <h1><?= $arrayTranslation["OrderHistoryLable"] ?></h1>
        <table class="cart">
            <tr>
                <th><?= $arrayTranslation["OrderID"] ?></th>
                <th><?= $arrayTranslation["ContentsLable"] ?></th>
                <th><?= $arrayTranslation["StatusID"] ?></th>
            </tr>
		<?php 
        $sqlQuery=$connection -> prepare("select o.orderid, o.statusEN, o.statusDE from orders o join clients c on o.username=c.username where o.username=? order by orderid desc;");
		$sqlQuery->bind_param("s", $_SESSION["Userlogged"]);
        $sqlQuery->execute();
		$result=$sqlQuery->get_result();
		while ($row=$result->fetch_assoc()) {
            ?>
            <tr>
                <td><?= $row["orderid"]?></td>
                <td><table><?php 
                $sqlSubQuery=$connection -> prepare("select p.ProductNameEN, bi.quantity from orders o join boughtitem bi on o.orderid=bi.orderid join products p on bi.ProductNameEN=p.ProductNameEN where o.orderid=?;");
                $sqlSubQuery->bind_param("i", $row["orderid"]);
                $sqlSubQuery->execute();
                $subResult=$sqlSubQuery->get_result();
                while ($subRow=$subResult->fetch_assoc()){
                ?>
                <tr>
                    <td><?= $subRow["ProductNameEN"] ?></td>
                    <td><?= $subRow["quantity"] ?></td>
                </tr>
                <?php
                }
                ?></table></td>
                <td><?= $row[($language == "EN") ? "statusEN" : "statusDE"] ?></td>
            </tr>
            <?php 
        }
        ?>
        </table>
</body>

</html>