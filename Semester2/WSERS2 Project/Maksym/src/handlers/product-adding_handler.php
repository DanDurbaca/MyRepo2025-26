<?php
  $uploadDir = __DIR__ . "/../../public/assets/images/products/";
  $maxBytes = 5 * 1024 * 1024;
  $allowedTypes = ["image/jpeg" => "jpeg", "image/png" => "png", "image/webp" => "webp", "image/gif" => "gif"];

  if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_FILES["image-path"])) {
    $product_name = trim($_POST["product-name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $quantity = filter_var($_POST["quantity"] ?? 0, FILTER_VALIDATE_INT);
    $price = $_POST["price"] ?? 0;
    $image = $_FILES['image-path'];

    if ($product_name === "" || $quantity === false || $quantity < 0) {
      http_response_code(400);
      error_log("Invalid input", 3, $error_logs);
      exit;
    }

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
      http_response_code(500);
      error_log("Server error", 3, $error_logs);
      exit;
    }

    if ($image['error'] !== UPLOAD_ERR_OK) {
      http_response_code(400);
      error_log("Upload error", 3, $error_logs);
      exit;
    }

    if ($image["size"] > $maxBytes) {
      http_response_code(400);
      error_log("File too large!", 3, $error_logs);
      exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($image['tmp_name']);

    if (!isset($allowedTypes[$mime])) {
      http_response_code(400);
      error_log("Invalid file type", 3, $error_logs);
      exit;
    }

    if (!is_uploaded_file($image['tmp_name'])) {
      http_response_code(400);
      error_log("Invalid upload", 3, $error_logs);
      exit;
    }

    $ext = $allowedTypes[$mime];
    $basename = bin2hex(random_bytes(16));
    $filename = $basename . '.' . $ext;
    $target = $uploadDir . $filename;

    if (!move_uploaded_file($image['tmp_name'], $target)) {
      http_response_code(500);
      error_log("Failed to save file", 3, $error_logs);
      exit;
    }

    $image_path = "assets/images/products/{$filename}";

    try {
      if (!isset($pdo)) throw new Exception('DB not initialized');
      $stmt = $pdo->prepare("INSERT INTO products (product_name, description, quantity, price, image_path) VALUES (?, ?, ?, ?, ?)");
      $stmt->execute([$product_name, $description, $quantity, $price, $image_path]);

      header("Location: ../../public/pages/add-product/");
      exit;
    } catch(PDOException $e) {
      die("Error catched: " . htmlspecialchars($e->getMessage()));
    }
  } else {
    header("Location: ../../public/pages/add-product/");
    exit;
  }
