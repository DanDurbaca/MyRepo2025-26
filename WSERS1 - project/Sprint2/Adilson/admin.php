<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <link rel="stylesheet" type="text/css" href="ShopStyle.CSS?<?= time() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin page</title>
</head>
<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("admin");
    
    // Define constants at the top level
    define('ALLOWED_FILES', [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/pjpeg'=> 'jpg',
        'image/x-jpeg'=> 'jpg'
    ]);
    define('MAX_SIZE', 5 * 1024 * 1024); // 5MB
    define('UPLOAD_DIR', __DIR__.'/img');
    ?>
    <h2>Admin Panel</h2>
    <p>Welcome to the admin panel. Here you can manage the webshop.</p>
    <?php
    if ($_SESSION["ADMIN"] == "yes") {
        print("<p>You have administrative privileges.</p>");
        
        // Handle Product Addition with Image Upload
        if (isset($_POST["add_product"])) {
            if (!isset($_POST["pdc_name"], $_POST["pdc_price"], $_POST["pdc_description"], $_POST["pdc_namePT"], $_POST["pdc_descriptionPT"]) || 
                empty($_POST["pdc_name"]) || empty($_POST["pdc_price"]) || !isset($_FILES['fileToUpload'])) {
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
                                $pdc_link = $newName; // Store the filename

                                // Now add product to CSV with the image filename
                                $fHandler = fopen("products.csv", "a");
                                fwrite($fHandler, "\n" . $_POST["pdc_name"] . ";" . $pdc_link . ";" . $_POST["pdc_price"] . ";" . $_POST["pdc_description"] . ";" . $_POST["pdc_descriptionPT"] . ";" . $_POST["pdc_namePT"]);
                                fclose($fHandler);
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
            <div>Product Description</div><br>
            <input type="text" name="pdc_description" required><br><br>
            <div>Product Name in Portuguese</div><br>
            <input type="text" name="pdc_namePT" required><br><br>
            <div>Product Description in Portuguese</div><br>
            <input type="text" name="pdc_descriptionPT" required><br><br>
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