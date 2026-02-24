<?php

include_once("function.php");


if (!isset($_SESSION['logged_in_user']) || $_SESSION['user_is_admin'] !== true) {
    
    header("Location: index.php");
    exit;
}


$productsFile = __DIR__ . "/Products.csv";
$uploadDir    = __DIR__ . "/pictures/";

$errors = [];
$success = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $price === '' || $description === '') {
        $errors[] = "All fields are required.";
    }

    if (!is_numeric($price)) {
        $errors[] = "Price must be numeric.";
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Image upload failed.";
    }

    if (empty($errors)) {

        $tmp  = $_FILES['image']['tmp_name'];
        $info = getimagesize($tmp);

        if ($info === false) {
            $errors[] = "Uploaded file is not a valid image.";
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $errors[] = "Only JPG, PNG or WEBP images allowed.";
            } else {

                $filename = time() . "_" . uniqid() . "." . $ext;
                $target   = $uploadDir . $filename;

                if (!move_uploaded_file($tmp, $target)) {
                    $errors[] = "Could not save image.";
                } else {

                    $row = [
                        $name,
                        "pictures/" . $filename,
                        number_format($price, 2, '.', ''),
                        $description
                    ];

                    $fp = fopen($productsFile, 'a');
                    fputcsv($fp, $row, ';');
                    fclose($fp);

                    $success = "Product successfully added.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


<?php NavigationBar($page="Admin"); ?>


<div class="admin-panel">
    <h2>Create Product</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <input type="text" name="name" placeholder="Product name" required>

        <input type="number" step="0.01" name="price" placeholder="Price" required>

        <textarea name="description" placeholder="Description" required></textarea>

        <input type="file" name="image" accept="image/*" required>

        <button type="submit">Add Product</button>

    </form>
</div>

<footer>
    © OrangeShop — Admin Panel
</footer>

</body>
</html>
