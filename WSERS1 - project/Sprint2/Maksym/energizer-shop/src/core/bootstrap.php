<?php
  require_once __DIR__ . "/../handlers/database_handler.php";
  require_once __DIR__ . "/../classes/Translator.php";
  require_once __DIR__ . "/../loaders/languages_loader.php";
  require_once __DIR__ . "/../loaders/translations_loader.php";
  require_once __DIR__ . "/../helpers/brand.php";

  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  $pdo = getPDO();

  $activeLanguages = loadActiveLanguages($pdo);
  $loaded = loadTranslations($pdo, $activeLanguages);

  $currentLang   = $loaded['lang'];
  $translation  = $loaded['translation'];
  $fallbacks     = $loaded['fallback'];

  $translator = new Translator(
    $currentLang,
    $translation,
    $fallbacks
  );

  function t(string $key, array $data = []): string {
    global $translator;

    return $translator->t($key, $data);
  }
