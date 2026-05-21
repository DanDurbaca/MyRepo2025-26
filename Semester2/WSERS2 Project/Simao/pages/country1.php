<!DOCTYPE html>
<html lang="en">

<head>
	<title>HTSTA Final Project</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="../style.css?<?= time() ?>">
</head>

<body>
	<?php
	include("commonCode.php");
	navBar("Products");


	?>
	<main>

		<h1><a href="https://en.wikipedia.org/wiki/Italy" target="blank_"><?= $arrayOfTranslations["Countries"] ?></a></h1>
		<h2>space</h2>
		<?php
		
		$sqlQuery = $connection->prepare("SELECT * FROM Products");
		$sqlQuery->execute();
		$result = $sqlQuery->get_result();
		while ($row = $result->fetch_assoc()) {
		?>
			<figure>
				<div class="pageLink"><a href="<?= $row["pageLink"] ?>">
						<figcaption><?= $row[($language == "EN") ? "productEN" : "productPT"] ?></figcaption>
					</a></div>
				<a href="<?= $row["imageLink"] ?>"><img src="<?= $row["imageLink"] ?>" alt="<?= $row["productEN"] ?>" width="300"></a>
				<figcaption><?= $row["price"] ?>€</figcaption>
				<form method="POST">
					<?php if ($_SESSION["UserLogged"] == true && $_SESSION["IsAdmin"] == false) {
					?>  <input type="number" placeholder=" <?= ($language == "EN") ? "quantity" : "quantidade" ?>" name="quantityToBuy">
						<input type="submit" value=" <?= ($language == "EN") ? "Buy" : "Comprar" ?>">
						<input type="hidden" value="<?= $row["productID"] ?>" name="itemToBuy"></input>
					<?php
					}
					?>
				</form>
			</figure>
		<?php
		}
		?>
		<h2>space</h2>
		<h2>space</h2>
	</main>
	<footer>
		<p> LPEM - HTSTA Final Project 2024 </p>
	</footer>
</body>

</html>