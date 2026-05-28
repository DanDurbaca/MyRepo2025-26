<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
requireLogin();
$lang = getLanguagePreference($conn);
$tr = fn($en, $fr) => $lang === "fr" ? $fr : $en;
$title = $tr("Measurements", "Mesures");

$msg = "";
$rows = [];

// load my stations
$stations = [];
$stmt = mysqli_prepare($conn, "SELECT station_id, name, serial_number FROM stations WHERE user_id=? ORDER BY name");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $stations[] = $row;

$station_id = (int)($_GET["station_id"] ?? 0);
$start = $_GET["start"] ?? "";
$end   = $_GET["end"] ?? "";

if (isset($_GET["filter"])) {
  $startSql = toSqlDateTime($start);
  $endSql   = toSqlDateTime($end);

  if ($station_id <= 0) {
    $msg = $tr("Please select a station.", "Veuillez selectionner une station.");
  } else if ($startSql === "" || $endSql === "") {
    $msg = $tr("Please select start and end date/time.", "Veuillez selectionner la date/heure de debut et de fin.");
  } else {
    // make sure station belongs to this user
    $chk = mysqli_prepare($conn, "SELECT station_id FROM stations WHERE station_id=? AND user_id=?");
    mysqli_stmt_bind_param($chk, "ii", $station_id, $_SESSION["user_id"]);
    mysqli_stmt_execute($chk);
    $chkRes = mysqli_stmt_get_result($chk);

    if (!mysqli_fetch_assoc($chkRes)) {
      $msg = $tr("Not allowed.", "Non autorise.");
    } else {
      $stmt = mysqli_prepare($conn, "
        SELECT measured_at, temperature, humidity, pressure, light, gas
        FROM measurements
        WHERE station_id=? AND measured_at BETWEEN ? AND ?
        ORDER BY measured_at DESC
        LIMIT 500
      ");
      mysqli_stmt_bind_param($stmt, "iss", $station_id, $startSql, $endSql);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    }
  }
}

require_once __DIR__ . "/../admin/includes/header.php";
?>

<h1 class="h3 mb-3"><?= esc($tr("Measurements", "Mesures")) ?></h1>

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

  <?php if (isset($_GET["filter"]) && $msg === ""): ?>
    <p class="text-muted"><?= esc($tr("Rows", "Lignes")) ?>: <?= count($rows) ?> (max 500)</p>

    <?php if (count($rows) === 0): ?>
      <p class="text-muted mb-0"><?= esc($tr("No data in this range.", "Aucune donnee dans cette periode.")) ?></p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th><?= esc($tr("Time", "Heure")) ?></th><th><?= esc($tr("Temp", "Temp")) ?></th><th><?= esc($tr("Hum", "Hum")) ?></th><th><?= esc($tr("Press", "Press")) ?></th><th>Light</th><th>Gas</th>
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
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <p class="text-muted mb-0"><?= esc($tr("Choose a station and time range.", "Choisissez une station et une periode.")) ?></p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . "/../admin/includes/footer.php";
 ?>
