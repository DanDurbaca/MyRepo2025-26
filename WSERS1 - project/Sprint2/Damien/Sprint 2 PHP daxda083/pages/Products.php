<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
	<link rel="stylesheet" href="style.css?<?= time() ?>">
	<meta charset="utf-8">
	<title>Products</title>

</head>

<body class="ProductBG">

	<?php
	include_once("function.php");
	NavigationBar("Products");
	?>

	<h1 class="ProductTitle">
		<?= $arrayTranslation["Productslable"] ?>
	</h1>

	<div class="AllProducts">
		<?php
		$fileProducts = fopen("Products.csv", "r");
		fgets($fileProducts);
		while (!feof($fileProducts)) {
			$oneProduct = fgets($fileProducts);
			$individualItemComponents = explode(";", $oneProduct);
			if (count($individualItemComponents) ==5 ){
		?> 
			<div class="OneProduct">
				<div><?= $individualItemComponents[0] ?>	</div>
				<img src="<?= $individualItemComponents[1] ?>">
				<div><?= $individualItemComponents[2] ?></div>
				<div><?=  $individualItemComponents[($language =="EN") ? 3 : 4] ?></div>
			</div>

		<?php
			}
		}
		?>

			
	</div>
</body>

</html>