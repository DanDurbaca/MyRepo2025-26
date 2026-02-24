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
		<h1><a href="https://en.wikipedia.org/wiki/Khon_Kaen" target="blank_">Khon Kaen</a></h1>
		<h2>space</h2>

		<a href="../images/khon.jpg"><img src="../images/khon.jpg" alt="Khon Kaen" style="width: 70%"></a>

		<p>
			<?= $arrayOfTranslations["KhonKaenDescription"]  ?>
		</p>

		<p>
			<?= $arrayOfTranslations["Recommendations"]  ?>
		</p>

		<dl>
			<dt><a href="https://www.tourismthailand.org/Attraction/khon-kaen-national-museum" target="blank_"><?= $arrayOfTranslations["NationalMuseum"]  ?></a></dt>
			<dt><a href="https://en.wikipedia.org/wiki/Phra_Mahathat_Kaen_Nakhon" target="blank_"><?= $arrayOfTranslations["PhraNakhon"]  ?></a></dt>
			<dt><a href="https://www.tourismthailand.org/Attraction/ton-tan-market" target="blank_"><?= $arrayOfTranslations["TonTann"]  ?></a></dt>
		</dl>

		<h2>space</h2>
	</main>
	<footer>
		<p> LPEM - HTSTA Final Project 2024 </p>
	</footer>
</body>

</html>