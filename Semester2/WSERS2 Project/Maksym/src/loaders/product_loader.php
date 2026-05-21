<?php
  function loadProducts(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, product_name, description, quantity, price, image_path, available FROM products");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
