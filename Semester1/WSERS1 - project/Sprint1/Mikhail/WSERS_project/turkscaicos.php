<!DOCTYPE html>
<html>

<head>
	<?php include_once("common.php"); ?>
	<title><?= $arrayOfTranslations["TurksCaicosFC"] ?></title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php head("Products", "Продукты"); ?>
	<main class="destination">
		<figure>
			<figcaption><?= $arrayOfTranslations["TurksCaicosFC"] ?></figcaption>
			<img src="images/turkscaicos.jpg" alt="Turks &amp; Caicos" width="300">
		</figure>
		<section>
			<p>
				<?= $arrayOfTranslations["TurksCaicosP"] ?>
			</p>
			<h1><?= $arrayOfTranslations["TurksCaicosH1"] ?></h1>
			<ul>
				<li><?= $arrayOfTranslations["TurksCaicosLI1"] ?></li>
				<li><?= $arrayOfTranslations["TurksCaicosLI2"] ?></li>
				<li><?= $arrayOfTranslations["TurksCaicosLI3"] ?></li>
			</ul>
		</section>
	</main>
	<?php
	foot();
	?>
</body>

</html>