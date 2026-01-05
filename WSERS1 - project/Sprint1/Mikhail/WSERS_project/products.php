<!DOCTYPE html>
<html>

<head>
	<title>Beaches</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php
	include_once("common.php");
	head("Products", "Продукты");
	?>
	<main class="category">
		<?php
		$fileProducts = fopen("Products.csv", "r");
		fgets($fileProducts);
		fgets($fileProducts);
		while (!feof($fileProducts)) {
			$oneProduct = fgets($fileProducts);
			$individualItemComponents = explode(";", $oneProduct);
		?>
			<figure>
				<figcaption><?= $individualItemComponents[($language == "EN") ? 0 : 6] ?></figcaption>
				<img src="images/<?= $individualItemComponents[1] ?>" alt="<?= $individualItemComponents[0] ?>" width="300">
				<figcaption><a href="<?= $individualItemComponents[2] ?>?lang=<?= $language ?>"><?= $individualItemComponents[($language == "EN") ? 4 : 5] ?></a></figcaption>
				<figcaption><?= $individualItemComponents[3] ?>€</figcaption>
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