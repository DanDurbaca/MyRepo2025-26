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
    <main class="admin">
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
                $sqlQuery=$connection->prepare("insert into Products(ProductNameEN, ImageLink, PageLink, Price, DescriptionEN, DescriptionRU, ProductNameRU) values(?, ?, ?, ?, ?, ?, ?)");
                $img=basename($_FILES['file']['name']);
                $sqlQuery->bind_param("sssisss", $_POST["enName"], $img, $_POST["page"], $_POST["price"], $_POST["enDescr"], $_POST["ruDescr"], $_POST["ruName"]);
                $sqlQuery->execute();
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
        <br>
        <br>
        <h1><?= $arrayOfTranslations["AdminH2"] ?></h1>
        <table class="cart">
            <tr>
                <th><?= $arrayOfTranslations["AdminLabel9"] ?></th>
                <th><?= $arrayOfTranslations["AdminLabel10"] ?></th>
                <th><?= $arrayOfTranslations["AdminLabel11"] ?></th>
                <th><?= $arrayOfTranslations["AdminLabel12"] ?></th>
            </tr>
		<?php 
        $sqlQuery=$connection -> prepare("select o.orderid, c.username, o.statusEN, o.statusRU from orders o join clients c on o.username=c.username order by o.orderid desc");
		$sqlQuery->execute();
		$result=$sqlQuery->get_result();
		while ($row=$result->fetch_assoc()) {
            ?>
            <tr>
                <td><?= $row["orderid"]?></td>
                <td><?= $row["username"]?></td>
                <td><table><?php 
                $sqlSubQuery=$connection -> prepare("select p.ProductNameEN, p.ProductNameRU, bi.quantity from orders o join boughtitem bi on o.orderid=bi.orderid join products p on bi.productid=p.productid where o.orderid=?");
                $sqlSubQuery->bind_param("i", $row["orderid"]);
                $sqlSubQuery->execute();
                $subResult=$sqlSubQuery->get_result();
                while ($subRow=$subResult->fetch_assoc()){
                ?>
                <tr>
                    <td><?= $subRow[($language == "EN") ? "ProductNameEN" : "ProductNameRU"] ?></td>
                    <td><?= $subRow["quantity"] ?></td>
                </tr>
                <?php
                }
                ?></table></td>
                <td><?= $row[($language == "EN") ? "statusEN" : "statusRU"] ?></td>
                <?php if($row["statusEN"]!="Delivered"){ ?>
                <td><form method="POST"><input type="hidden" value="<?= $row["orderid"] ?>" name="orderToSend"></input><input type="submit" value="<?= $arrayOfTranslations["AdminLabel13"] ?>"></form></td>
                <?php } ?>
            </tr>
            <?php 
        }
        ?>
        </table>
    </main>
    
</body>
</html>