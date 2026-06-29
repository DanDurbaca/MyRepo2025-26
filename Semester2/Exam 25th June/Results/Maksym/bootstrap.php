<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  require_once __DIR__ . "/db_handler.php";

  include_once __DIR__ . "/country_loader.php";
  include_once __DIR__ . "/city_loader.php";
  include_once __DIR__ . "/people_loader.php";

  $pdo = getPDO();

  if (!isset($_SESSION["current_countr"])) {
    $_SESSION["current_countr"] = 0;
  }

  if (isset($_POST["countrs"])) {
    $_SESSION["current_countr"] = (int)($_POST["countrs"]);
  }

  if (!isset($_SESSION["current_city"])) {
    $_SESSION["current_city"] = 0;
  }

  if (isset($_POST["cities"])) {
    $_SESSION["current_city"] = (int)($_POST["cities"]);
  }

  if (!isset($_SESSION["current_person"])) {
    $_SESSION["current_person"] = 0;
  }

  if (isset($_POST["ppls"])) {
    $_SESSION["current_person"] = (int)($_POST["ppls"]);
  }

  if (!isset($_SESSION["countries"])) $_SESSION["countries"] = loadCountries($pdo);

  $_SESSION["cities"] = loadCities($pdo);
  $_SESSION["people_byname"] = loadPeopleOrderByName($pdo);
  $_SESSION["people_byage"] = loadPeopleOrderByAge($pdo);
