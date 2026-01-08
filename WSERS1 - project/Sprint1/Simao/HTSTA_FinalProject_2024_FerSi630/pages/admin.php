<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
    <title>Admin</title>
</head>

<body>

<?php
include_once("commonCode.php");
navBar("Admin");

if (!$_SESSION["UserLogged"] || empty($_SESSION["IsAdmin"]) || $_SESSION["IsAdmin"] !== true) {
    echo "<p class='error'>You do not have admin permissions.</p>";
    echo "<meta http-equiv='refresh' content='2; url=index.php?lang=$language'>";
    exit;
}

$placesFile = "places.csv";
$translationsFile = "Translation.csv";

function saveCSVLine($file, $line) {
    $f = fopen($file, "a");
    fputcsv($f, $line, ";");
    fclose($f);
}

function getCSVData($file) {
    $data = [];
    if (($f = fopen($file, "r")) !== false) {
        while (($line = fgetcsv($f, 0, ";")) !== false) {
            if (count($line) > 0) $data[] = $line;
        }
        fclose($f);
    }
    return $data;
}

if (isset($_POST["action"])) {
    if ($_POST["action"] === "add_place") {
        $country = $_POST["country"];
        $placeName = trim($_POST["place_name"]);
        $desc = trim($_POST["place_desc"]);
        $image = "";
        if (!empty($_FILES["place_image"]["name"])) {
            $imgName = basename($_FILES["place_image"]["name"]);
            $targetDir = "../images/";
            $targetFile = $targetDir . $imgName;
            move_uploaded_file($_FILES["place_image"]["tmp_name"], $targetFile);
            $image = "images/" . $imgName;
        }
        saveCSVLine($placesFile, [$country, $placeName, $placeName, $desc, $image]);
        echo "<p class='success'>Place added!</p>";
    } elseif ($_POST["action"] === "add_translation") {
        $translation = trim($_POST["translation"]);
        $parts = explode(";", $translation);
        if (count($parts) === 3) {
            saveCSVLine($translationsFile, $parts);
            echo "<p class='success'>Translation added!</p>";
        } else {
            echo "<p class='error'>Invalid translation format.</p>";
        }
    }
}

$places = getCSVData($placesFile);
?>

<div class="admin-section">
    <h2>Current Places</h2>
    <div class="table-wrapper">
        <table>
            <tr><th>Country</th><th>Place Name</th><th>Description</th><th>Image</th></tr>
            <?php foreach($places as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p[0]) ?></td>
                    <td><?= htmlspecialchars($p[1]) ?></td>
                    <td><?= htmlspecialchars($p[3]) ?></td>
                    <td>
                        <?php if(!empty($p[4])): ?>
                            <img src="<?= htmlspecialchars($p[4]) ?>" class="place-img" alt="<?= htmlspecialchars($p[1]) ?>">
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="admin-section">
    <h2>Add New Place</h2>
    <div class="form-wrapper">
        <form class="admin-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_place">
            <label>Country</label>
            <select name="country">
                <option value="Country1">Country 1</option>
                <option value="Country2">Country 2</option>
            </select>
            <label>Place Name</label>
            <input type="text" name="place_name" required>
            <label>Description</label>
            <input type="text" name="place_desc" required>
            <label>Image</label>
            <input type="file" name="place_image">
            <button class="btn-add" type="submit">Add Place</button>
        </form>
    </div>
</div>

<div class="admin-section">
    <h2>Add / Update Translation</h2>
    <div class="form-wrapper">
        <form class="admin-form" method="POST">
            <input type="hidden" name="action" value="add_translation">
            <label>Translation (format: Key;English;Portuguese)</label>
            <input type="text" name="translation" required>
            <button class="btn-add" type="submit">Add Translation</button>
        </form>
    </div>
</div>

</body>
</html>
