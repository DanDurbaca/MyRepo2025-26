<?php
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $message = $_POST["message"] ?? "IdI Nahuj!";

      try {
          $stmt = $pdo->prepare("INSERT INTO messages (user_id, username, message) VALUES (?, ?, ?)");
          $stmt->execute([$_SESSION["user_id"], $_SESSION["username"], $message]);
      } catch (PDOException $e) {
          echo "Fail: " . $e;
      }

      header("Location: ../../public/pages/forum/");
  }
