<?php
require_once __DIR__ . "/includes/CommonCode.php";
requireAdmin();
$lang = getLanguagePreference($conn);
$tr = fn($en, $fr) => $lang === "fr" ? $fr : $en;
$title = $tr("Admin - Stations", "Admin - Stations");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();
  $action = $_POST["action"] ?? "";

  if ($action === "create") {
    $serial = trim($_POST["serial"] ?? "");
    $name   = trim($_POST["name"] ?? "");
    $desc   = trim($_POST["description"] ?? "");

    if ($serial === "" || $name === "") {
      $msg = $tr("Serial and name required.", "Le numero de serie et le nom sont obligatoires.");
    } else {
      $stmt = mysqli_prepare($conn, "INSERT INTO stations (serial_number, name, description, user_id) VALUES (?,?,?,NULL)");
      mysqli_stmt_bind_param($stmt, "sss", $serial, $name, $desc);
      if (mysqli_stmt_execute($stmt)) {
        $msg = $tr("Station created (available).", "Station creee (disponible).");
      } else {
        $msg = $tr("Create failed (serial may exist).", "Creation impossible (le numero de serie existe peut-etre deja).");
      }
    }
  }

  if ($action === "assign") {
    $sid = (int)($_POST["station_id"] ?? 0);
    $uid = (int)($_POST["user_id"] ?? 0);
    if ($sid <= 0 || $uid <= 0) {
      $msg = $tr("Choose a valid station and user.", "Choisissez une station et un utilisateur valides.");
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE stations SET user_id=? WHERE station_id=?");
      mysqli_stmt_bind_param($stmt, "ii", $uid, $sid);
      mysqli_stmt_execute($stmt);
      $msg = $tr("Assigned.", "Attribuee.");
    }
  }

  if ($action === "edit") {
    $sid = (int)($_POST["station_id"] ?? 0);
    $serial = trim($_POST["serial"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $desc = trim($_POST["description"] ?? "");

    if ($sid <= 0) {
      $msg = $tr("Invalid station.", "Station invalide.");
    } else if ($serial === "" || $name === "") {
      $msg = $tr("Serial and name are required.", "Le numero de serie et le nom sont obligatoires.");
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE stations SET serial_number=?, name=?, description=? WHERE station_id=?");
      mysqli_stmt_bind_param($stmt, "sssi", $serial, $name, $desc, $sid);
      if (mysqli_stmt_execute($stmt)) {
        $msg = $tr("Station updated.", "Station mise a jour.");
      } else {
        $msg = $tr("Update failed (serial may already exist).", "Mise a jour impossible (le numero de serie existe peut-etre deja).");
      }
    }
  }

  if ($action === "unassign") {
    $sid = (int)($_POST["station_id"] ?? 0);
    if ($sid <= 0) {
      $msg = $tr("Invalid station.", "Station invalide.");
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE stations SET user_id=NULL WHERE station_id=?");
      mysqli_stmt_bind_param($stmt, "i", $sid);
      mysqli_stmt_execute($stmt);
      $msg = $tr("Unassigned (available).", "Desattribuee (disponible).");
    }
  }

  if ($action === "delete") {
    $sid = (int)($_POST["station_id"] ?? 0);
    if ($sid <= 0) {
      $msg = $tr("Invalid station.", "Station invalide.");
    } else {
      $stmt = mysqli_prepare($conn, "DELETE FROM stations WHERE station_id=?");
      mysqli_stmt_bind_param($stmt, "i", $sid);
      mysqli_stmt_execute($stmt);
      $msg = $tr("Deleted permanently.", "Supprimee definitivement.");
    }
  }
}

$users = [];
$res = mysqli_query($conn, "SELECT user_id, username, role FROM users ORDER BY role DESC, username");
while ($row = mysqli_fetch_assoc($res)) $users[] = $row;

$stations = [];
$res = mysqli_query($conn, "
  SELECT s.station_id, s.serial_number, s.name, s.description, s.user_id, u.username AS owner
  FROM stations s
  LEFT JOIN users u ON s.user_id=u.user_id
  ORDER BY (s.user_id IS NOT NULL) DESC, s.station_id DESC
");
while ($row = mysqli_fetch_assoc($res)) $stations[] = $row;

require_once __DIR__ . "/includes/header.php";
?>

<h1 class="h3 mb-3"><?= esc($tr("Admin - Stations", "Admin - Stations")) ?></h1>

<?php if ($msg !== ""): ?>
  <div class="alert alert-info"><?= esc($msg) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-3">
      <h2 class="h5"><?= esc($tr("Create station", "Creer une station")) ?></h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="create">

        <input class="form-control mb-2" name="serial" placeholder="<?= esc($tr("e.g. ST-4004-000", "ex. ST-4004-000")) ?>" required>
        <input class="form-control mb-2" name="name" placeholder="<?= esc($tr("Station name", "Nom de la station")) ?>" required>
        <input class="form-control mb-3" name="description" placeholder="<?= esc($tr("Description (optional)", "Description (optionnelle)")) ?>">

        <button class="btn btn-dark w-100"><?= esc($tr("Create", "Creer")) ?></button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card p-3">
      <h2 class="h5"><?= esc($tr("All stations", "Toutes les stations")) ?></h2>

      <?php if (count($stations) === 0): ?>
        <p class="empty-state"><?= esc($tr("No stations yet.", "Aucune station pour le moment.")) ?></p>
      <?php else: ?>
        <div class="management-card-list">
          <?php foreach ($stations as $s): ?>
            <?php $taken = ($s["user_id"] !== null); ?>
            <section class="management-card">
              <div class="management-card-header">
                <div>
                  <h3 class="management-card-title"><?= esc($s["name"]) ?></h3>
                  <p class="management-card-description"><?= esc($s["serial_number"]) ?></p>
                </div>
                <span class="badge rounded-pill <?= $taken ? "text-bg-danger" : "text-bg-success" ?>">
                  <?= $taken ? esc($tr("Taken", "Prise")) : esc($tr("Available", "Disponible")) ?>
                </span>
              </div>

              <div class="management-card-meta three-col">
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Station ID", "ID station")) ?></span>
                  <span class="management-meta-value"><?= (int)$s["station_id"] ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Owner", "Proprietaire")) ?></span>
                  <span class="management-meta-value"><?= $taken ? esc($s["owner"] ?? $tr("unknown", "inconnu")) : esc($tr("Nobody assigned", "Personne attribuee")) ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Description", "Description")) ?></span>
                  <span class="management-meta-value"><?= esc($s["description"] ?: $tr("No description added.", "Aucune description ajoutee.")) ?></span>
                </div>
              </div>

              <div class="management-card-actions">
                <form method="post" class="management-form">
                  <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="station_id" value="<?= (int)$s["station_id"] ?>">

                  <div class="management-form-grid">
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Serial", "Numero de serie")) ?></label>
                      <input class="form-control form-control-sm" name="serial" value="<?= esc($s["serial_number"]) ?>" required>
                    </div>
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Name", "Nom")) ?></label>
                      <input class="form-control form-control-sm" name="name" value="<?= esc($s["name"]) ?>" required>
                    </div>
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Description", "Description")) ?></label>
                      <input class="form-control form-control-sm" name="description" value="<?= esc($s["description"] ?? "") ?>">
                    </div>
                    <div>
                      <label class="collection-inline-label">&nbsp;</label>
                      <button class="btn btn-sm btn-outline-dark w-100"><?= esc($tr("Save station", "Enregistrer la station")) ?></button>
                    </div>
                  </div>
                </form>

                <form method="post" class="management-form">
                  <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                  <input type="hidden" name="action" value="assign">
                  <input type="hidden" name="station_id" value="<?= (int)$s["station_id"] ?>">

                  <div class="management-form-grid station-assign">
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Assign to user", "Attribuer a un utilisateur")) ?></label>
                      <select class="form-select form-select-sm" name="user_id" required>
                        <option value=""><?= esc($tr("Choose user...", "Choisir un utilisateur...")) ?></option>
                        <?php foreach ($users as $u): ?>
                          <option value="<?= (int)$u["user_id"] ?>"><?= esc($u["username"]) ?> (<?= esc($u["role"]) ?>)</option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="collection-inline-label">&nbsp;</label>
                      <button class="btn btn-sm btn-outline-dark w-100"><?= esc($tr("Assign station", "Attribuer la station")) ?></button>
                    </div>
                  </div>
                </form>

                <div class="management-toolbar">
                  <form method="post" onsubmit="return confirm('<?= esc($tr("Unassign station?", "Desattribuer la station ?")) ?>');">
                    <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                    <input type="hidden" name="action" value="unassign">
                    <input type="hidden" name="station_id" value="<?= (int)$s["station_id"] ?>">
                    <button class="btn btn-sm btn-outline-secondary"><?= esc($tr("Unassign", "Desattribuer")) ?></button>
                  </form>

                  <form method="post" onsubmit="return confirm('<?= esc($tr("Delete station permanently?", "Supprimer definitivement la station ?")) ?>');">
                    <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="station_id" value="<?= (int)$s["station_id"] ?>">
                    <button class="btn btn-sm btn-outline-danger"><?= esc($tr("Delete station", "Supprimer la station")) ?></button>
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
