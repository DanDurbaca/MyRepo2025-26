<!DOCTYPE html>
<html>

<head>
	<?php include_once("common.php"); ?>
	<title><?= $arrayOfTranslations["FijiFC"] ?></title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php head("Products", "Продукты"); ?>
	<main class="destination">
		<figure>
			<figcaption><?= $arrayOfTranslations["FijiFC"] ?></figcaption>
			<img src="images/fiji.jpg" alt="Fiji" width="300">
		</figure>
		<section>
			<p>
				<?= $arrayOfTranslations["FijiP"] ?>
			</p>
			<h1><?= $arrayOfTranslations["FijiH1"] ?></h1>
			<ul>
				<li><?= $arrayOfTranslations["FijiLI1"] ?></li>
				<li><?= $arrayOfTranslations["FijiLI2"] ?></li>
				<li><?= $arrayOfTranslations["FijiLI3"] ?></li>
			</ul>
		</section>
	</main>
	<?php
	foot();
	?>
</body>

</html>