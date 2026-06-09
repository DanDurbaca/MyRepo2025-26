<?php
  function loadActiveLanguages(PDO $pdo): array {
    try {
      $stmt = $pdo->query("SELECT code, name FROM languages WHERE is_active = 1");

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      die("Error loading languages: " . htmlspecialchars($e->getMessage()));
    }
  }
