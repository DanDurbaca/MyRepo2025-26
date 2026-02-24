<?php
include_once("CommonCode.php");
includeCSS("Admin.css");

//Check if user is Admin
if (!$_SESSION["UserLogged"] || $_SESSION["UserType"] !== "Admin") {
    echo "<p style='text-align:center; margin-top:50px; color:red;'>Access denied. Admins only.</p>";
    echo "<p style='text-align:center;'><a href='Home.php?lang=$language'>Return Home</a></p>";
    exit;
}

NavigationBar($arrayOfTranslations["AdminBtn"] ?? "Admin Panel");

$message = "";
$imageLink = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productNameEN = trim($_POST["productNameEN"]);
    $productNamePT = trim($_POST["productNamePT"]);
    $price = trim($_POST["price"]);
    $descriptionEN = trim($_POST["descriptionEN"]);
    $effectEN = trim($_POST["effectEN"]);
    $descriptionPT = trim($_POST["descriptionPT"]);
    $effectPT = trim($_POST["effectPT"]);

    //File upload handling
    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
        $maxSize = 5 * 1024 * 1024; //5MB

        $uploadDir = __DIR__ . '/Pictures'; //Use existing folder

        $fileTmp = $_FILES['imageFile']['tmp_name'];
        $fileType = mime_content_type($fileTmp);

        if (!array_key_exists($fileType, $allowedTypes)) {
            $message = "<div class='error'>Invalid file type. Only PNG and JPEG allowed.</div>";
        } elseif (filesize($fileTmp) > $maxSize) {
            $message = "<div class='error'>File too large. Max 5MB allowed.</div>";
        } else {
            $ext = $allowedTypes[$fileType];
            $newName = preg_replace("/[^a-zA-Z0-9_-]/", "", pathinfo($_FILES['imageFile']['name'], PATHINFO_FILENAME)) . "." . $ext;
            $destination = $uploadDir . '/' . $newName;

            if (move_uploaded_file($fileTmp, $destination)) {
                $imageLink = "$newName";
            } else {
                $message = "<div class='error'>Error saving the uploaded file.</div>";
            }
        }
    }

    //Save to CSV if all fields are valid
    if ($productNameEN && $productNamePT && $price && $imageLink && $message === "") {
        $fileHandler = fopen("Products.csv", "a");
        fwrite($fileHandler, "\n$productNameEN;$imageLink;$price;$descriptionEN;$effectEN;$descriptionPT;$effectPT;$productNamePT");
        fclose($fileHandler);
        $message = "<div class='success'>Product successfully added!</div>";
    } elseif ($message === "") {
        $message = "<div class='error'>Please fill in all required fields.</div>";
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
    <div class="register" style="max-width:600px; margin:50px auto;">
        <h2>Admin Panel - Create Product</h2>

        <?= $message ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Product Name (EN)</label>
            <input type="text" name="productNameEN" required>

            <label>Product Name (PT)</label>
            <input type="text" name="productNamePT" required>

            <label>Price (EUR)</label>
            <input type="number" name="price" step="0.01" required>

            <label>Description (EN)</label>
            <input type="text" name="descriptionEN">

            <label>Effect (EN)</label>
            <input type="text" name="effectEN">

            <label>Description (PT)</label>
            <input type="text" name="descriptionPT">

            <label>Effect (PT)</label>
            <input type="text" name="effectPT">

            <label>Product Image (PNG or JPEG, max 5MB)</label>
            <input type="file" name="imageFile" accept="image/png, image/jpeg" required>

            <button type="submit">Add Product</button>
        </form>
    </div>
</body>

</html>