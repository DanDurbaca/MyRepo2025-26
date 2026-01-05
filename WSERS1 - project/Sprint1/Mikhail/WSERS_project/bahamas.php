<!DOCTYPE html>
<html>

<head>
	<?php include_once("common.php"); ?>
	<title><?= $arrayOfTranslations["BahamasFC"] ?></title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php head("Products", "Продукты"); ?>
	<main class="destination">
		<figure>
			<figcaption><?= $arrayOfTranslations["BahamasFC"] ?></figcaption>
			<img src="images/bahamas.jpg" alt="Bahamas" width="300">
		</figure>
		<section>
			<p>
				<?= $arrayOfTranslations["BahamasP"] ?>
			</p>
			<h1><?= $arrayOfTranslations["BahamasH1"] ?></h1>
			<ul>
				<li><?= $arrayOfTranslations["BahamasLI1"] ?></li>
				<li><?= $arrayOfTranslations["BahamasLI2"] ?></li>
				<li><?= $arrayOfTranslations["BahamasLI3"] ?></li>
			</ul>
		</section>
	</main>
	<?php
	foot();
	?>
</body>

</html>