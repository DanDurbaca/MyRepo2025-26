<?php
  function loadCities(PDO $pdo):array {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cities WHERE CountryId = ?");
        $stmt->execute([$_SESSION["current_countr"]]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOExcaption $e) {
        exit("DB Error: " . $e);
    }
  }
