<?php
require_once __DIR__ . "/includes/CommonCode.php";
requireAdmin();

$lang = getLanguagePreference($conn);
$tr = fn($en, $fr) => $lang === "fr" ? $fr : $en;

$title = $tr("Admin Measurements", "Mesures admin");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();
  $action = $_POST["action"] ?? "";
  if ($action === "delete") {
    $mid = (int)($_POST["measurement_id"] ?? 0);
    if ($mid <= 0) {
      $msg = $tr("Invalid measurement.", "Mesure invalide.");
    } else {
      $stmt = mysqli_prepare($conn, "DELETE FROM measurements WHERE measurement_id=?");
      mysqli_stmt_bind_param($stmt, "i", $mid);
      mysqli_stmt_execute($stmt);
      $msg = $tr("Measurement deleted.", "Mesure supprimee.");
    }
  }
}

$stations = [];
$res = mysqli_query($conn, "SELECT station_id, serial_number, name FROM stations ORDER BY name");
while ($row = mysqli_fetch_assoc($res)) {
  $stations[] = $row;
}

$station_id = (int)($_GET["station_id"] ?? 0);
$start = $_GET["start"] ?? "";
$end = $_GET["end"] ?? "";

$rows = [];
if (isset($_GET["filter"])) {
  $startSql = toSqlDateTime($start);
  $endSql = toSqlDateTime($end);

  if ($station_id > 0 && $startSql !== "" && $endSql !== "") {
    $stmt = mysqli_prepare($conn, "
      SELECT measurement_id, measured_at, temperature, humidity, pressure, light, gas
      FROM measurements
      WHERE station_id=? AND measured_at BETWEEN ? AND ?
      ORDER BY measured_at DESC
      LIMIT 200
    ");
    mysqli_stmt_bind_param($stmt, "iss", $station_id, $startSql, $endSql);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
      $rows[] = $r;
    }
  }
}

require_once __DIR__ . "/includes/header.php";
?>

<h1 class="h3 mb-3"><?= esc($tr("Admin - Measurements", "Admin - Mesures")) ?></h1>

<?php if ($msg !== ""): ?>
  <div class="alert alert-info"><?= esc($msg) ?></div>
<?php endif; ?>

<div class="card p-3 mb-3">
  <h2 class="h5"><?= esc($tr("Filter", "Filtre")) ?></h2>
  <form method="get" class="row g-3 align-items-end">
    <input type="hidden" name="filter" value="1">

    <div class="col-md-4">
      <label class="form-label"><?= esc($tr("Station", "Station")) ?></label>
      <select class="form-select" name="station_id" required>
        <option value="0"><?= esc($tr("-- choose --", "-- choisir --")) ?></option>
        <?php foreach ($stations as $s): ?>
          <option value="<?= (int)$s["station_id"] ?>" <?= ((int)$s["station_id"] === $station_id) ? "selected" : "" ?>>
            <?= esc($s["name"]) ?> (<?= esc($s["serial_number"]) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label"><?= esc($tr("Start", "Debut")) ?></label>
      <input class="form-control" type="datetime-local" name="start" value="<?= esc($start) ?>" required>
    </div>

    <div class="col-md-3">
      <label class="form-label"><?= esc($tr("End", "Fin")) ?></label>
      <input class="form-control" type="datetime-local" name="end" value="<?= esc($end) ?>" required>
    </div>

    <div class="col-md-2">
      <button class="btn btn-dark w-100"><?= esc($tr("Show", "Afficher")) ?></button>
    </div>
  </form>
</div>

<div class="card p-3">
  <h2 class="h5"><?= esc($tr("Results", "Resultats")) ?></h2>

  <?php if (isset($_GET["filter"])): ?>
    <p class="text-muted"><?= esc($tr("Showing up to 200 rows.", "Affichage de 200 lignes maximum.")) ?></p>

    <?php if (count($rows) === 0): ?>
      <p class="text-muted mb-0"><?= esc($tr("No data.", "Aucune donnee.")) ?></p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th><?= esc($tr("Time", "Heure")) ?></th>
              <th><?= esc($tr("Temp", "Temp")) ?></th>
              <th><?= esc($tr("Hum", "Hum")) ?></th>
              <th><?= esc($tr("Press", "Press")) ?></th>
              <th><?= esc($tr("Light", "Lumiere")) ?></th>
              <th><?= esc($tr("Gas", "Gaz")) ?></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= esc($r["measured_at"]) ?></td>
                <td><?= esc((string)$r["temperature"]) ?></td>
                <td><?= esc((string)$r["humidity"]) ?></td>
                <td><?= esc((string)$r["pressure"]) ?></td>
                <td><?= esc((string)$r["light"]) ?></td>
                <td><?= esc((string)$r["gas"]) ?></td>
                <td>
                  <form method="post" onsubmit="return confirm('<?= esc($tr("Delete this measurement?", "Supprimer cette mesure ?")) ?>');">
                    <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="measurement_id" value="<?= (int)$r["measurement_id"] ?>">
                    <button class="btn btn-sm btn-outline-danger"><?= esc($tr("Delete", "Supprimer")) ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <p class="text-muted mb-0"><?= esc($tr("Choose station and time range.", "Choisissez la station et l'intervalle de temps.")) ?></p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
