<!DOCTYPE html>
<html lang="en">
	<head>
		<title>HTSTA Final Project</title>
		<meta charset="utf-8">
		<link rel="stylesheet" href="\HTSTAProject/HTSTA_FinalProject_2024_FerSi630/style.css?<?= time() ?>">
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
	</head>
	<body>
		<?php
			include("commonCode.php");
			navBar("Home");
		?>
		<main>
			<h2>space</h2>
			<h1>
				<?= $arrayOfTranslations["Welcome"] ?>
			</h1>
			<h2>space</h2>
			<p>	
				<?= $arrayOfTranslations["HomeText"] ?>
			</p>
			<h2>space</h2>
			<p>
				<?= $arrayOfTranslations["HomeNav"] ?>
			</p>
			<h2>space</h2>
			<img src="../images/smiley.jpg" alt="Smiley face" width="40%">
		</main>
		<footer>
			<p> LPEM - HTSTA Final Project 2024 </p>
		</footer>
	</body>
</html>