<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Admin");

    if (!isset($_SESSION['userType']) || $_SESSION['userType'] != "administrator") {
        header("Location: Home.php");
        exit();
    }
    ?>

    <h1>Admin Page - Add Product</h1>

    <?php
    $bShowForm = true;

    if (isset($_POST["productNameEn"], $_POST["productNameGr"], $_POST["price"])) {
        $bShowForm = false;
        print("Adding product...<br>");

        $imageLink = "";

        if (isset($_FILES["productImage"]) && $_FILES["productImage"]["error"] == 0) {
            $extension = strtolower(pathinfo($_FILES["productImage"]["name"], PATHINFO_EXTENSION));
            $allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];

            if (!in_array($extension, $allowedExtensions)) {
                $bShowForm = true;
                print("Error: only image files (jpg, jpeg, png, gif, webp) are allowed.");
            } else {
                $newName = substr($_POST["productNameEn"], 0, 10) . "." . $extension;
                $uploadPath = "Images/" . $newName;

                move_uploaded_file($_FILES["productImage"]["tmp_name"], $uploadPath);

                $imageLink = "Images/" . $newName;
            }
        }

        $sqlInsertProduct = $connection->prepare("INSERT INTO products (product_name_en, product_name_gr, description_en, description_gr, price, image_link) VALUES (?,?,?,?,?,?)");
        $sqlInsertProduct->bind_param("sssdss", $_POST["productNameEn"], $_POST["productNameGr"], $_POST["descriptionEn"], $_POST["descriptionGr"], $_POST["price"], $imageLink);
        $sqlInsertProduct->execute();

        print("Product added successfully!");
        print("<br><a href='Admin.php'>Add another product</a>");
    }

    if ($bShowForm) {
        ?>
        <form method="POST" enctype="multipart/form-data">
            <div>Product name (English):</div>
            <input type="text" name="productNameEn">

            <div>Product name (Greek):</div>
            <input type="text" name="productNameGr">

            <div>Description (English):</div>
            <input type="text" name="descriptionEn">

            <div>Description (Greek):</div>
            <input type="text" name="descriptionGr">

            <div>Price:</div>
            <input type="text" name="price">

            <div>Product image:</div>
            <input type="file" name="productImage">

            <br><input type="submit" value="Add Product">
        </form>
        <?php
    }
    ?>

</body>
</html>