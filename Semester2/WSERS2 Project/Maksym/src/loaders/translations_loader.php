<?php
  function loadTranslations(PDO $pdo, array $activeLanguages): array {
    $languageCode = $_GET["lang"] ?? $_SESSION["lang"] ?? "en";

    $codes = array_column($activeLanguages, 'code');

    if (!in_array($languageCode, $codes, true)) {
      $languageCode = "en";
    }

    $_SESSION["lang"] = $languageCode;

    try {
      $stmt = $pdo->prepare("SELECT key_name, value FROM translations WHERE lang_code = ?");
      $stmt->execute([$languageCode]);

      $translation = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
      die("Error loading translations: " . htmlspecialchars($e->getMessage()));
    }

    $fallbackTranslations = [];

    if ($languageCode !== "en") {
      try {
        $stmt = $pdo->prepare("SELECT key_name, value FROM translations WHERE lang_code = 'en'");
        $stmt->execute();
        $fallbackTranslations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
      } catch (PDOException $e) {
        die("Error loading English fallback: " . htmlspecialchars($e->getMessage()));
      }
    }

    return [
      "lang" => $languageCode,
      "activeLanguages" => $activeLanguages,
      "translation" => $translation,
      "fallback" => $fallbackTranslations
    ];
  }
