<!DOCTYPE html>
<html>

<head>
	<?php include_once("common.php"); ?>
	<title><?= $arrayOfTranslations["LasVegasFC"] ?></title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php head("Products", "Продукты"); ?>
	<main class="destination">
		<figure>
			<figcaption><?= $arrayOfTranslations["LasVegasFC"] ?></figcaption>
			<img src="images/lasvegas.jpg" alt="Las Vegas" width="300">
		</figure>
		<section>
			<p>
				<?= $arrayOfTranslations["LasVegasP"] ?>
			</p>
			<h1><?= $arrayOfTranslations["LasVegasH1"] ?></h1>
			<ul>
				<li><?= $arrayOfTranslations["LasVegasLI1"] ?></li>
				<li><?= $arrayOfTranslations["LasVegasLI2"] ?></li>
				<li><?= $arrayOfTranslations["LasVegasLI3"] ?></li>
			</ul>
		</section>
	</main>
	<?php
	foot();
	?>
</body>

</html>