<?php
// admin.php
include_once("nav.php");

// ✅ Only allow logged-in admins
if (empty($_SESSION["UserLogged"]) || empty($_SESSION["IsAdmin"]) || $_SESSION["IsAdmin"] !== true) {
    // you can also show a nicer error message instead
    header("Location: index.php?lang=" . $language);
    exit;
}

// ===== File upload constants =====
const ALLOWED_FILES = [
    'image/png'  => 'png',
    'image/jpeg' => 'jpg',
    'image/jpg'  => 'jpg',
    'image/webp' => 'webp'
];

const MAX_SIZE = 5 * 1024 * 1024; // 5MB
// IMPORTANT: products.php expects images inside ./WebsiteImages/
const UPLOAD_DIR = __DIR__ . '/WebsiteImages';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Get product fields ---
    $nameEN   = trim($_POST['name_en'] ?? '');
    $nameDE   = trim($_POST['name_de'] ?? '');
    $price    = trim($_POST['price'] ?? '');
    $descEN   = trim($_POST['desc_en'] ?? '');
    $descDE   = trim($_POST['desc_de'] ?? '');

    // Basic validation
    if ($nameEN === "" || $nameDE === "" || $price === "" || !is_numeric($price) || $descEN === "" || $descDE === "") {
        $message = "Please fill in all fields and use a numeric price.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $message = "Please select an image to upload.";
    } else {

        $file = $_FILES['image'];

        // Size check
        if (filesize($file['tmp_name']) > MAX_SIZE) {
            $message = "File too large. Max 5MB.";
        } else {
            // Mime type check
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!isset(ALLOWED_FILES[$mime_type])) {
                $message = "Invalid file type. Only PNG / JPG / WEBP are allowed.";
            } else {
                // Safe file name
                $safeBase = preg_replace('/[^a-zA-Z0-9-_]/', '_', strtolower($nameEN));
                $ext      = ALLOWED_FILES[$mime_type];
                $newName  = $safeBase . "_" . time() . "." . $ext;

                // Ensure folder exists
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }

                $destination = UPLOAD_DIR . '/' . $newName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // ✅ Save to Products.csv
                    // Current structure in products.php:
                    // [0]=NameEN; [1]=ImageFile; [2]=Price; [3]=DescEN; [4]=DescDE; [5]=NameDE
                    $fh = fopen("Products.csv", "a");

                    if ($fh) {
                        $line = "\n" . $nameEN . ";" . $newName . ";" . $price . ";" . $descEN . ";" . $descDE . ";" . $nameDE;
                        fwrite($fh, $line);
                        fclose($fh);
                        $message = "Product created successfully 🎉";
                    } else {
                        $message = "Could not open Products.csv for writing.";
                    }
                } else {
                    $message = "Error saving uploaded file.";
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
    <title>Admin</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>
<body>
    <?php NavigationBar(($arrayOfTranslations["AdminBtn"] ?? "Admin")); ?>

    <header>
        <h1><?= $arrayOfTranslations["AdminTitle"] ?? "Admin – Create Product"; ?></h1>
        <h2><?= $arrayOfTranslations["AdminSubTitle"] ?? "Add new products for the shop"; ?></h2>
    </header>

    <main>
        <?php if ($message !== ""): ?>
            <section>
                <h3><?= htmlspecialchars($message); ?></h3>
            </section>
        <?php endif; ?>

        <section>
            <h3><?= $arrayOfTranslations["AdminCreateProduct"] ?? "Create a new product"; ?></h3>

            <!-- IMPORTANT: enctype for file uploads -->
            <form method="POST" enctype="multipart/form-data">
                <div>
                    <label>Product name (EN):</label><br>
                    <input type="text" name="name_en" required>
                </div>
                <br>

                <div>
                    <label>Product name (DE):</label><br>
                    <input type="text" name="name_de" required>
                </div>
                <br>

                <div>
                    <label>Price (EUR per g):</label><br>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <br>

                <div>
                    <label>Description (EN):</label><br>
                    <textarea name="desc_en" rows="3" cols="40" required></textarea>
                </div>
                <br>

                <div>
                    <label>Description (DE):</label><br>
                    <textarea name="desc_de" rows="3" cols="40" required></textarea>
                </div>
                <br>

                <div>
                    <label>Product image:</label><br>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <br>

                <button type="submit" class="logout-nav">
                    <?= $arrayOfTranslations["AdminCreateBtn"] ?? "Create product"; ?>
                </button>
            </form>
        </section>
    </main>
</body>
</html>
