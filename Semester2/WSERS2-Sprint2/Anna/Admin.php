<?php
include_once("CommonCode.php");
include_once("Database.php");

if (!isset($_SESSION["UserLogged"]) || $_SESSION["UserLogged"] !== true || $_SESSION["Admin"] !== "yes") {
    header("Location: Login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nameEN"])) {
    
    $nameEN = trim($_POST["nameEN"]);
    $nameRU = trim($_POST["nameRU"]);
    $image  = trim($_POST["image"]);
    $extra  = trim($_POST["extra"]); // This is your Price field
    $descEN = trim($_POST["descEN"]);
    $descRU = trim($_POST["descRU"]);

    if ($nameEN !== "" && $nameRU !== "" && $image !== "" && $extra !== "") {
        
        // 1. Prepare an SQL statement targeting your MySQL database table
        $stmt = $connection->prepare("INSERT INTO Products (ProductNameEN, ImageLink, Price, DescriptionEN, DescriptionRU, ProductNameRU) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            // 2. Bind the form inputs safely to prevent SQL structural errors
            $stmt->bind_param("ssssss", $nameEN, $image, $extra, $descEN, $descRU, $nameRU);
            
            // 3. Execute the query
            if ($stmt->execute()) {
                $message = "Product created successfully in the database!";
            } else {
                $message = "Database error: Failed to save product.";
            }
            $stmt->close();
        } else {
            $message = "Failed to prepare the database query.";
        }

    } else {
        $message = "Please fill all required fields!";
    }
}
// Handle deleting a product from the database
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_product_id"])) {
    $productIdToDelete = intval($_POST["delete_product_id"]);
    
    // Step 1: Clear out any references in BoughtItems first to satisfy foreign keys
    $cleanHistoryStmt = $connection->prepare("DELETE FROM BoughtItems WHERE id = ?;");
    if ($cleanHistoryStmt) {
        $cleanHistoryStmt->bind_param("i", $productIdToDelete);
        $cleanHistoryStmt->execute();
        $cleanHistoryStmt->close();
    }
    
    // Step 2: Delete the actual product row from the table
    $deleteProductStmt = $connection->prepare("DELETE FROM Products WHERE id = ?;");
    if ($deleteProductStmt) {
        $deleteProductStmt->bind_param("i", $productIdToDelete);
        if ($deleteProductStmt->execute()) {
            $message = "Product #" . $productIdToDelete . " deleted successfully!";
        } else {
            $message = "Error deleting product from database.";
        }
        $deleteProductStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="ShopStyles.css?v=<?php echo time(); ?>">
    <title>Admin Panel</title>
</head>
<body>

<?php NavigationBar($arrayOfTranslations["AdminBtn"]); ?>

<div class="adminBox">

    <h1><?= $arrayOfTranslations["AdminBtn"] ?></h1>
    <p><?= $arrayOfTranslations["WelcomeText"] ?> <?= $_SESSION["Username"] ?></p>

    <?php if ($message !== ""): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <h2><?= $arrayOfTranslations["CreateBtn"] ?></h2>

    <form id="productForm" method="POST" enctype="multipart/form-data">

        <label><?= $arrayOfTranslations["NameTextEn"] ?>*</label>
        <input type="text" id="nameEN" name="nameEN">

        <label><?= $arrayOfTranslations["NameTextRu"] ?>*</label>
        <input type="text" id="nameRU" name="nameRU">

        <label><?= $arrayOfTranslations["FileName"] ?>*</label>
        <input type="file" id="file" name="file">

        <input type="hidden" id="image" name="image">

        <label><?= $arrayOfTranslations["Price"] ?>*</label>
        <input type="text" id="extra" name="extra">

        <label><?= $arrayOfTranslations["DescEN"] ?></label>
        <textarea name="descEN"></textarea>

        <label><?= $arrayOfTranslations["DescRU"] ?></label>
        <textarea name="descRU"></textarea>

        <button class="btn" type="submit"><?= $arrayOfTranslations["CreateBtn"] ?></button>
    </form>
        <hr class="sectionDivider" style="margin: 40px 0; border: 0; border-top: 2px dashed #ccc;">

<h2>Manage / Delete Products</h2>

<?php
// Retrieve active products directly from your MySQL table
$productsQuery = $connection->query("SELECT id, ProductNameEN, Price FROM Products ORDER BY id DESC;");

if ($productsQuery && $productsQuery->num_rows > 0): ?>
    <div class="adminProductList" style="display: flex; flex-direction: column; gap: 10px;">
        <?php while ($product = $productsQuery->fetch_assoc()): ?>
            <div class="adminProductCard" style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #e0e0e0; padding: 12px; background: #fff; border-radius: 6px;">
                <div>
                    <strong><?= htmlspecialchars($product['ProductNameEN']) ?></strong> 
                    <span style="color: #666; margin-left: 10px;">(<?= htmlspecialchars($product['Price']) ?>)</span>
                </div>
                
                <!-- Individual Delete Form Trigger -->
                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to permanently delete this product? This will remove it from existing shopping carts and historical tracking records.');" style="margin: 0;">
                    <input type="hidden" name="delete_product_id" value="<?= $product['id'] ?>">
                    <button type="submit" style="background: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                        Delete
                    </button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>No products found in the database.</p>
<?php endif; ?>
<script>
document.getElementById("productForm").addEventListener("submit", function (e) {
    e.preventDefault();

    let requiredFields = ["nameEN", "nameRU", "extra"];
    for (let field of requiredFields) {
        if (document.getElementById(field).value.trim() === "") {
            alert("Please fill all required fields!");
            return;
        }
    }

    let fileInput = document.getElementById("file");
    if (fileInput.files.length === 0) {
        alert("Please select a file!");
        return;
    }

    let formData = new FormData();
    formData.append("file", fileInput.files[0]);

    fetch("upload.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert("Upload error: " + data.error);
            return;
        }

        document.getElementById("image").value = data.filename;
        this.submit();
    })
    .catch(err => alert("Unexpected error: " + err));
});
</script>

<hr class="sectionDivider" style="margin: 40px 0; border: 0; border-top: 2px dashed #ccc;">

<?php
// Handle switching order status from pending to delivered
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["approve_order_id"])) {
    $orderIdToDeliver = intval($_POST["approve_order_id"]);
    $updateStmt = $connection->prepare("UPDATE Orders SET OrderStatus = 'delivered' WHERE OrderID = ?;");
    $updateStmt->bind_param("i", $orderIdToDeliver);
    $updateStmt->execute();
    echo "<p class='successMsg' style='color:#155724; background:#d4edda; padding:12px; border-radius:4px; text-align:center;'>Order #$orderIdToDeliver updated to Delivered!</p>";
}

// Fetch all orders from the database
$allOrders = [];
$ordersQuery = $connection->query("
    SELECT o.OrderID, o.Username, o.OrderStatus, b.Quantity, p.ProductNameEN, p.Price 
    FROM Orders o
    JOIN BoughtItems b ON o.OrderID = b.OrderID
    JOIN Products p ON b.id = p.id
    ORDER BY o.OrderID DESC;
");

while ($row = $ordersQuery->fetch_assoc()) {
    $allOrders[$row['OrderID']]['Username'] = $row['Username'];
    $allOrders[$row['OrderID']]['Status'] = $row['OrderStatus'];
    $allOrders[$row['OrderID']]['items'][] = [
        'Name' => $row['ProductNameEN'],
        'Qty' => $row['Quantity'],
        'Price' => $row['Price']
    ];
}
?>

<h2>Order Management History</h2>
<?php 
if (empty($allOrders)) { 
?>
    <p>No orders to view.</p>
<?php 
} else { 
    foreach ($allOrders as $id => $order) {
        // Set background and text colors based on the status
        $badgeBackground = ($order['Status'] === 'delivered') ? '#d4edda' : '#fff3cd';
        $badgeColor = ($order['Status'] === 'delivered') ? '#155724' : '#856404';
?>
        <div class="orderCard" style="border:1px solid #e0e0e0; padding:15px; margin-bottom:15px; background:#fff; border-radius:6px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Order #<?php echo $id; ?> — User: <?php echo htmlspecialchars($order['Username']); ?></h3>
                
                <!-- Display status badge -->
                <span style="font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 0.9rem; background: <?php echo $badgeBackground; ?>; color: <?php echo $badgeColor; ?>;">
                    <?php echo strtoupper($order['Status']); ?>
                </span>
            </div>
            
            <ul>
                <?php 
                foreach ($order['items'] as $item) { 
                ?>
                    <li><?php echo htmlspecialchars($item['Name']); ?> (x<?php echo $item['Qty']; ?>) - <?php echo htmlspecialchars($item['Price']); ?></li>
                <?php 
                } 
                ?>
            </ul>
            
            <!-- Conditional processing button using regular if/else syntax -->
            <?php 
            if ($order['Status'] === 'pending') { 
            ?>
                <form method="POST" action="">
                    <input type="hidden" name="approve_order_id" value="<?php echo $id; ?>">
                    <button type="submit" class="btnApprove" style="background:#28a745; color:white; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Mark as Delivered</button>
                </form>
            <?php 
            } else { 
            ?>
                <p style="color:#6c757d; font-size:0.9rem; margin:0; font-style:italic;">This order has been completed.</p>
            <?php 
            } 
            ?>
        </div>
<?php 
    } 
} 
?>

</div>
</body>
</html>
