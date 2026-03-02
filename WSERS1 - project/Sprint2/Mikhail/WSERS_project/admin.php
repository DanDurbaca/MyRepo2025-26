<?php
include_once("common.php");
if ($_SESSION["IsAdmin"] == false) {
    header("Refresh:0; url=home.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Best Holiday Destinations</title>
</head>

<body>
    <?php head("Admin"); ?>
    <main class="register">
        <h1><?= $arrayOfTranslations["AdminH1"] ?></h1>
        <br>
        <?php
        const ALLOWED_FILES = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
        const MAX_SIZE = 5 * 1024 * 1024; // 5MB
        const UPLOAD_DIR = __DIR__ . '\images';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["enName"], $_POST["ruName"], $_FILES['file'], $_POST["price"], $_POST["enDescr"], $_POST["ruDescr"])) {
            if ($_POST["enName"] == null || $_POST["ruName"] == null || $_FILES['file'] == null || $_POST["price"] == null || $_POST["enDescr"] == null || $_POST["ruDescr"] == null) {
                print($arrayOfTranslations["AdminOut1"]);
            } else {
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
                    echo "Product added successfully.";
                } else {
                    echo "Error saving file.";
                    exit();
                }
                $fHandler = fopen("Products.csv", "a");
                fwrite($fHandler, "\n" . $_POST["enName"] . ";" . basename($_FILES['file']['name']) . ";" . $_POST["page"] . ";" . $_POST["price"] . ";" . $_POST["enDescr"] . ";" . $_POST["ruDescr"] . ";" . $_POST["ruName"]);
                fclose($fHandler);
            }
        }

        ?>
        <form method="POST" enctype="multipart/form-data">
            <label><?= $arrayOfTranslations["AdminLabel1"] ?></label>
            <input type="text" name="enName">
            <br>
            <br>
            <label><?= $arrayOfTranslations["AdminLabel2"] ?></label>
            <input type="text" name="ruName">
            <br>
            <br>
            <label><?= $arrayOfTranslations["AdminLabel3"] ?></label>
            <input type="file" name="file" id="file">
            <br>
            <br>
            <label><?= $arrayOfTranslations["AdminLabel4"] ?></label>
            <input type="text" name="page">
            <br>
            <br>
            <label><?= $arrayOfTranslations["AdminLabel5"] ?></label>
            <input type="number" name="price">
            <br>
            <br>
            <label><?= $arrayOfTranslations["AdminLabel6"] ?></label>
            <input type="text" name="enDescr">
            <br>
            <br>
            <label><?= $arrayOfTranslations["AdminLabel7"] ?></label>
            <input type="text" name="ruDescr">
            <br>
            <br>
            <input type="submit" value="<?= $arrayOfTranslations["AdminLabel8"] ?>">
        </form>
    </main>
</body>

</html>