<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="style.css?<?= time() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin page</title>
</head>

<body>
    <?php
    include_once("commonCode.php");
    navBar("Admin");

    // Define constants at the top level
    const ALLOWED_FILES = [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/x-jpeg' => 'jpg'
    ];
    const MAX_SIZE = 5 * 1024 * 1024; // 5MB
    const UPLOAD_DIR =  __DIR__ . '../../images';
    ?>
    <h2>Admin Panel</h2>
    <p>Welcome to the admin panel. Here you can manage the webshop.</p>
    <?php
    if ($_SESSION["IsAdmin"] == 1) {
        print("<p>You have administrative privileges.</p>");

        // Handle Product Addition with Image Upload
        if (isset($_POST["add_product"])) {
            if (
                !isset($_POST["pdc_name"], $_POST["pdc_price"], $_POST["pdc_namePT"], $_POST["pg_link"]) ||
                empty($_POST["pdc_name"]) || empty($_POST["pdc_price"]) || !isset($_FILES['fileToUpload'])
            ) {
                print("<p style='color: red;'>Some information is missing or no image was uploaded!</p><br>");
            } else {
                // Process image upload first
                $file = $_FILES['fileToUpload'];
                $pdc_link = ""; // Will store the filename

                if ($file['error'] === UPLOAD_ERR_OK) {
                    if (filesize($file['tmp_name']) <= MAX_SIZE) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);

                        $allowedFiles = ALLOWED_FILES;
                        if (isset($allowedFiles[$mime_type])) {
                            $newName = pathinfo($file['name'], PATHINFO_FILENAME) . '.' . $allowedFiles[$mime_type];
                            $destination = UPLOAD_DIR . '/' . $newName;

                            if (move_uploaded_file($file['tmp_name'], $destination)) {
                                $pdc_link = "../images/" . $newName; // Store the filename

                                // Now add product to database with the image filename
                                $connection = new mysqli("localhost", "root", "", "HTSTA_DB");
                                $sqlQuery = $connection->prepare("INSERT INTO Products(productEN, productPT, price, imageLink, pageLink) values(?,?,?,?,?);");
                                $sqlQuery->bind_param("sssss", $_POST["pdc_name"], $_POST["pdc_namePT"], $_POST["pdc_price"], $pdc_link, $_POST["pg_link"]);
                                $sqlQuery->execute();
                                print("<p style='color: green;'>Product " . htmlspecialchars($_POST["pdc_name"]) . " added successfully with image: " . htmlspecialchars($newName) . "</p>");
                            } else {
                                print("<p style='color: red;'>Error saving image file.</p>");
                            }
                        } else {
                            print("<p style='color: red;'>Invalid file type. Only PNG and JPG are allowed.</p>");
                        }
                    } else {
                        print("<p style='color: red;'>File too large. Maximum size is 5MB.</p>");
                    }
                } else {
                    print("<p style='color: red;'>Upload error: " . $file['error'] . "</p>");
                }
            }
        }
    ?>
        <form method="POST" enctype="multipart/form-data">
            <div>Product Name</div><br>
            <input type="text" name="pdc_name" required><br><br>
            <div>Product Price</div><br>
            <input type="text" name="pdc_price" placeholder="e.g., $39.99" required><br><br>
            <div>Product Name in Portuguese</div><br>
            <input type="text" name="pdc_namePT" required><br><br>
            <div>Page Link (optional)</div><br>
            <input type="text" name="pg_link"><br><br>
            <div>Product Image</div><br>
            <input type="file" name="fileToUpload" id="fileToUpload" accept="image/png, image/jpeg" required><br><br>
            <input type="submit" value="Add Product" name="add_product">
        </form>

        <hr> <?php
                // --- SPRINT 2: PROCESS ORDER APPROVAL ---
                $connection = new mysqli("localhost", "root", "", "HTSTA_DB");

                if (isset($_POST["approve_order"])) {
                    $orderIdToApprove = $_POST["order_id"];
                    $sqlUpdateStatus = $connection->prepare("UPDATE Orders SET status = 'delivered' WHERE orderID = ?");
                    $sqlUpdateStatus->bind_param("i", $orderIdToApprove);
                    $sqlUpdateStatus->execute();
                    print("<p style='color: green; font-weight: bold;'>Order #$orderIdToApprove has been marked as Delivered!</p>");
                }

                // --- SPRINT 2: DISPLAY PENDING ORDERS ---
                print("<h3>Pending Orders Management</h3>");

                // Fetch only orders that are currently pending
                $sqlPendingOrders = $connection->prepare("SELECT * FROM Orders WHERE status = 'pending' ORDER BY orderID DESC");
                $sqlPendingOrders->execute();
                $ordersResult = $sqlPendingOrders->get_result();

                if ($ordersResult->num_rows > 0) {
                    while ($orderRow = $ordersResult->fetch_assoc()) {
                        $currentOrderID = $orderRow["orderID"];
                        $customer = $orderRow["username"];

                        print("<div style='border: 1px solid black; margin-bottom: 15px; padding: 10px;'>");
                        print("<strong>Order ID:</strong> #$currentOrderID | <strong>Customer:</strong> $customer | <strong>Status:</strong> pending<br><br>");

                        // Fetch items belonging to this specific order
                        $sqlOrderItems = $connection->prepare("
                    SELECT BoughtItem.quantity, Products.productEN, Products.productPT, Products.price 
                    FROM BoughtItem 
                    JOIN Products ON BoughtItem.productID = Products.productID 
                    WHERE BoughtItem.orderID = ?
                ");
                        $sqlOrderItems->bind_param("i", $currentOrderID);
                        $sqlOrderItems->execute();
                        $itemsResult = $sqlOrderItems->get_result();

                        print("<table border='1' style='width:100%; border-collapse: collapse;'>");
                        print("<tr>
                        <th>" . (($language == "EN") ? "Product Name" : "Nome do Produto") . "</th>
                        <th>" . $arrayOfTranslations["Price"] . "</th>
                        <th>" . $arrayOfTranslations["Quantity"] . "</th>
                        <th>" . $arrayOfTranslations["SubTotal"] . "</th>
                       </tr>");

                        $orderTotal = 0;
                        while ($itemRow = $itemsResult->fetch_assoc()) {
                            $productName = ($language == "EN") ? $itemRow["productEN"] : $itemRow["productPT"];
                            $subtotal = $itemRow["price"] * $itemRow["quantity"];
                            $orderTotal += $subtotal;
                            print("<tr>");
                            print("<td>" . htmlspecialchars($productName) . "</td>");
                            print("<td>" . $itemRow["price"] . "€</td>");
                            print("<td>" . $itemRow["quantity"] . "</td>");
                            print("<td>" . $subtotal . "€</td>");
                            print("</tr>");
                        }
                        print("<tr><td colspan='3' align='right'><strong>Total:</strong></td><td><strong>" . $orderTotal . "€</strong></td></tr>");
                        print("</table><br>");

                        // Action Form to change status to 'delivered'
                        print("<form method='POST'>");
                        print("<input type='hidden' name='order_id' value='$currentOrderID'>");
                        print("<button type='submit' name='approve_order'>" . (($language == "EN") ? "Mark as Delivered" : "Marcar como Entregue") . "</button>");
                        print("</form>");
                        print("</div>");
                    }
                } else {
                    print("<p>" . (($language == "EN") ? "No pending orders at this moment." : "Não existem encomendas pendentes de momento.") . "</p>");
                }
                ?>

    <?php
    } else {
        print("<h1>You do not have permission to access this page.</h1>");
        die();
    }
    ?>
</body>

</html>