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
		</tr>
	</table>

</body>

</html>