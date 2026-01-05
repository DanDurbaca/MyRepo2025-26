<!DOCTYPE html>
<html>

<head>
	<?php include_once("common.php"); ?>
	<title><?= $arrayOfTranslations["RomeFC"] ?></title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php head("Products", "Продукты"); ?>
	<main class="destination">
		<figure>
			<figcaption><?= $arrayOfTranslations["RomeFC"] ?></figcaption>
			<img src="images/rome.jpg" alt="Las Vegas" width="300">
		</figure>
		<section>
			<p>
				<?= $arrayOfTranslations["RomeP"] ?>
			</p>
			<h1><?= $arrayOfTranslations["RomeH1"] ?></h1>
			<ul>
				<li><?= $arrayOfTranslations["RomeLI1"] ?></li>
				<li><?= $arrayOfTranslations["RomeLI2"] ?></li>
				<li><?= $arrayOfTranslations["RomeLI3"] ?></li>
			</ul>
		</section>
	</main>
	<?php
	foot();
	?>
</body>

</html>