<?php
include_once("CommonCode.php");
includeCSS("Products.css");

NavigationBar("Products");
?>

<div style="text-align: center; margin: 40px 0;">
    <div class="header-banner">
        <h2 class="shop-title"><?= ($language === "EN") ? "MAGICAL PRODUCTS" : "PRODUTOS MÁGICOS" ?></h2>
        <div class="title-underline"></div> 
    </div>
</div>

<?php
$sql = "SELECT * FROM products";
$result = $connection->query($sql);

echo "<div class='product-container'>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $image = $row['ImageLink'];
        $rarity = $row['Rarity'] ?? 'Common';
        $rarityClass = strtolower($rarity);

        $name = ($language === "EN") ? $row['ProductNameEN'] : $row['ProductNamePT'];
        $desc = ($language === "EN") ? $row['DescriptionEN'] : $row['DescriptionPT'];
        $effect = ($language === "EN") ? $row['EffectEN'] : $row['EffectPT'];
?>

        <div class="product-card rarity-<?= $rarityClass ?>">
            <div class="rarity-badge"><?= strtoupper($rarity) ?></div>

            <div class="image-section">
                <img src="Pictures/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($name) ?>">
            </div>

            <div class="info-section">
                <h3><?= htmlspecialchars($name) ?></h3>
                <p class="price"><?= number_format($row['Price'], 2) ?> EUR</p>
                <p class="description"><?= htmlspecialchars($desc) ?></p>

                <div class="effect-box">
                    <span class="effect-label">EFFECT:</span> <?= htmlspecialchars($effect) ?>
                </div>
            </div>

            <?php if (isset($_SESSION["UserType"]) && $_SESSION["UserType"] !== "Admin") : ?>
                <form method="POST" action="ShopCartContents.php" class="buy-form">
                    <input type="hidden" name="product_id" value="<?= $row['ProductID'] ?>">

                    <div class="qty-wrapper" style="margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <span style="font-family: 'VT323', monospace; color: #fff; font-size: 1.2rem;">QTY:</span>
                        <input type="number" name="quantity" value="1" min="1" max="99"
                            style="width: 60px; background: rgba(160, 100, 255, 0.2); border: 2px solid #a064ff; border-radius: 8px; color: #fff; text-align: center; font-family: 'VT323', monospace; font-size: 1.2rem; padding: 5px;">
                    </div>

                    <button type="submit" class="buy-btn"><?= ($language === "EN") ? "ADD TO CART" : "ADICIONAR AO CARRINHO" ?></button>
                </form>
            <?php else : ?>
                <div class="admin-lock">
                    <p>VIEWING AS ADMIN</p>
                    <span>PURCHASE DISABLED</span>
                </div>
            <?php endif; ?>
        </div>

<?php
    }
} else {
    echo "<p class='empty-msg'>The shop is currently empty.</p>";
}

echo "</div>";
?>