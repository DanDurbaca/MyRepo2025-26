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
	NavigationBar("Products");
	?>

	<h1 class="PH1">
		<?= $arrayTranslation["Productslable"] ?>
	</h1>

	<div class="AllProducts">
		<?php
		$connection = new mysqli("localhost", "root", "", "Daxda083");
		$sqlQuery = $connection->prepare("select * from products");
		$sqlQuery->execute();
		$result = $sqlQuery->get_result();
		while ($row = $result->fetch_assoc()) {
		?>
			<div class="OneProduct">
				<div style="font-size: xx-large;"><?= $row["ProductNameEN"] ?> </div>
				<img src="<?= $row["ImageLink"] ?>">
				<div><?= $row["Price"] ?></div>
				<div><?= $row[($language == "EN") ? "DescriptionEN" : "DescriptionDE"] ?></div>
				<?php if($_SESSION["UserType"]!="admin" && $_SESSION["Userlogged"]!="false"){ ?>
				<form method="POST">
					<input type="number" min="1" value="1" placeholder="quantity" name="quantityToBuy">
					<input type="hidden" value="<?= $row["ProductNameEN"] ?>" name="itemToBuy">
					<input type="submit" value="<?= $arrayTranslation["BuyBTN"] ?>">
				</form>
				<?php } ?>
			</div>

		<?php
		}
		?>


	</div>
</body>

</html>