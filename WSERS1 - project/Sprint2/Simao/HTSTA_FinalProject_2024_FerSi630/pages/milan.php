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
		navBar("Country1");
		?>
	</header>
	<main>
		<h2>space</h2>
		<h1><a href="https://en.wikipedia.org/wiki/Milan" target="blank_"><?= $arrayOfTranslations["MilanTitle"]  ?></a></h1>
		<h2>space</h2>

		<a href="../images/milan.jpg"><img src="../images/milan.jpg" alt="Milan" style="width: 50%"></a>

		<p>
			<?= $arrayOfTranslations["MilanDescription"]  ?>
		</p>

		<p>
			<?= $arrayOfTranslations["Recommendations"]  ?>
		</p>

		<dl>
			<dt><a href="https://en.wikipedia.org/wiki/Milan_Cathedral" target="blank_"><?= $arrayOfTranslations["Cathedral"]  ?></a></dt>
			<dt><a href="https://en.wikipedia.org/wiki/The_Last_Supper_(Leonardo)" target="blank_"><?= $arrayOfTranslations["TourGuide"]  ?>"</a></dt>
			<dt><a href="https://en.wikipedia.org/wiki/Navigli" target="blank_"><?= $arrayOfTranslations["Navigali"]  ?></a></dt>
		</dl>
		<h2>space</h2>
	</main>
	<footer>
		<p> LPEM - HTSTA Final Project 2024 </p>
	</footer>
</body>

</html>