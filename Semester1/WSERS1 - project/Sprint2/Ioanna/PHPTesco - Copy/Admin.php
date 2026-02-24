<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
</head>

<body>

    <?php
    include_once("CommonCode.php");
    NavigationBar("Admin");

    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin_user') {
        header("Location: Home.php");
        exit();
    }
    ?>

    <form action="Admin.php" method="post" enctype="multipart/form-data">

        <label>Type new product name (EN):</label>
        <input type="text" name="product_name_en" id="ProductNameEN">
        <br />

        <label>Type new product name (GR):</label>
        <input type="text" name="product_name_gr" id="ProductNameGR">
        <br />


        <label>Type product description (EN):</label>
        <input type="text" name="product_description_en" id="product_description_en">
        <br />

        <label>Type product description (GR):</label>
        <input type="text" name="product_description_gr" id="product_description_gr">
        <br />

        <label>Type product price:</label>
        <input type="text" name="product_price" id="product_price">
        <br />

        <label>Select file:</label>
        <input type="file" name="file" id="file">
        <br />


        <button type="submit">Upload</button>




    </form>

    <?php

    const ALLOWED_FILES = ['image/png' => 'png', 'image/jpeg' => 'jpg'];

    const MAX_SIZE = 5 * 1024 * 1024; // 5MB
    
    const UPLOAD_DIR = __DIR__ . '/Images';


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

        $file = $_FILES['file'];


        if ($file['error'] !== UPLOAD_ERR_OK) {

            die("Upload error: " . $file['error']);

        }


        if (filesize($file['tmp_name']) > MAX_SIZE) {

            die("File too large.");

        }


        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mime_type = finfo_file($finfo, $file['tmp_name']);

        finfo_close($finfo);


        if (!isset(ALLOWED_FILES[$mime_type])) {

            die("Invalid file type.");

        }


        $newName = pathinfo($file['name'], PATHINFO_FILENAME) . '.' . ALLOWED_FILES[$mime_type];

        $destination = UPLOAD_DIR . '/' . $newName;


        if (move_uploaded_file($file['tmp_name'], $destination)) {

            // Collect form data safely
            $productNameEN = trim($_POST['product_name_en'] ?? '');
            $productNameGR = trim($_POST['product_name_gr'] ?? '');
            $descriptionEN = trim($_POST['product_description_en'] ?? '');
            $descriptionGR = trim($_POST['product_description_gr'] ?? '');
            $price = trim($_POST['product_price'] ?? '');

            // Basic validation
            if (
                $productNameEN === '' ||
                $productNameGR === '' ||
                $descriptionEN === '' ||
                $descriptionGR === '' ||
                !is_numeric($price)
            ) {
                die("Invalid product data.");
            }

            // CSV file path
            $csvFile = './Products.csv';

            // Open CSV in append mode
            $handle = fopen($csvFile, 'a');

            if ($handle === false) {
                die("Unable to open CSV file.");
            }

            // Write new row
            fputcsv(
                $handle,
                [
                    $productNameEN,
                    $newName, // image filename
                    $price,
                    $descriptionEN,
                    $productNameGR,
                    $descriptionGR,


                ],
                ';' // <-- semicolon separator
            );

            fclose($handle);

            echo "Product added successfully.";

        } else {

            echo "Error saving file.";

        }

    }

    ?>


</body>

</html>