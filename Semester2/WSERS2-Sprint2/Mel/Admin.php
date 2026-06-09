<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order & Product Manager</title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>
<body>

<header>
    <img src="pictures/Logo.png" alt="Logo">
    <h1>CS:GO Case Shop</h1>
</header>

<?php
/** @var array $arrayOfTranslations */
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

// ORDER APPROVAL HANDLING (Updates database status to 'delivered')
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["approve_order_id"])) {
    $orderIdToApprove = intval($_POST["approve_order_id"]);
    
    $sqlUpdateOrder = $connection->prepare("UPDATE Orders SET status = 'delivered' WHERE id = ? AND status = 'pending'");
    $sqlUpdateOrder->bind_param("i", $orderIdToApprove);
    
    if ($sqlUpdateOrder->execute()) {
        $message = "<p style='color:lightgreen;'>Order #$orderIdToApprove has been successfully updated to Delivered!</p>";
    } else {
        $message = "<p style='color:red;'>Failed to update order status in the database.</p>";
    }
}

// PRODUCT CREATION HANDLING
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["approve_order_id"])) {

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
            $sqlInsert = $connection->prepare("insert into Products(ProductNameEN,ProductNameGE,ImageLink,Price,DescriptionEN,DescriptionGE) VALUES(?,?,?,?,?,?)");
            $sqlInsert->bind_param("ssssss",$productNameEN,$productNameGE,$imageName,$price,$descriptionEN,$descriptionGE);
            $sqlInsert-> execute();

            $message = "<p style='color:lightgreen;'>" . $arrayOfTranslations["Admin3"] . " </p>";
        } else {
            $message = "<p style='color:red;'>" . $arrayOfTranslations["Admin4"] . "</p>";
        }
    }
}

// SHOW ACTION MESSAGES
if (!empty($message)) {
    echo "<div class='container'>$message</div>";
}
?>

<div class="container">

    <h2><?= $arrayOfTranslations["Admin18"] ?></h2>
    <?php
    $sqlPending = $connection->prepare("SELECT * FROM Orders WHERE status = 'pending'");
    $sqlPending->execute();
    $pendingResult = $sqlPending->get_result();

    if ($pendingResult->num_rows > 0) {
        ?>
        <table border="1" style="width:100%; border-collapse: collapse; margin-bottom: 30px; text-align: left;">
            <thead>
                <tr style="background-color: #d97706; color: white;">
                    <th style="padding: 10px;"><?= $arrayOfTranslations["Admin14"] ?></th>
                    <th style="padding: 10px;"><?= $arrayOfTranslations["Admin15"] ?></th>
                    <th style="padding: 10px;"><?= $arrayOfTranslations["Admin16"] ?></th>
                    <th style="padding: 10px;"><?= $arrayOfTranslations["Admin17"] ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $pendingResult->fetch_assoc()) { ?>
                    <tr>
                        <td style="padding: 10px;"><?= $order["id"] ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($order["username"]) ?></td>
                        <td style="padding: 10px;"><span style="color: #d97706; font-weight: bold;"><?= $order["status"] ?></span></td>
                        <td style="padding: 10px;">
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="approve_order_id" value="<?= $order["id"] ?>">
                                <button type="submit" style="background-color: #16a34a; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 4px;"><?= $arrayOfTranslations["Admin19"] ?></button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<p style='color: gray; margin-bottom: 30px;'>" . $arrayOfTranslations["Admin21"] . "</p>";
    }
    ?>


    <h2><?= $arrayOfTranslations["Admin13"] ?></h2>
    <?php
    $sqlDelivered = $connection->prepare("SELECT * FROM Orders WHERE status = 'delivered'");
    $sqlDelivered->execute();
    $deliveredResult = $sqlDelivered->get_result();

    if ($deliveredResult->num_rows > 0) {
        ?>
        <table border="1" style="width:100%; border-collapse: collapse; margin-bottom: 40px; text-align: left;">
            <thead>
                <tr style="background-color: #1e3a8a; color: white;">
                    <th style="padding: 10px;"><?= $arrayOfTranslations["Admin14"] ?></th>
                    <th style="padding: 10px;"><?= $arrayOfTranslations["Admin15"] ?></th>
                    <th style="padding: 10px;"><?= $arrayOfTranslations["Admin16"] ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $deliveredResult->fetch_assoc()) { ?>
                    <tr>
                        <td style="padding: 10px;"><?= $order["id"] ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($order["username"]) ?></td>
                        <td style="padding: 10px;"><span style="color: #16a34a; font-weight: bold;"><?= $order["status"] ?></span></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<p style='color: gray; margin-bottom: 40px;'>" . $arrayOfTranslations["Admin20"] . "</p>";
    }
    ?>

    <hr style="border: 0; height: 1px; background: #ccc; margin-bottom: 40px;">

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