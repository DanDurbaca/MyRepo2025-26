<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Product Manager</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>
<body>

<header>
    <img src="pictures/Logo.png" alt="Logo">
    <h1>CS:GO Case Shop</h1>
</header>

<?php
include_once("commoncode.php");
Melnav("Admin");

// ACCESS CONTROL
if ($_SESSION["UserRole"] !== "admin") {
    echo "<div class='container'>";
    echo "<h2 style='color:red;'>" . $arrayOfTranslations["Admin1"] . "</h2>";
    echo "<p>" . $arrayOfTranslations["Admin2"] . "</p>";
    echo "</div>";
    header("Refresh:3; url=Website.php");

    exit;
}

// MESSAGE HOLDER
$message = "";
?>

<div class="container">

<?php
// PRODUCT CREATION HANDLING
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["ProductNameEN"], $_POST["ProductNameGE"], $_POST["Price"], 
              $_POST["DescriptionEN"], $_POST["DescriptionGE"]) 
        && isset($_FILES["ProductImage"])) {

        $productNameEN = trim($_POST["ProductNameEN"]);
        $productNameGE = trim($_POST["ProductNameGE"]);
        $price = trim($_POST["Price"]);
        $descriptionEN = trim($_POST["DescriptionEN"]);
        $descriptionGE = trim($_POST["DescriptionGE"]);

        // IMAGE UPLOAD
        $imageName = basename($_FILES["ProductImage"]["name"]);
        $targetDir = "pictures/";
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["ProductImage"]["tmp_name"], $targetFile)) {

            // SAVE PRODUCT TO CSV
            $file = fopen("Products.csv", "a");
            fwrite($file, "\n$productNameEN;$productNameGE;$imageName;$price;$descriptionEN;$descriptionGE");
            fclose($file);

            $message = "<p style='color:lightgreen;'>" . $arrayOfTranslations["Admin3"] . " </p>";
        } else {
            $message = "<p style='color:red;'>" . $arrayOfTranslations["Admin4"] . "</p>";
        }
    }
}

// SHOW MESSAGE
echo $message;
?>

<h2><?= $arrayOfTranslations["Admin5"] ?></h2>

<form method="POST" enctype="multipart/form-data">

    <label><?= $arrayOfTranslations["Admin6"] ?></label><br>
    <input type="text" name="ProductNameEN" required><br><br>

    <label><?= $arrayOfTranslations["Admin7"] ?></label><br>
    <input type="text" name="ProductNameGE" required><br><br>

    <label><?= $arrayOfTranslations["Admin8"] ?></label><br>
    <input type="file" name="ProductImage" accept="image/*" required><br><br>

    <label><?= $arrayOfTranslations["Admin9"] ?></label><br>
    <input type="number" name="Price" step="0.01" required><br><br>

    <label><?= $arrayOfTranslations["Admin10"] ?></label><br>
    <textarea name="DescriptionEN" required></textarea><br><br>

    <label><?= $arrayOfTranslations["Admin11"] ?></label><br>
    <textarea name="DescriptionGE" required></textarea><br><br>

    <button type="submit"><?= $arrayOfTranslations["Admin12"] ?></button>
</form>

</div>

</body>
</html>