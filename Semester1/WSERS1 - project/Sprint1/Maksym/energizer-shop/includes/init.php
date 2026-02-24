<?php
  require_once "dbh.php";

  session_start();

  try {
    $stmt = $pdo->query("SELECT code, name FROM languages WHERE is_active = 1");
    $languages= $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    die("Database error while loading languages: " . htmlspecialchars($e->getMessage()));
  }

  $languageCodes = array_column($languages, "code");

  $languageCode = $_GET["lang"] ?? $_SESSION["lang"] ?? "en";

  if (!in_array($languageCode, $languageCodes)) {
    $languageCode = "en";
  }

  $_SESSION["lang"] = $languageCode;

  try {
    $stmt = $pdo->prepare("SELECT key_name, value FROM translations WHERE lang_code = ?");
    $stmt->execute([$languageCode]);
    $translations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
  } catch (PDOException $e) {
    die("Database error while loading translations: " . htmlspecialchars($e->getMessage()));
  }

  function t($key, $replacements = []) {
    global $translations;

    $value = $translations[$key] ?? null;

    if ($value === null && str_contains($key, ".")) {
      $parts = explode(".", $key);
      $lookupKey = implode(".", $parts);
      $value = $translations[$lookupKey] ?? $key;
    }

    $text = $value ?? $key;

    foreach ($replacements as $placeHolder => $replacement) {
      $text = str_replace("{" . $placeHolder . "}", $replacement, $text);
    }

    if ($text === $key && $_SESSION["lang"] !== "en") {
    global $pdo;

    $stmt = $pdo->prepare("SELECT value FROM translations WHERE lang_code = 'en' AND key_name = ?");
    $stmt->execute([$key]);
    $fallback = $stmt->fetchColumn();

    if ($fallback) $text = $fallback;
    }

    return $text;
  }

  $brand = "<span id='highlighted-text-1'>" . t("brand-name-1") . "</span>" . "<span id='highlighted-text-2'>" . t('brand-name-2') . "</span>";
