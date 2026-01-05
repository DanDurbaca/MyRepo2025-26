<!DOCTYPE html>
<html>

<head>
	<?php include_once("common.php"); ?>
	<title><?= $arrayOfTranslations["BaliFC"] ?></title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php head("Products", "Продукты"); ?>
	<main class="destination">
		<figure>
			<figcaption><?= $arrayOfTranslations["BaliFC"] ?></figcaption>
			<img src="images/bali.jpg" alt="Bali" width="300">
		</figure>
		<section>
			<p>
				<?= $arrayOfTranslations["BaliP"] ?>
			</p>
			<h1><?= $arrayOfTranslations["BaliH1"] ?></h1>
			<ul>
				<li><?= $arrayOfTranslations["BaliLI1"] ?></li>
				<li><?= $arrayOfTranslations["BaliLI2"] ?></li>
				<li><?= $arrayOfTranslations["BaliLI3"] ?></li>
			</ul>
		</section>
	</main>
	<?php
	foot();
	?>
</body>

</html>