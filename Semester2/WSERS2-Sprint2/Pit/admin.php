<?php
// admin.php
include_once("nav.php");

//  Only allow logged-in admins
if (empty($_SESSION["UserLogged"]) || empty($_SESSION["IsAdmin"]) || $_SESSION["IsAdmin"] !== true) {
    // you can also show a nicer error message instead
    header("Location: index.php?lang=" . $language);
    exit;
}

// File upload constants 
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

$orderMessage = "";

if (isset($_POST["update_order_status"])) {
    $orderId = intval($_POST["order_id"] ?? 0);
    $newStatus = $_POST["order_status"] ?? "pending";

    $allowedStatuses = ["pending", "allowed", "rejected", "completed"];

    if ($orderId > 0 && in_array($newStatus, $allowedStatuses)) {
        $sqlUpdateOrder = $connection->prepare(
            "UPDATE Orders SET OrderStatus = ? WHERE OrderID = ?"
        );
        $sqlUpdateOrder->bind_param("si", $newStatus, $orderId);

        if ($sqlUpdateOrder->execute()) {
            $orderMessage = $arrayOfTranslations["AdminOrderUpdated"] ?? "Order status updated.";
        } else {
            $orderMessage = $arrayOfTranslations["AdminOrderUpdateError"] ?? "Error updating order status.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["create_product"])) {

    //  Get product fields 
    $nameEN   = trim($_POST['name_en'] ?? '');
    $nameDE   = trim($_POST['name_de'] ?? '');
    $price    = trim($_POST['price'] ?? '');
    $descEN   = trim($_POST['desc_en'] ?? '');
    $descDE   = trim($_POST['desc_de'] ?? '');

    // Basic validation
    if ($nameEN === "" || $nameDE === "" || $price === "" || !is_numeric($price) || $descEN === "" || $descDE === "") {
        $message = $arrayOfTranslations["AdminMsgFillFields"] ?? "Please fill in all fields and use a numeric price.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $message = $arrayOfTranslations["AdminMsgSelectImage"] ?? "Please select an image to upload.";
    } else {

        $file = $_FILES['image'];

        // Size check
        if (filesize($file['tmp_name']) > MAX_SIZE) {
            $message = $arrayOfTranslations["AdminMsgFileLarge"] ?? "File too large. Max 5MB.";
        } else {
            // Mime type check
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!isset(ALLOWED_FILES[$mime_type])) {
                $message = $arrayOfTranslations["AdminMsgInvalidType"] ?? "Invalid file type. Only PNG / JPG / WEBP are allowed.";
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
                    $connection = new mysqli("localhost", "root", "", "4PageWebsite");

                    $sqlInsert = $connection->prepare(
                        "INSERT INTO Products (ProductNameEN, ImageLink, Price, DescriptionEN, DescriptionDE, ProductNameDE)
                            VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    $sqlInsert->bind_param("ssdsss", $nameEN, $newName, $price, $descEN, $descDE, $nameDE);

                    if ($sqlInsert->execute()) {
                        $message = $arrayOfTranslations["AdminMsgCreated"] ?? "Product created successfully.";
                    } else {
                        $message = ($arrayOfTranslations["AdminMsgDbError"] ?? "Database error:") . " " . $connection->error;
                    }

                    $sqlInsert->close();
                } else {
                    $message = $arrayOfTranslations["AdminMsgSaveError"] ?? "Error saving uploaded file.";
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
    <title><?= $arrayOfTranslations["AdminBtn"] ?? "Admin" ?></title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <?php NavigationBar($arrayOfTranslations["AdminBtn"] ?? "Admin"); ?>

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
                    <label><?= $arrayOfTranslations["AdminNameEN"] ?? "Product name (EN):" ?></label><br>
                    <input type="text" name="name_en" required>
                </div>
                <br>

                <div>
                    <label><?= $arrayOfTranslations["AdminNameDE"] ?? "Product name (DE):" ?></label><br>
                    <input type="text" name="name_de" required>
                </div>
                <br>

                <div>
                    <label><?= $arrayOfTranslations["AdminPrice"] ?? "Price (EUR per g):" ?></label><br>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <br>

                <div>
                    <label><?= $arrayOfTranslations["AdminDescEN"] ?? "Description (EN):" ?></label><br>
                    <textarea name="desc_en" rows="3" cols="40" required></textarea>
                </div>
                <br>

                <div>
                    <label><?= $arrayOfTranslations["AdminDescDE"] ?? "Description (DE):" ?></label><br>
                    <textarea name="desc_de" rows="3" cols="40" required></textarea>
                </div>
                <br>

                <div>
                    <label><?= $arrayOfTranslations["AdminImage"] ?? "Product image:" ?></label><br>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <br>

                <button type="submit" name="create_product" class="logout-nav">
                    <?= htmlspecialchars($arrayOfTranslations["AdminCreateBtn"] ?? "Create product", ENT_QUOTES, 'UTF-8'); ?>
                </button>
            </form>
        </section>

        <section>
            <h3><?= htmlspecialchars($arrayOfTranslations["AdminCustomerOrders"] ?? "Customer Orders", ENT_QUOTES, 'UTF-8') ?></h3>

            <?php if ($orderMessage !== ""): ?>
                <h3><?= htmlspecialchars($orderMessage, ENT_QUOTES, 'UTF-8') ?></h3>
            <?php endif; ?>

            <?php
            $sqlOrders = $connection->prepare("SELECT * FROM Orders ORDER BY OrderDate DESC");
            $sqlOrders->execute();
            $ordersResult = $sqlOrders->get_result();

            if ($ordersResult->num_rows === 0) {
                echo "<p>" . htmlspecialchars($arrayOfTranslations["AdminNoOrders"] ?? "No orders yet.", ENT_QUOTES, 'UTF-8') . "</p>";
            } else {
                while ($order = $ordersResult->fetch_assoc()) {
            ?>
                    <div class="order-box">
                        <h3>
                            <?= htmlspecialchars($arrayOfTranslations["OrderNumber"] ?? "Order", ENT_QUOTES, 'UTF-8') ?>
                            #<?= (int)$order["OrderID"] ?>
                            <?= htmlspecialchars($arrayOfTranslations["OrderBy"] ?? "by", ENT_QUOTES, 'UTF-8') ?>
                            <?= htmlspecialchars($order["Username"], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($arrayOfTranslations["OrderDate"] ?? "Date", ENT_QUOTES, 'UTF-8') ?>:
                            <?= htmlspecialchars($order["OrderDate"], ENT_QUOTES, 'UTF-8') ?><br>

                            <?= htmlspecialchars($arrayOfTranslations["OrderTotal"] ?? "Total", ENT_QUOTES, 'UTF-8') ?>:
                            <?= number_format($order["TotalPrice"], 2) ?> EUR<br>

                            <?= htmlspecialchars($arrayOfTranslations["AdminCurrentStatus"] ?? "Current status", ENT_QUOTES, 'UTF-8') ?>:
                            <strong>
                                <?php
                                if ($order["OrderStatus"] === "pending") {
                                    echo htmlspecialchars($arrayOfTranslations["StatusPending"] ?? "Pending", ENT_QUOTES, 'UTF-8');
                                } elseif ($order["OrderStatus"] === "allowed") {
                                    echo htmlspecialchars($arrayOfTranslations["StatusAllowed"] ?? "Allowed", ENT_QUOTES, 'UTF-8');
                                } elseif ($order["OrderStatus"] === "rejected") {
                                    echo htmlspecialchars($arrayOfTranslations["StatusRejected"] ?? "Rejected", ENT_QUOTES, 'UTF-8');
                                } elseif ($order["OrderStatus"] === "completed") {
                                    echo htmlspecialchars($arrayOfTranslations["StatusCompleted"] ?? "Completed", ENT_QUOTES, 'UTF-8');
                                } else {
                                    echo htmlspecialchars($order["OrderStatus"], ENT_QUOTES, 'UTF-8');
                                }
                                ?>
                            </strong>
                        </p>

                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= (int)$order["OrderID"] ?>">

                            <select name="order_status">
                                <option value="pending" <?= ($order["OrderStatus"] == "pending") ? "selected" : "" ?>>
                                    <?= htmlspecialchars($arrayOfTranslations["StatusPending"] ?? "Pending", ENT_QUOTES, 'UTF-8') ?>
                                </option>

                                <option value="allowed" <?= ($order["OrderStatus"] == "allowed") ? "selected" : "" ?>>
                                    <?= htmlspecialchars($arrayOfTranslations["StatusAllowed"] ?? "Allowed", ENT_QUOTES, 'UTF-8') ?>
                                </option>

                                <option value="rejected" <?= ($order["OrderStatus"] == "rejected") ? "selected" : "" ?>>
                                    <?= htmlspecialchars($arrayOfTranslations["StatusRejected"] ?? "Rejected", ENT_QUOTES, 'UTF-8') ?>
                                </option>

                                <option value="completed" <?= ($order["OrderStatus"] == "completed") ? "selected" : "" ?>>
                                    <?= htmlspecialchars($arrayOfTranslations["StatusCompleted"] ?? "Completed", ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            </select>

                            <button type="submit" name="update_order_status" class="logout-nav">
                                <?= htmlspecialchars($arrayOfTranslations["AdminUpdateStatus"] ?? "Update status", ENT_QUOTES, 'UTF-8') ?>
                            </button>
                        </form>
                        <br>

                        <table>
                            <tr>
                                <th><?= htmlspecialchars($arrayOfTranslations["CartProduct"] ?? "Product", ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($arrayOfTranslations["CartPrice"] ?? "Price", ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($arrayOfTranslations["CartQuantity"] ?? "Quantity", ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($arrayOfTranslations["CartSubtotal"] ?? "Subtotal", ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>

                            <?php
                            $orderId = $order["OrderID"];

                            $sqlItems = $connection->prepare(
                                "SELECT * FROM OrderItems WHERE OrderID = ?"
                            );
                            $sqlItems->bind_param("i", $orderId);
                            $sqlItems->execute();
                            $itemsResult = $sqlItems->get_result();

                            while ($item = $itemsResult->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td>
                                        <?php
                                        $productName = ($language == "EN")
                                            ? $item["ProductNameEN"]
                                            : $item["ProductNameDE"];

                                        echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </td>
                                    <td><?= number_format($item["Price"], 2) ?> EUR</td>
                                    <td><?= (int)$item["Quantity"] ?> g</td>
                                    <td><?= number_format($item["Subtotal"], 2) ?> EUR</td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <br>
            <?php
                }
            }
            ?>
        </section>
    </main>
</body>

</html>