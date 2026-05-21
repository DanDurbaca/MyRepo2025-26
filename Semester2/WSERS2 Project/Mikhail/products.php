<!DOCTYPE html>
<html>

<head>
	<title>Products</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php
	include_once("common.php");
	head("Products");
	?>
	<main class="category">
		<?php
		$sqlQuery= $connection -> prepare("select * from Products");
		$sqlQuery->execute();
		$result=$sqlQuery->get_result();
		while ($row=$result->fetch_assoc()) {
		?>
			<figure>
				<figcaption><?= $row[($language == "EN") ? "ProductNameEN" : "ProductNameRU"] ?></figcaption>
				<img src="images/<?= $row["ImageLink"] ?>" alt="<?= $row["ProductNameEN"] ?>" width="300">
				<figcaption><a href="<?= $row["PageLink"] ?>?lang=<?= $language ?>"><?= $row[($language == "EN") ? "DescriptionEN" : "DescriptionRU"] ?></a></figcaption>
				<figcaption><?= $row["Price"] ?>€</figcaption>
				<?php if(!$_SESSION["IsAdmin"]){
				?><form method="POST">
					<input type="number" min="1" max="99" value="1" name="quantityToBuy" style="width: 40px">
					<input type="hidden" value="<?= $row["productid"] ?>" name="itemToBuy"></input>
					<input type="submit" value="buy">
				</form>
				<?php } ?>
			</figure>
		<?php
		}
		?>
	</main>
	<?php
	foot();
	?>
</body>

</html>