<?php
include_once("function.php");
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'en') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= tr('products_title') ?></title>
<link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>

<?php NavigationBar($page="Products"); ?>

<section class="hero">
    <h1 class="site-heading"><?= tr('Welcome') ?></h1>
    <p><?= tr('Quality') ?></p>
    <a href="products.php?language=<?= $language ?>" class="btn">
        <?= tr('ShopNow') ?>
    </a>
</section>

<section class="products">
    <h2><?= tr('FeaturedProducts') ?></h2>
    <div class="product-grid">

    <?php
    
    $db = getDB();
    $stmt = $db->prepare("SELECT productId, productName, productPicture, price, description FROM Products");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($product = $res->fetch_assoc()) {
            ?>
            <div class="product-card" data-sticker="">
                <div class="card-media">
                    <img src="<?= htmlspecialchars($product['productPicture']) ?>" alt="<?= htmlspecialchars($product['productName']) ?>">
                </div>

                <div class="card-body">
                    <h3><?= htmlspecialchars($product['productName']) ?></h3>
                    <p><?= htmlspecialchars($product['description']) ?></p>

                    <?php if (isset($_SESSION['logged_in_user']) && empty($_SESSION['user_is_admin'])): ?>
                        <form method="POST">
                            <input type="hidden" name="productId" value="<?= intval($product['productId']) ?>">
                            <input type="hidden" name="add_to_cart" value="1">

                            <div class="meta">
                                <div class="price"><?= tr('price') ?>: <?= htmlspecialchars($product['price']) ?></div>

                                <div class="quantity">
                                    <label for="q-<?= intval($product['productId']) ?>" class="sr-only"><?= tr('Quantity') ?></label>
                                    <input id="q-<?= intval($product['productId']) ?>" type="number" name="quantity" value="1" min="1">
                                </div>
                            </div>

                            <div class="actions">
                                <button type="submit" class="buy-button"><?= tr('AddToCart') ?></button>
                            </div>
                        </form>
                    <?php elseif (!isset($_SESSION['logged_in_user'])): ?>
                        <div class="login-prompt"><?= tr('LoginToAdd') ?> <a href="login.php?language=<?= $language ?>"><?= tr('Login') ?></a></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        $stmt->close();
    } else {
        echo "<p>" . ($arrayOfTranslations['ProductsLoadError'][$language] ?? 'Could not load products.') . "</p>";
    }
    ?>
    </div>
</section>

<footer>
<p>&copy; 2025 OrangeShop. <?= $arrayOfTranslations['Rights'][$language] ?? 'All rights reserved.' ?></p>
</footer>

</body>
</html>
