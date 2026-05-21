<?php
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item = $_POST["item"];
    $quantity = $_POST["quantity"];

    try {
      $stmt = $pdo->query("SELECT id, image_path, product_name, description, quantity, price FROM products WHERE id = '" . $item . "'");
      $newProduct = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $newQuantity = $newProduct[0]["quantity"] - $quantity;
      $pdo->query("UPDATE products SET quantity = " . $newQuantity . " WHERE id = " . $item);
      $newProduct[0]["quantity"] = $quantity;
      $newProduct[0]["price"] = $newProduct[0]["price"] * $quantity;
    } catch (PDOException $e) {
      error_log("DB Connection Error: " . $e->getMessage());

      die("Database connection failed." . $e->getMessage());
    }

    $_SESSION["Cart"] = array_merge($_SESSION["Cart"], $newProduct);

    header("Location: ../../public/pages/market/");
  }
