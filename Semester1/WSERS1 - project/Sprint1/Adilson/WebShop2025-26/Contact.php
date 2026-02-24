<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>MyModificationGarage Contact</title>
	<link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
</head>

<body>
	<?php
	include_once("CommonCode.php");
	NavigationBar("Contact");
	?>
	<h2><?= $arrayOfTranslations["ContactTextH1"] ?></h2>
	<ul>
		<li>Email: info@webshop.com</li>
		<li><?= $arrayOfTranslations["Phonetrnsl"] ?> +1 234 567 890</li>
		<li><?= $arrayOfTranslations["addresstrnsl"] ?> 123 Webshop St, Shopville, Country</li>
	</ul>
	<form methop="POST">
		<div><?= $arrayOfTranslations["Fnametrnsl"] ?></div><br>
		<input type="text" name="fname"><br>
		<div><?= $arrayOfTranslations["Lnametrnsl"] ?></div><br>
		<input type="text" name="lname"><br><br>
		<input type="submit" value="Send">
	</form>
	<?php
	/*
$_GET
is an array that contains all the filled in form data by the user.

we need to check what is inside this array:
we will use (for debug):
	var_dump($_GET);
*/
	//var_dump($_GET);
	if (isset($_POST["fname"])) {
	?>
		<h1><?= $arrayOfTranslations["sppWlcH1"] . $_POST["fname"] ?></h1> <?php
																		}
																			?>
	<p><?= $arrayOfTranslations["sppWlctxt"] ?></p>
</body>

</html>