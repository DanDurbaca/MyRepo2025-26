<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>MyModificationGarage Cart</title>
	<link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
</head>

<body>
	<?php
	include_once("CommonCode.php");
	NavigationBar("Cart");
	?>
	<h2><?= $arrayOfTranslations["CartTextH1"] ?></h2>
	<p><?= $arrayOfTranslations["CartText"] ?></p>

	<div class="cartContainer">
		<div class="emptyCartMessage">
			<p><?= $arrayOfTranslations["CartEmpty"] ?></p>
			<a href="Products.php?lang=<?= $language ?>" class="continueShopping">
				<?= ($language=="EN") ? "Continue Shopping" : "Continuar a Comprar" ?>
			</a>
		</div>
	</div>
	
</body>

</html>
