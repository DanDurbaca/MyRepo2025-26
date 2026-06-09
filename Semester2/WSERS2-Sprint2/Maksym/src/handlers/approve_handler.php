<?php
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST["item_for_approve"];

    try {
      $stmt = $pdo->prepare("UPDATE orders SET ord_product_status = 'delivered' WHERE order_id = ?");
      $stmt->execute([$order_id]);
    } catch (PDOException $e) {
      echo "Error: " . $e;

      exit(1);
    }

    header("Location: ../../public/pages/order-history/");
  }
