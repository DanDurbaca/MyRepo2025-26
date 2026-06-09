<?php
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item = $_POST["item"];
    $quantity = $_POST["quantity"];
    $exists = false;

    if ($quantity <= 0) {
      header("Location: ../../public/pages/market/");

      exit;
    }

    try {
      $stmt = $pdo->query("SELECT id, image_path, product_name, description, quantity, price FROM products WHERE id = '" . $item . "'");
      $newProduct = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $newQuantity = $newProduct[0]["quantity"] - $quantity;
      $pdo->query("UPDATE products SET quantity = " . $newQuantity . " WHERE id = " . $item);

      for ($i = 0; $i < count($_SESSION["Cart"]); $i++) {
        if ($newProduct[0]["id"] === $_SESSION["Cart"][$i]["id"]) {
          $_SESSION["Cart"][$i]["quantity"] += $quantity;

          $exists = true;
        }
      }

      if (!$exists) {
        $newProduct[0]["quantity"] = $quantity;
        $newProduct[0]["price"] = $newProduct[0]["price"] * $quantity;

        $_SESSION["Cart"] = array_merge($_SESSION["Cart"], $newProduct);
      }
    } catch (PDOException $e) {
      error_log("DB Connection Error: " . $e->getMessage());

      die("Database connection failed." . $e->getMessage());
    }

    header("Location: ../../public/pages/market/");
  }
