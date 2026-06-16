<?php
$error_logs = __DIR__ . "/../logs/errors.log";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $password = trim($_POST["password"] ?? "");
  $confirm = trim($_POST["password-confirm"] ?? "");
  $captcha = trim($_POST["captcha"] ?? "");

  $isAdmin = 0;

  $hasLower = !preg_match("/[a-z]/", $password);
  $hasUpper = !preg_match("/[A-Z]/", $password);
  $hasNumber = !preg_match("/[0-9]/", $password);
  $hasSpecial = !preg_match("/[\W_]/", $password);

  try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $emailCheck = $stmt->fetchAll();
  } catch (PDOException $e) {
    error_log("DB Connection Error: " . $e->getMessage(), 3, $error_logs);
    exit(1);
  }

  if ($username === "" || $email === "" || $password === "" || $confirm === "") {
    $errors[] = t("error.fill-all-fields");
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = t("error.invalid-email");
  }

  if (!empty($emailCheck)) {
    $errors[] = t("error.identical-emails");
  }

  if ($password !== $confirm) {
    $errors[] = t("error.password-mismatch");
  }

  if (strlen($password) < 8) {
    $errors[] = t("error.short-password");
  }

  if ($hasLower || $hasUpper || $hasNumber || $hasSpecial) {
    if ($hasLower) {
      $errors[] = t("error.password-lower");
    }

    if ($hasUpper) {
      $errors[] = t("error.password-upper");
    }

    if ($hasNumber) {
      $errors[] = t("error.password-number");
    }

    if ($hasSpecial) {
      $errors[] = t("error.password-special");
    }
  }

  if ($captcha != $_SESSION["captcha_answer"]) {
    $errors[] = t("error.captcha-incorrect");
  }

  if (true) {
    try {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, is_admin) VALUES (?, ?, ?, ?)");
      $stmt->execute([$username, $email, $hash, $isAdmin]);

      $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
      $stmt->execute([$username]);
      $user_id = $stmt->fetch();

      $_SESSION["isLogged"] = true;
      $_SESSION["username"] = $username;
      $_SESSION["user_id"] = $user_id["id"];
      $_SESSION["isAdmin"] = $is_admin;

      header("Location: " . $_SERVER["PHP_SELF"]);
      exit;
    } catch (PDOException $e) {
      error_log("DB Connection Error: " . $e->getMessage(), 3, $error_logs);
      exit(1);
    }
  } else {
    $_SESSION["signup_errors"] = $errors;

    header("Location: " . basename($_SERVER["PHP_SELF"], ".php"));
    exit;
  }
} else {
  header("Location: ../../public/pages/home/");
  exit;
}
