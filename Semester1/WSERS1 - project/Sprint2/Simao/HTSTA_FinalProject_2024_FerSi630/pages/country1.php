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
	navBar("Country1");
	?>
	<main>

		<h1><a href="https://en.wikipedia.org/wiki/Italy" target="blank_"><?= $arrayOfTranslations["Italy"] ?></a></h1>
		<h2>space</h2>
		<figure>
			<a href="sicily.php"> <img src="../images/sicily_tn.jpg" alt="Sicily"> </a>

			<figcaption>
				<a href="sicily.php">Sicily</a>
			</figcaption>

			<a href="rome.php"> <img src="../images/rome_tn.jpg" alt="Rome"> </a>

			<figcaption>
				<a href="rome.php">Rome</a>
			</figcaption>

			<a href="milan.php"> <img src="../images/milan_tn.jpg" alt="Milan"> </a>

			<figcaption>
				<a href="milan.php">Milan</a>
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