<?php
  require_once "../includes/dbh.php";

  $errors = [];

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirm = trim($_POST["password-confirm"] ?? "");
    $captcha = trim($_POST["captcha"] ?? "");

    if ($username === "" || $email === "" || $password = "" || $confirm === "") {
      $errors[] = t("error.fill-all-fields");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = t("error.invalid-email");
    } elseif ($password !== $confirm) {
        $errors[] = t("error.password-mismatch");
    } elseif (strlen($password) < 8) {
        $errors[] = t("error.short-password");
    } elseif ($captcha != $_SESSION["captcha_answer"]) {
      $errors[] = t("error.captcha-incorrect");
    }

    if (empty($errors)) {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
      $stmt->execute([$username, $email, $hash]);

      header("Location: ../index.php");
      exit;
    } else {
      $_SESSION["signup_errors"] = $errors;

      header("Location: ../index.php");
      exit();
    }
  } else {
    header("Location: ../index.php");

    exit;
  }
