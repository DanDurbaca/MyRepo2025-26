<?php
  require_once __DIR__ . "/../handlers/database_handler.php";
  require_once __DIR__ . "/../classes/Translator.php";
  require_once __DIR__ . "/../loaders/languages_loader.php";
  require_once __DIR__ . "/../loaders/translations_loader.php";
  require_once __DIR__ . "/../loaders/product_loader.php";
  require_once __DIR__ . "/../helpers/brand.php";

  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  if (!isset($_SESSION["isLogged"])) {
    $_SESSION["isLogged"] = false;
  }

  if (!isset($_SESSION["username"])) {
    $_SESSION["username"] = "";
  }

  if (!isset($_SESSION["user_id"])) {
    $_SESSION["user_id"] = 0;
  }

  if (!isset($_SESSION["isAdmin"])) {
    $_SESSION["isAdmin"] = 0;
  }

  if (!isset($_SESSION["Cart"])) {
    $_SESSION["Cart"] = [];
  }

  $pdo = getPDO();

  $activeLanguages = loadActiveLanguages($pdo);
  $loaded = loadTranslations($pdo, $activeLanguages);

  $currentLang = $loaded['lang'];
  $translation = $loaded['translation'];
  $fallbacks = $loaded['fallback'];

  $translator = new Translator($currentLang, $translation, $fallbacks);

  function t(string $key, array $data = []): string {
    global $translator;

    return $translator->t($key, $data);
  }

  $products = loadProducts($pdo);
