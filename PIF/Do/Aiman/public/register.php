<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
/** @var mysqli $conn */
$title = "Register";

$msg = "";

if (isLoggedIn()) {
  header("Location: " . appUrl("/user/welcome.php"));
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();

  $username  = trim($_POST["username"] ?? "");
  $full_name = trim($_POST["full_name"] ?? "");
  $email     = trim($_POST["email"] ?? "");
  $pass1     = $_POST["password"] ?? "";
  $pass2     = $_POST["password2"] ?? "";

  if ($username === "" || $full_name === "" || $email === "" || $pass1 === "") {
    $msg = t("please_fill_all_fields");
  } else if ($pass1 !== $pass2) {
    $msg = t("passwords_do_not_match");
  } else {
    $hash = password_hash($pass1, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, full_name, email, password, role) VALUES (?,?,?,?, 'user')");
    if (!$stmt) {
      error_log("Register prepare failed: " . mysqli_error($conn));
      $msg = "Registration is currently not available. Please check the server database setup.";
    } else {
      mysqli_stmt_bind_param($stmt, "ssss", $username, $full_name, $email, $hash);

      try {
        if (mysqli_stmt_execute($stmt)) {
          $msg = t("account_created_login");
        } else {
          error_log("Register execute failed: " . mysqli_stmt_error($stmt));
          $msg = "Username or email already exists, or the database user cannot insert new accounts.";
        }
      } catch (mysqli_sql_exception $e) {
        error_log("Register execute exception: " . $e->getMessage());
        $msg = "Username or email already exists.";
      }
    }
  }
}

require_once __DIR__ . "/../admin/includes/header.php";
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h1 class="h4 mb-3"><?= esc(t("register")) ?></h1>

      <?php if ($msg !== ""): ?>
        <div class="alert alert-info"><?= esc($msg) ?></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">

        <div class="mb-2">
          <label class="form-label"><?= esc(t("username")) ?></label>
          <input class="form-control" name="username" required>
        </div>

        <div class="mb-2">
          <label class="form-label"><?= esc(t("full_name")) ?></label>
          <input class="form-control" name="full_name" required>
        </div>

        <div class="mb-2">
          <label class="form-label"><?= esc(t("email")) ?></label>
          <input class="form-control" type="email" name="email" required>
        </div>

        <div class="mb-2">
          <label class="form-label"><?= esc(t("password")) ?></label>
          <input class="form-control" type="password" name="password" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><?= esc(t("confirm_password")) ?></label>
          <input class="form-control" type="password" name="password2" required>
        </div>

        <button class="btn btn-dark w-100"><?= esc(t("create_account")) ?></button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . "/../admin/includes/footer.php";
 ?>
