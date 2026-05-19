<?php
include_once("CommonCode.php");
includeCSS("Admin.css");

// Check if user is Admin
if (!isset($_SESSION["UserLogged"]) || !$_SESSION["UserLogged"] || $_SESSION["UserType"] !== "Admin") {
    echo "<p style='text-align:center; margin-top:50px; color:red;'>" . ($arrayOfTranslations["AdminErrAccess"] ?? "Access denied. Admins only.") . "</p>";
    echo "<p style='text-align:center;'><a href='Home.php?lang=$language'>" . ($arrayOfTranslations["CartReturnBtn"] ?? "Return Home") . "</a></p>";
    exit;
}

NavigationBar("Admin");

$message = "";
$imageLink = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productNameEN = trim($_POST["productNameEN"]);
    $productNamePT = trim($_POST["productNamePT"]);
    $price         = trim($_POST["price"]);
    $descriptionEN = trim($_POST["descriptionEN"]);
    $effectEN      = trim($_POST["effectEN"]);
    $descriptionPT = trim($_POST["descriptionPT"]);
    $effectPT      = trim($_POST["effectPT"]);
    $rarity        = $_POST["rarity"];

    // File upload logic
    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/Pictures';
        $fileTmp   = $_FILES['imageFile']['tmp_name'];
        $ext       = pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION);

        $newName   = preg_replace("/[^a-zA-Z0-9_-]/", "", pathinfo($_FILES['imageFile']['name'], PATHINFO_FILENAME)) . "." . $ext;
        $destination = $uploadDir . '/' . $newName;

        if (move_uploaded_file($fileTmp, $destination)) {
            $imageLink = $newName;
        } else {
            $message = "<div class='error' style='color: #ff4b2b; text-align: center; margin-bottom: 10px;'>Error saving file to Pictures folder.</div>";
        }
    }

    if ($productNameEN && $productNamePT && $price && $imageLink && $message === "") {
        $sqlInsert = $connection->prepare("INSERT INTO products (ProductNameEN, ProductNamePT, Price, DescriptionEN, EffectEN, DescriptionPT, EffectPT, Imagelink, Rarity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($sqlInsert) {
            $sqlInsert->bind_param(
                "ssdssssss",
                $productNameEN,
                $productNamePT,
                $price,
                $descriptionEN,
                $effectEN,
                $descriptionPT,
                $effectPT,
                $imageLink,
                $rarity
            );

            if ($sqlInsert->execute()) {
                $message = "<div class='success' style='color: #4caf50; text-align: center; margin-bottom: 10px;'>Success! Item added as " . htmlspecialchars($rarity) . ".</div>";
            } else {
                $message = "<div class='error' style='color: #ff4b2b; text-align: center; margin-bottom: 10px;'>DB Error: " . $sqlInsert->error . "</div>";
            }
            $sqlInsert->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel | Pixel Potion Shop</title>
</head>

<body>
    <div class="register" style="max-width:650px; margin:50px auto; background: rgba(26, 15, 46, 0.9); padding: 40px; border-radius: 15px; border: 1px solid #a064ff; color: white; font-family: 'VT323', monospace; text-align: center;">


        <div class="header-banner">
            <h2 class="page-main-title"><?= $arrayOfTranslations["AdminTitle"] ?? "ADMIN PANEL - CREATE PRODUCT" ?></h2>
            <hr class="title-underline">
        </div>

        <?= $message ?>

        <form method="POST" enctype="multipart/form-data" style="text-align: left;">
            <label>Product Name (EN)</label>
            <input type="text" name="productNameEN" required style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Product Name (PT)</label>
            <input type="text" name="productNamePT" required style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label>Price (EUR)</label>
                    <input type="number" name="price" step="0.01" required style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">
                </div>
                <div style="flex: 1;">
                    <label>Rarity Tier</label>
                    <select name="rarity" class="rarity-selector" style="width: 100%; padding: 8px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white;">
                        <option value="Common">Common</option>
                        <option value="Rare" style="color: #3498db;">Rare</option>
                        <option value="Epic" style="color: #9b59b6;">Epic</option>
                        <option value="Legendary" style="color: #f1c40f;">Legendary</option>
                    </select>
                </div>
            </div>

            <label>Description (EN)</label>
            <input type="text" name="descriptionEN" style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Effect (EN)</label>
            <input type="text" name="effectEN" style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Description (PT)</label>
            <input type="text" name="descriptionPT" style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Effect (PT)</label>
            <input type="text" name="effectPT" style="width: 100%; margin-bottom: 15px; background: rgba(0,0,0,0.3); border: 1px solid #a064ff; color: white; padding: 8px;">

            <label>Product Image</label>
            <input type="file" name="imageFile" accept="image/png, image/jpeg" required style="width: 100%; margin-bottom: 15px; color: white;">

            <button type="submit" style="margin-top: 20px; width: 100%; background: #4caf50; color: white; border: none; padding: 15px; border-radius: 5px; cursor: pointer; font-family: 'Press Start 2P', cursive; font-size: 10px;">
                Add Magical Item
            </button>
        </form>
    </div>

    <script>
        const selector = document.querySelector('.rarity-selector');
        selector.addEventListener('change', function() {
            if (this.value === 'Rare') this.style.color = '#3498db';
            else if (this.value === 'Epic') this.style.color = '#9b59b6';
            else if (this.value === 'Legendary') this.style.color = '#f1c40f';
            else this.style.color = 'white';
        });
    </script>
</body>

</html>