<!DOCTYPE html>
<html lang="en">

<head>
	<title>HTSTA Final Project</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="../style.css">
</head>

<body>
	<?php
	include("commonCode.php");
	navBar("Country2");
	?>
	<main>
		<h1><a href="https://en.wikipedia.org/wiki/Thailand" target="blank_"><?= $arrayOfTranslations["Thailand"]  ?></a></h1>
		<h2>space</h2>
		<figure>
			<a href="bangkok.php"> <img src="../images/bangkok.jpg" alt="Bangkok" width="50%"> </a>

			<figcaption>
				<a href="bangkok.php">Bangkok</a>
			</figcaption>

			<a href="khon.php"> <img src="../images/khon.jpg" href="khon.php" alt="Khon Kaen" width="50%"> </a>

			<figcaption>
				<a href="khon.php">Khon Kaen</a>
			</figcaption>

			<a href="phiphi.php"> <img src="../images/phiphi.jpg" alt="Ko Phi Phi" width="50%"> </a>

			<figcaption>
				<a href="phiphi.php">Ko Phi Phi</a>
			</figcaption>
		</figure>
		<h2>space</h2>
		<h2>space</h2>
	</main>
	<footer>
		<p> LPEM - HTSTA Final Project 2024 </p>
	</footer>
</body>

</html>