<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../style.css?<?= time() ?>">
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
                                $pdc_link = "../images/".$newName; // Store the filename

                                // Now add product to CSV with the image filename
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

    <?php
    } else {
        print("<h1>You do not have permission to access this page.</h1>");
        die();
    }
    ?>
</body>

</html>