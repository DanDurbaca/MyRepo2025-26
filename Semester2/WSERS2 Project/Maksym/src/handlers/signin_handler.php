<?php
  $errors = [];

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $captcha = trim($_POST["captcha"] ?? "");

    try {
      $stmt = $pdo->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE username = ?");
      $stmt->execute([$username]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      die("Error catched: " . htmlspecialchars($e->getMessage()));
    }

    $hash = $user[0]["password_hash"];

    if ($username === "" || $password === "") {
      $errors[] = t("error.fill-all-fields");
    } elseif (password_verify($password, $hash)) {
      $errors[] = t("error.wrong-password");
    } elseif ($captcha != $_SESSION["captcha_answer"]) {
      $errors[] = t("error.captcha-incorrect");
    }

    if (empty($errors)) {
      $_SESSION["isLogged"] = 1;
      $_SESSION["username"] = $username;
      $_SESSION["user_id"] = $user["id"];
      $_SESSION["isAdmin"] = $user["is_admin"];

      header("Location: ../../public/pages/home/");
      exit;
    } else {
      header("Location: ../../public/pages/home/");
      exit;
    }
  }
