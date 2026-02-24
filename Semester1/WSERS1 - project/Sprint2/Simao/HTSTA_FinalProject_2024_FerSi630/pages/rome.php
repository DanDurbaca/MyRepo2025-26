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
		<h1><a href="https://en.wikipedia.org/wiki/Rome" target="blank_"><?= $arrayOfTranslations["RomeTitle"]  ?></a></h1>
		<h2>space</h2>

		<a href="../images/rome.jpg"><img src="../images/rome.jpg" alt="Rome" style="width: 50%"></a>

		<p>
			<?= $arrayOfTranslations["RomeDescription"]  ?>
		</p>

		<p>
			<?= $arrayOfTranslations["Recommendations"]  ?>
		</p>

		<dl>
			<dt><a href="https://en.wikipedia.org/wiki/Colosseum" target="blank_"><?= $arrayOfTranslations["Colosseum"]  ?></a></dt>
			<dt><a href="https://en.wikipedia.org/wiki/Pantheon,_Rome" target="blank_"><?= $arrayOfTranslations["Pantheon"]  ?></a></dt>
			<dt><a href="https://en.wikipedia.org/wiki/Vatican_Museums" target="blank_"><?= $arrayOfTranslations["Museums"]  ?></a></dt>
		</dl>
		<h2>space</h2>
	</main>
	<footer>
		<p> LPEM - HTSTA Final Project 2024 </p>
	</footer>
</body>

</html>