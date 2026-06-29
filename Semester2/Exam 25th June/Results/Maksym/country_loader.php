<?php
  function loadCountries(PDO $pdo):array {
    try {
      $stmt = $pdo->query("SELECT * FROM countries");

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOExcaption $e) {
      exit("DB Error: " . $e);
    }
  }
