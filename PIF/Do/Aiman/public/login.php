<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
/** @var mysqli $conn */
$title = "Login";

$msg = "";

if (isLoggedIn()) {
  header("Location: " . (isAdmin() ? appUrl("/admin/dashboard.php") : appUrl("/user/welcome.php")));
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();

  $username = trim($_POST["username"] ?? "");
  $password = $_POST["password"] ?? "";

  // The schema evolved during the project, so login adapts if theme/language
  // preferences are not present yet in an older database copy.
  if (hasThemeColumn($conn) && hasLanguageColumn($conn)) {
    $sql = "SELECT user_id, username, password, role, theme, language FROM users WHERE username = ?";
  } else if (hasThemeColumn($conn)) {
    $sql = "SELECT user_id, username, password, role, theme FROM users WHERE username = ?";
  } else if (hasLanguageColumn($conn)) {
    $sql = "SELECT user_id, username, password, role, language FROM users WHERE username = ?";
  } else {
    $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
  }

  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  if ($row = mysqli_fetch_assoc($res)) {
    if (password_verify($password, $row["password"])) {
      // Session values are the central access-control state used by user/admin pages.
      $_SESSION["user_id"] = (int)$row["user_id"];
      $_SESSION["username"] = $row["username"];
      $_SESSION["role"] = $row["role"];
      saveThemePreference($conn, $row["theme"] ?? "light");
      saveLanguagePreference($conn, $row["language"] ?? "en");

      header("Location: " . ($row["role"] === "admin" ? appUrl("/admin/dashboard.php") : appUrl("/user/welcome.php")));
      exit();
    }
  }
  $msg = t("wrong_username_or_password");
}

require_once __DIR__ . "/../admin/includes/header.php";
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
      <h1 class="h4 mb-3"><?= esc(t("login")) ?></h1>

      <?php if ($msg !== ""): ?>
        <div class="alert alert-danger"><?= esc($msg) ?></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">

        <div class="mb-2">
          <label class="form-label"><?= esc(t("username")) ?></label>
          <input class="form-control" name="username" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><?= esc(t("password")) ?></label>
          <input class="form-control" type="password" name="password" required>
        </div>

        <button class="btn btn-dark w-100"><?= esc(t("login")) ?></button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . "/../admin/includes/footer.php";
 ?>
