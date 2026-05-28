<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
/** @var mysqli $conn */
requireLogin();
$title = "Account";

$msg = "";
$user = getCurrentUser($conn);
$currentTheme = getThemePreference($conn);
$currentLanguage = getLanguagePreference($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();

  $action = $_POST["action"] ?? "save_account";

  if ($action === "save_theme") {
    $theme = ($_POST["theme"] ?? "light") === "dark" ? "dark" : "light";
    saveThemePreference($conn, $theme);
    $currentTheme = $theme;
    $msg = t("theme_updated");
  } else if ($action === "save_language") {
    $language = ($_POST["language"] ?? "en") === "fr" ? "fr" : "en";
    saveLanguagePreference($conn, $language);
    $currentLanguage = $language;
    $msg = t("language_updated");
  } else {
    $username  = trim($_POST["username"] ?? "");
    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");

    $new1 = $_POST["new_password"] ?? "";
    $new2 = $_POST["new_password2"] ?? "";

    if ($username === "" || $full_name === "" || $email === "") {
      $msg = t("please_fill_all_fields");
    } else if ($new1 !== "" && $new1 !== $new2) {
      $msg = t("passwords_do_not_match");
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, full_name=?, email=? WHERE user_id=?");
      mysqli_stmt_bind_param($stmt, "sssi", $username, $full_name, $email, $_SESSION["user_id"]);
      $ok = mysqli_stmt_execute($stmt);

      if ($ok && $new1 !== "") {
        $hash = password_hash($new1, PASSWORD_DEFAULT);
        $stmt2 = mysqli_prepare($conn, "UPDATE users SET password=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt2, "si", $hash, $_SESSION["user_id"]);
        mysqli_stmt_execute($stmt2);
      }

      if ($ok) {
        $_SESSION["username"] = $username;
        $msg = t("account_updated");
      } else {
        $msg = t("update_failed_exists");
      }
    }
  }

  $user = getCurrentUser($conn);
  $currentTheme = getThemePreference($conn);
  $currentLanguage = getLanguagePreference($conn);
}

require_once __DIR__ . "/../admin/includes/header.php";
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-3 h-100">
      <h2 class="h5"><?= esc(t("appearance")) ?></h2>
      <p class="text-muted"><?= esc(t("current_theme")) ?></p>

      <form method="post" class="d-grid gap-2">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="save_theme">

        <button
          class="btn <?= $currentTheme === "light" ? "btn-dark" : "btn-outline-dark" ?>"
          type="submit"
          name="theme"
          value="light"
        >
          <?= esc(t("light_mode")) ?>
        </button>

        <button
          class="btn <?= $currentTheme === "dark" ? "btn-dark" : "btn-outline-dark" ?>"
          type="submit"
          name="theme"
          value="dark"
        >
          <?= esc(t("dark_mode")) ?>
        </button>
      </form>

      <p class="small text-muted mt-3 mb-3"><?= esc(t("current_theme")) ?>: <strong><?= esc($currentTheme === "dark" ? t("dark_mode") : t("light_mode")) ?></strong></p>

      <hr>

      <h2 class="h5"><?= esc(t("language")) ?></h2>
      <p class="text-muted"><?= esc(t("current_language")) ?>: <strong><?= esc($currentLanguage === "fr" ? t("french") : t("english")) ?></strong></p>

      <form method="post" class="d-grid gap-2">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="save_language">

        <button
          class="btn <?= $currentLanguage === "en" ? "btn-dark" : "btn-outline-dark" ?>"
          type="submit"
          name="language"
          value="en"
        >
          <?= esc(t("english")) ?>
        </button>

        <button
          class="btn <?= $currentLanguage === "fr" ? "btn-dark" : "btn-outline-dark" ?>"
          type="submit"
          name="language"
          value="fr"
        >
          <?= esc(t("french")) ?>
        </button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card p-3">
      <h2 class="h5"><?= esc(t("my_account")) ?></h2>

      <?php if ($msg !== ""): ?>
        <div class="alert alert-info"><?= esc($msg) ?></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="save_account">

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label"><?= esc(t("username")) ?></label>
            <input class="form-control" name="username" value="<?= esc($user["username"]) ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label"><?= esc(t("full_name")) ?></label>
            <input class="form-control" name="full_name" value="<?= esc($user["full_name"]) ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label"><?= esc(t("email")) ?></label>
            <input class="form-control" type="email" name="email" value="<?= esc($user["email"]) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label"><?= esc(t("new_password_optional")) ?></label>
            <input class="form-control" type="password" name="new_password">
          </div>

          <div class="col-md-6">
            <label class="form-label"><?= esc(t("confirm_new_password")) ?></label>
            <input class="form-control" type="password" name="new_password2">
          </div>

          <div class="col-12">
            <button class="btn btn-dark"><?= esc(t("save")) ?></button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . "/../admin/includes/footer.php";
 ?>
