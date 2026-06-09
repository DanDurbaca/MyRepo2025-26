<?php
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];

    foreach ($_SESSION["Cart"] as $cart_item) {
      try {
        $stmt = $pdo->prepare("INSERT INTO orders (ord_product_name, ord_product_description, ord_product_quantity, ord_product_price, ord_product_img, user_id) values (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cart_item["product_name"], $cart_item["description"], $cart_item["quantity"], $cart_item["price"], $cart_item["image_path"], $user_id]);

        $_SESSION["Cart"] = [];
      } catch (PDOException $e) {
        echo "Error: " . $e;
      }
    }

    header("Location: ../../public/pages/cart/");
    exit;
  } else {
    echo "Something wrong(";
  }
