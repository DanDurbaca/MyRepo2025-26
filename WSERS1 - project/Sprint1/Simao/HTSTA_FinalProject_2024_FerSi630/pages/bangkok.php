<!DOCTYPE html>
<html lang="en">

<head>
	<title>HTSTA Final Project</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="../style.css">
</head>

<body>
	<header>
		<?php
		include_once("commonCode.php");
		navBar("Country2");
		?>
	</header>
	<main>
		<h2>space</h2>
		<h1><a href="https://en.wikipedia.org/wiki/Bangkok" target="blank_">Bangkok</a></h1>
		<h2>space</h2>

		<a href="../images/bangkok.jpg"><img src="../images/bangkok.jpg" alt="Bangkok" style="width: 70%"></a>

		<p>
			<?= $arrayOfTranslations["BangkokDescription"]  ?>
		</p>

		<p>
			<?= $arrayOfTranslations["Recommendations"]  ?>
		</p>

		<dl>
			<dt><a href="https://en.wikipedia.org/wiki/Grand_Palace" target="blank_"><?= $arrayOfTranslations["GrandPalace"]  ?> </a></dt>
			<dt><a href="https://en.wikipedia.org/wiki/Giant_Swing" target="blank_"><?= $arrayOfTranslations["GiantSwing"]  ?> </a></dt>
			<dt><a href="https://en.wikipedia.org/wiki/Wat_Arun" target="blank_"><?= $arrayOfTranslations["WatArun"]  ?> </a></dt>
		</dl>

		<h2>space</h2>
	</main>
	<footer>
		<p> LPEM - HTSTA Final Project 2024 </p>
	</footer>
</body>

</html>