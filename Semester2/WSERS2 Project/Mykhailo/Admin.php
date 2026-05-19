<?php

include_once("function.php");


if (!isset($_SESSION['logged_in_user']) || $_SESSION['user_is_admin'] !== true) {
    
    header("Location: index.php");
    exit;
}


$uploadDir    = __DIR__ . "/pictures/";

$errors = [];
$success = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $price === '' || $description === '') {
        $errors[] = $arrayOfTranslations['AllFieldsRequired'][$language] ?? 'All fields are required.';
    }

    if (!is_numeric($price)) {
        $errors[] = $arrayOfTranslations['PriceNumeric'][$language] ?? 'Price must be numeric.';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = $arrayOfTranslations['ImageUploadFailed'][$language] ?? 'Image upload failed.';
    }

    if (empty($errors)) {

        $tmp  = $_FILES['image']['tmp_name'];
        $info = getimagesize($tmp);

        if ($info === false) {
            $errors[] = $arrayOfTranslations['InvalidImage'][$language] ?? 'Uploaded file is not a valid image.';
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $errors[] = $arrayOfTranslations['InvalidImageType'][$language] ?? 'Only JPG, PNG or WEBP images allowed.';
            } else {

                $filename = time() . "_" . uniqid() . "." . $ext;
                $target   = $uploadDir . $filename;

                if (!move_uploaded_file($tmp, $target)) {
                    $errors[] = $arrayOfTranslations['CouldNotSaveImage'][$language] ?? 'Could not save image.';
                } else {

                        
                        $db = getDB();
                        $stmt = $db->prepare("INSERT INTO Products (productName, productPicture, price, description) VALUES (?, ?, ?, ?)");
                        if ($stmt) {
                            $picPath = "pictures/" . $filename;
                            $priceFormatted = number_format($price, 2, '.', '');
                            $stmt->bind_param('ssds', $name, $picPath, $priceFormatted, $description);
                            if ($stmt->execute()) {
                                $success = $arrayOfTranslations['ProductAdded'][$language] ?? 'Product successfully added.';
                            } else {
                                $errors[] = ($arrayOfTranslations['DatabaseInsertFailed'][$language] ?? 'Database insert failed: ') . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            $errors[] = $arrayOfTranslations['DatabasePrepareFailed'][$language] ?? 'Database error: could not prepare statement.';
                        }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $arrayOfTranslations['AdminPanel'][$language] ?? 'Admin Panel' ?></title>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>


<?php NavigationBar($page="Admin"); ?>


<div class="admin-panel">
    <h2><?= $arrayOfTranslations['CreateProduct'][$language] ?? 'Create Product' ?></h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <input type="text" name="name" placeholder="<?= $arrayOfTranslations['ProductName'][$language] ?? 'Product name' ?>" required>

        <input type="number" step="0.01" name="price" placeholder="<?= $arrayOfTranslations['price'][$language] ?? 'Price' ?>" required>

        <textarea name="description" placeholder="<?= $arrayOfTranslations['Description'][$language] ?? 'Description' ?>" required></textarea>

        <input type="file" name="image" accept="image/*" required>

        <button type="submit"><?= $arrayOfTranslations['AddProduct'][$language] ?? 'Add Product' ?></button>

    </form>
</div>

<footer>
    <?= htmlspecialchars($arrayOfTranslations['AdminFooter'][$language] ?? '© OrangeShop — Admin Panel') ?>
</footer>

</body>
</html>
