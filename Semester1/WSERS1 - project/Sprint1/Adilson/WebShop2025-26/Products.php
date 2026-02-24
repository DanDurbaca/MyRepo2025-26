<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>MyModificationGarage Products</title>
	<link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
</head>

<body>
	<?php
	include_once("CommonCode.php");
	NavigationBar("Products");
	?>
	<h2>Our Products</h2>
	<p>Browse our selection and find the perfect product for you!</p>

	<div class="allDivs">
	<?php 
	$fileProducts = fopen("products.csv", "r");
	$headerOfTable = fgets($fileProducts);
	while (!feof($fileProducts)) {
		$oneProduct = fgets($fileProducts);
		$individualItemComponents = explode(";", $oneProduct);
		if (count($individualItemComponents) == 6) {
			?>
			<div class="divStyle">
				<div class="productNameDivStyle"><?= $individualItemComponents[($language=="EN") ? 0 : 5] ?></div>
				<img src="img/<?= $individualItemComponents [1] ?>" alt="Brake Pads" style="width:180px; height:180px; object-fit:cover; border-radius:8px;">
				<div class="colorWite"><?= $individualItemComponents [2] ?></div>
				<div><?= $individualItemComponents[($language=="EN") ? 3 : 4] ?></div>
				<button class="addToCartBtn"><?= ($language=="EN") ? "Add to Cart" : "Adicionar ao Carrinho" ?></button>
			</div>
			<?php
		}
	}
	?>
		
	</div>
	
</body>

</html>