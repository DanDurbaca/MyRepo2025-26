<?php
include_once("function.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OrangeShop - Home</title>
<link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>

<?php NavigationBar($page="Products"); ?>

<section class="hero">
    <h1><?= $arrayOfTranslations['Welcome'][$language] ?? 'Welcome to OrangeShop' ?></h1>
    <p><?= $arrayOfTranslations['Quality'][$language] ?? 'Quality products delivered to your door.' ?></p>
    <a href="products.php?language=<?= $language ?>" class="btn">
        <?= $arrayOfTranslations['ShopNow'][$language] ?? 'Shop Now' ?>
    </a>
</section>

<section class="products">
    <h2><?= $arrayOfTranslations['FeaturedProducts'][$language] ?? 'Featured Products' ?></h2>
    <div class="product-grid">

    <?php
    // Load products from CSV file
    $fProducts = fopen("Products.csv", "r");
    $header = fgetcsv($fProducts, 0, ";");
    while (($row = fgetcsv($fProducts, 0, ";")) !== false) {
        if (count($row) == count($header)) {
            $product = array_combine($header, $row);
            ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($product['ProductPicture']) ?>"
                     alt="<?= htmlspecialchars($product['ProductName']) ?>">
                <h3><?= htmlspecialchars($product['ProductName']) ?></h3>
                <p><?= $arrayOfTranslations['Price'][$language] ?? 'Price' ?>:
                   $<?= htmlspecialchars($product['Price']) ?></p>
                <p><?= htmlspecialchars($product['Description']) ?></p>
                <button><?= $arrayOfTranslations['AddToCart'][$language] ?? 'Add to Cart' ?></button>
            </div>
            <?php
        }
    }
    fclose($fProducts);
    ?>
    </div>
</section>

<footer>
<p>&copy; 2025 OrangeShop. <?= $arrayOfTranslations['Rights'][$language] ?? 'All rights reserved.' ?></p>
</footer>

</body>
</html>
