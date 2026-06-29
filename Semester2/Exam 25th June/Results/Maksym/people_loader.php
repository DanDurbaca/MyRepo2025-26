<?php
  function loadPeopleOrderByName(PDO $pdo):array {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ppl WHERE CityId = ? ORDER BY PersonName ASC");
        $stmt->execute([$_SESSION["current_city"]]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOExcaption $e) {
        exit("DB Error: " . $e);
    }
  }

  function loadPeopleOrderByAge(PDO $pdo):array {
    try {
      $stmt = $pdo->prepare("SELECT * FROM ppl WHERE CityId = ? ORDER BY Age ASC");
      $stmt->execute([$_SESSION["current_city"]]);

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOExcaption $e) {
      exit("DB Error: " . $e);
    }
  }
