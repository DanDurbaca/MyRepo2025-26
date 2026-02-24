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
		<h1><a href="https://en.wikipedia.org/wiki/Ko_Phi_Phi" target="blank_">Ko Phi Phi</a></h1>
		<h2>space</h2>

		<a href="../images/phiphi.jpg"><img src="../images/phiphi.jpg" alt="Ko Phi Phi" style="width: 70%"></a>

		<p>
			<?= $arrayOfTranslations["KoPhiPhiDescription"]  ?>
		</p>

		<p>
			<?= $arrayOfTranslations["Recommendations"]  ?>
		</p>

		<dl>
			<dt><a href="https://en.wikipedia.org/wiki/Ko_Phi_Phi_Le" target="blank_"><?= $arrayOfTranslations["MayaBay"]  ?></a></dt>
			<dt><a href="https://en.wikipedia.org/wiki/Ko_Phi_Phi_Le" target="blank_"><?= $arrayOfTranslations["VikingCave"]  ?></a></dt>
			<dt><a href="https://www.tonsaibayresort.com/" target="blank_"><?= $arrayOfTranslations["TonsaiBay"]  ?></a></dt>
		</dl>

		<h2>space</h2>
	</main>
	<footer>
		<p> LPEM - HTSTA Final Project 2024 </p>
	</footer>
</body>

</html>