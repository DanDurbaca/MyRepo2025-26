<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
requireLogin();
$lang = getLanguagePreference($conn);
$tr = fn($en, $fr) => $lang === "fr" ? $fr : $en;
$title = $tr("Stations", "Stations");

$msg = "";
$editId = (int)($_GET["edit"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();
  $action = $_POST["action"] ?? "";

  // Register by serial (only if available: user_id IS NULL)
  if ($action === "register") {
    $serial = trim($_POST["serial"] ?? "");
    if ($serial === "") {
      $msg = $tr("Please type a serial number.", "Veuillez saisir un numero de serie.");
    } else {
      $stmt = mysqli_prepare($conn, "SELECT station_id, user_id FROM stations WHERE serial_number=?");
      mysqli_stmt_bind_param($stmt, "s", $serial);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);

      if ($row = mysqli_fetch_assoc($res)) {
        if ($row["user_id"] !== null) {
          $msg = $tr("This station is already taken.", "Cette station est deja prise.");
        } else {
          $sid = (int)$row["station_id"];
          $stmt2 = mysqli_prepare($conn, "UPDATE stations SET user_id=? WHERE station_id=? AND user_id IS NULL");
          mysqli_stmt_bind_param($stmt2, "ii", $_SESSION["user_id"], $sid);
          mysqli_stmt_execute($stmt2);
          $msg = $tr("Station registered to your account.", "Station enregistree sur votre compte.");
        }
      } else {
        $msg = $tr("Serial number not found.", "Numero de serie introuvable.");
      }
    }
  }

  // Save station edits (only if it belongs to you)
  if ($action === "save") {
    $sid = (int)($_POST["station_id"] ?? 0);
    $name = trim($_POST["name"] ?? "");
    $desc = trim($_POST["description"] ?? "");

    if ($sid <= 0 || $name === "") {
      $msg = $tr("Name is required.", "Le nom est obligatoire.");
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE stations SET name=?, description=? WHERE station_id=? AND user_id=?");
      mysqli_stmt_bind_param($stmt, "ssii", $name, $desc, $sid, $_SESSION["user_id"]);
      mysqli_stmt_execute($stmt);
      header("Location: " . appUrl("/user/stations.php"));
      exit();
    }
  }

  // Unassign station (your Delete meaning A)
  if ($action === "unassign") {
    $sid = (int)($_POST["station_id"] ?? 0);
    $stmt = mysqli_prepare($conn, "UPDATE stations SET user_id=NULL WHERE station_id=? AND user_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $sid, $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);
    $msg = $tr("Station removed and is available again.", "La station a ete retiree et redevient disponible.");
  }
}

// Load my stations
$myStations = [];
$stmt = mysqli_prepare($conn, "SELECT station_id, serial_number, name, description FROM stations WHERE user_id=? ORDER BY station_id DESC");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $myStations[] = $row;

require_once __DIR__ . "/../admin/includes/header.php";
?>

<h1 class="h3 mb-3"><?= esc($tr("Stations", "Stations")) ?></h1>

<?php if ($msg !== ""): ?>
  <div class="alert alert-info"><?= esc($msg) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-3">
      <h2 class="h5"><?= esc($tr("Register station", "Enregistrer une station")) ?></h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="register">

        <label class="form-label"><?= esc($tr("Serial number", "Numero de serie")) ?></label>
        <input
  class="form-control mb-2"
  name="serial"
  placeholder="<?= esc($tr("e.g. ST-4004-000", "ex. ST-4004-000")) ?>"
  required
>

        <button class="btn btn-dark w-100"><?= esc($tr("Register", "Enregistrer")) ?></button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card p-3">
      <h2 class="h5"><?= esc($tr("My stations", "Mes stations")) ?></h2>

      <?php if (count($myStations) === 0): ?>
        <p class="empty-state"><?= esc($tr("No stations yet.", "Aucune station pour le moment.")) ?></p>
      <?php else: ?>
        <div class="management-card-list">
          <?php foreach ($myStations as $s): ?>
            <?php $sid = (int)$s["station_id"]; $isEdit = ($editId === $sid); ?>
            <section class="management-card">
              <div class="management-card-header">
                <div>
                  <h3 class="management-card-title"><?= esc($s["name"]) ?></h3>
                  <p class="management-card-description"><?= esc($s["serial_number"]) ?></p>
                </div>
                <span class="badge rounded-pill text-bg-dark"><?= esc($tr("Owned by you", "Vous appartient")) ?></span>
              </div>

              <div class="management-card-meta three-col">
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Station ID", "ID station")) ?></span>
                  <span class="management-meta-value"><?= $sid ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Serial number", "Numero de serie")) ?></span>
                  <span class="management-meta-value"><?= esc($s["serial_number"]) ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc($tr("Description", "Description")) ?></span>
                  <span class="management-meta-value"><?= esc($s["description"] ?: $tr("No description added.", "Aucune description ajoutee.")) ?></span>
                </div>
              </div>

              <div class="management-card-actions">
                <?php if ($isEdit): ?>
                  <form method="post" class="management-form">
                    <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="station_id" value="<?= $sid ?>">

                    <div class="management-form-grid two-fields">
                      <div>
                        <label class="collection-inline-label"><?= esc($tr("Station name", "Nom de la station")) ?></label>
                        <input class="form-control form-control-sm" name="name" value="<?= esc($s["name"]) ?>" required>
                      </div>
                      <div>
                        <label class="collection-inline-label"><?= esc($tr("Description", "Description")) ?></label>
                        <input class="form-control form-control-sm" name="description" value="<?= esc($s["description"] ?? "") ?>">
                      </div>
                      <div>
                        <label class="collection-inline-label">&nbsp;</label>
                        <button class="btn btn-sm btn-outline-dark w-100"><?= esc($tr("Save changes", "Enregistrer les modifications")) ?></button>
                      </div>
                    </div>
                  </form>

                  <div class="management-toolbar">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= esc(appUrl('/user/stations.php')) ?>"><?= esc($tr("Cancel", "Annuler")) ?></a>
                  </div>
                <?php else: ?>
                  <div class="management-toolbar">
                    <a class="btn btn-sm btn-outline-dark" href="<?= esc(appUrl('/user/stations.php')) ?>?edit=<?= $sid ?>"><?= esc($tr("Edit", "Modifier")) ?></a>

                    <form method="post" onsubmit="return confirm('<?= esc($tr("Remove this station? It becomes available again.", "Retirer cette station ? Elle redeviendra disponible.")) ?>');">
                      <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                      <input type="hidden" name="action" value="unassign">
                      <input type="hidden" name="station_id" value="<?= $sid ?>">
                      <button class="btn btn-sm btn-outline-danger"><?= esc($tr("Remove station", "Retirer la station")) ?></button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../admin/includes/footer.php";
 ?>
