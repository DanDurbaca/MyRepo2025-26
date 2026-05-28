<?php
require_once __DIR__ . "/includes/CommonCode.php";
requireAdmin();
$lang = getLanguagePreference($conn);
$tr = fn($en, $fr) => $lang === "fr" ? $fr : $en;
$title = $tr("Admin - Users", "Admin - Utilisateurs");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();
  $action = $_POST["action"] ?? "";

  if ($action === "create") {
    $username  = trim($_POST["username"] ?? "");
    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $pass      = $_POST["password"] ?? "";
    $role      = ($_POST["role"] ?? "user") === "admin" ? "admin" : "user";

    if ($username === "" || $full_name === "" || $email === "" || $pass === "") {
      $msg = $tr("Fill all fields.", "Remplissez tous les champs.");
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = mysqli_prepare($conn, "INSERT INTO users (username, full_name, email, password, role) VALUES (?,?,?,?,?)");
      mysqli_stmt_bind_param($stmt, "sssss", $username, $full_name, $email, $hash, $role);
      if (mysqli_stmt_execute($stmt)) {
        $msg = $tr("User created.", "Utilisateur cree.");
      } else {
        $msg = $tr("Create failed (username/email may exist).", "Creation impossible (nom d'utilisateur/e-mail existe peut-etre deja).");
      }
    }
  }

  if ($action === "edit") {
    $uid = (int)($_POST["user_id"] ?? 0);
    $username = trim($_POST["username"] ?? "");
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $role = ($_POST["role"] ?? "user") === "admin" ? "admin" : "user";

    if ($uid <= 0) {
      $msg = $tr("Invalid user.", "Utilisateur invalide.");
    } else if ($username === "" || $full_name === "" || $email === "") {
      $msg = $tr("Username, full name, and email are required.", "Le nom d'utilisateur, le nom complet et l'e-mail sont obligatoires.");
    } else if ($uid === (int)$_SESSION["user_id"]) {
      $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, full_name=?, email=? WHERE user_id=?");
      mysqli_stmt_bind_param($stmt, "sssi", $username, $full_name, $email, $uid);
      if (mysqli_stmt_execute($stmt)) {
        $_SESSION["username"] = $username;
        $msg = $tr("Your account details were updated. Role unchanged.", "Les details de votre compte ont ete mis a jour. Role inchange.");
      } else {
        $msg = $tr("Update failed (username/email may exist).", "Mise a jour impossible (nom d'utilisateur/e-mail existe peut-etre deja).");
      }
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, full_name=?, email=?, role=? WHERE user_id=?");
      mysqli_stmt_bind_param($stmt, "ssssi", $username, $full_name, $email, $role, $uid);
      if (mysqli_stmt_execute($stmt)) {
        $msg = $tr("User updated.", "Utilisateur mis a jour.");
      } else {
        $msg = $tr("Update failed (username/email may exist).", "Mise a jour impossible (nom d'utilisateur/e-mail existe peut-etre deja).");
      }
    }
  }

  if ($action === "reset") {
    $uid = (int)($_POST["user_id"] ?? 0);
    $new = $_POST["new_password"] ?? "";
    if ($uid <= 0) {
      $msg = $tr("Invalid user.", "Utilisateur invalide.");
    } else if ($new === "") {
      $msg = $tr("Password is empty.", "Le mot de passe est vide.");
    } else {
      $hash = password_hash($new, PASSWORD_DEFAULT);
      $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE user_id=?");
      mysqli_stmt_bind_param($stmt, "si", $hash, $uid);
      mysqli_stmt_execute($stmt);
      $msg = $tr("Password reset.", "Mot de passe reinitialise.");
    }
  }

  if ($action === "delete") {
    $uid = (int)($_POST["user_id"] ?? 0);
    if ($uid <= 0) {
      $msg = $tr("Invalid user.", "Utilisateur invalide.");
    } else if ($uid === (int)$_SESSION["user_id"]) {
      $msg = $tr("You cannot delete yourself while logged in.", "Vous ne pouvez pas vous supprimer pendant que vous etes connecte.");
    } else {
      mysqli_begin_transaction($conn);

      $stmt = mysqli_prepare($conn, "UPDATE stations SET user_id=NULL WHERE user_id=?");
      mysqli_stmt_bind_param($stmt, "i", $uid);
      $ok1 = mysqli_stmt_execute($stmt);

      $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id=?");
      mysqli_stmt_bind_param($stmt, "i", $uid);
      $ok2 = mysqli_stmt_execute($stmt);

      if ($ok1 && $ok2) {
        mysqli_commit($conn);
        $msg = $tr("User deleted (stations became available).", "Utilisateur supprime (les stations sont redevenues disponibles).");
      } else {
        mysqli_rollback($conn);
        $msg = $tr("Delete failed.", "Suppression impossible.");
      }
    }
  }
}

$users = [];
$res = mysqli_query($conn, "SELECT user_id, username, full_name, email, role FROM users ORDER BY role DESC, username");
while ($row = mysqli_fetch_assoc($res)) $users[] = $row;

require_once __DIR__ . "/includes/header.php";
?>

<h1 class="h3 mb-3"><?= esc($tr("Admin - Users", "Admin - Utilisateurs")) ?></h1>

<?php if ($msg !== ""): ?>
  <div class="alert alert-info"><?= esc($msg) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-3">
      <h2 class="h5"><?= esc($tr("Create user", "Creer un utilisateur")) ?></h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="create">

        <input class="form-control mb-2" name="username" placeholder="<?= esc($tr("Username", "Nom d'utilisateur")) ?>" required>
        <input class="form-control mb-2" name="full_name" placeholder="<?= esc($tr("Full name", "Nom complet")) ?>" required>
        <input class="form-control mb-2" type="email" name="email" placeholder="<?= esc($tr("Email", "E-mail")) ?>" required>
        <input class="form-control mb-2" type="password" name="password" placeholder="<?= esc($tr("Password", "Mot de passe")) ?>" required>

        <select class="form-select mb-3" name="role">
          <option value="user"><?= esc($tr("user", "utilisateur")) ?></option>
          <option value="admin"><?= esc($tr("admin", "admin")) ?></option>
        </select>

        <button class="btn btn-dark w-100"><?= esc($tr("Create", "Creer")) ?></button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card p-3">
      <h2 class="h5"><?= esc($tr("All users", "Tous les utilisateurs")) ?></h2>

      <?php if (count($users) === 0): ?>
        <p class="empty-state"><?= esc($tr("No users yet.", "Aucun utilisateur pour le moment.")) ?></p>
      <?php else: ?>
        <div class="management-card-list">
          <?php foreach ($users as $u): ?>
            <?php $isCurrentUser = ((int)$u["user_id"] === (int)$_SESSION["user_id"]); ?>
            <section class="management-card">
              <div class="management-card-header">
                <div>
                  <h3 class="management-card-title"><?= esc($u["username"]) ?></h3>
                  <p class="management-card-description"><?= esc($u["full_name"]) ?></p>
                </div>
                <span class="badge rounded-pill <?= $u["role"] === "admin" ? "text-bg-dark" : "text-bg-secondary" ?>">
                  <?= esc($u["role"]) ?>
                </span>
              </div>

              <div class="management-card-meta">
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("User ID", "ID utilisateur")) ?></span>
                  <span class="management-meta-value"><?= (int)$u["user_id"] ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Full name", "Nom complet")) ?></span>
                  <span class="management-meta-value"><?= esc($u["full_name"]) ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Email", "E-mail")) ?></span>
                  <span class="management-meta-value"><?= esc($u["email"]) ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Status", "Statut")) ?></span>
                  <span class="management-meta-value"><?= $isCurrentUser ? esc($tr("Current account", "Compte actuel")) : esc($tr("Managed account", "Compte gere")) ?></span>
                </div>
              </div>

              <div class="management-card-actions">
                <form method="post" class="management-form">
                  <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="user_id" value="<?= (int)$u["user_id"] ?>">
                  <div class="management-form-grid">
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Username", "Nom d'utilisateur")) ?></label>
                      <input class="form-control form-control-sm" name="username" value="<?= esc($u["username"]) ?>" required>
                    </div>
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Full name", "Nom complet")) ?></label>
                      <input class="form-control form-control-sm" name="full_name" value="<?= esc($u["full_name"]) ?>" required>
                    </div>
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Email", "E-mail")) ?></label>
                      <input class="form-control form-control-sm" type="email" name="email" value="<?= esc($u["email"]) ?>" required>
                    </div>
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Role", "Role")) ?></label>
                      <select class="form-select form-select-sm" name="role">
                        <option value="user" <?= $u["role"] === "user" ? "selected" : "" ?>><?= esc($tr("user", "utilisateur")) ?></option>
                        <option value="admin" <?= $u["role"] === "admin" ? "selected" : "" ?>><?= esc($tr("admin", "admin")) ?></option>
                      </select>
                    </div>
                    <div>
                      <label class="collection-inline-label">&nbsp;</label>
                      <button class="btn btn-sm btn-outline-dark w-100"><?= esc($tr("Save user", "Enregistrer l'utilisateur")) ?></button>
                    </div>
                  </div>
                </form>

                <form method="post" class="management-form">
                  <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                  <input type="hidden" name="action" value="reset">
                  <input type="hidden" name="user_id" value="<?= (int)$u["user_id"] ?>">
                  <div class="management-form-grid two-fields">
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("New password", "Nouveau mot de passe")) ?></label>
                      <input class="form-control form-control-sm" type="password" name="new_password" placeholder="<?= esc($tr("Enter a new password", "Saisissez un nouveau mot de passe")) ?>" required>
                    </div>
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Account", "Compte")) ?></label>
                      <input class="form-control form-control-sm" value="<?= esc($u["username"]) ?>" readonly>
                    </div>
                    <div>
                      <label class="collection-inline-label">&nbsp;</label>
                      <button class="btn btn-sm btn-outline-secondary w-100"><?= esc($tr("Reset password", "Reinitialiser le mot de passe")) ?></button>
                    </div>
                  </div>
                </form>

                <div class="management-toolbar">
                  <form method="post" onsubmit="return confirm('<?= esc($tr("Delete user? Their stations become available.", "Supprimer l'utilisateur ? Ses stations redeviendront disponibles.")) ?>');">
                    <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="<?= (int)$u["user_id"] ?>">
                    <button class="btn btn-sm btn-outline-danger" <?= $isCurrentUser ? "disabled" : "" ?>><?= esc($tr("Delete user", "Supprimer l'utilisateur")) ?></button>
                  </form>
                </div>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
