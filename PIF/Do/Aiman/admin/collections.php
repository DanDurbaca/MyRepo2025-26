<?php
require_once __DIR__ . "/includes/CommonCode.php";
requireAdmin();

$lang = getLanguagePreference($conn);
$tr = fn($en, $fr) => $lang === "fr" ? $fr : $en;

$title = $tr("Admin Collections", "Collections admin");
$msg = "";
$currentUserId = (int)$_SESSION["user_id"];

$stations = [];
$res = mysqli_query($conn, "
  SELECT s.station_id, s.name, s.serial_number, u.username AS owner_username
  FROM stations s
  LEFT JOIN users u ON u.user_id = s.user_id
  ORDER BY s.name
");
while ($row = mysqli_fetch_assoc($res)) {
  $stations[] = $row;
}

$friends = [];
$stmt = mysqli_prepare($conn, "
  SELECT u.user_id, u.username
  FROM friendships f
  INNER JOIN users u ON u.user_id = f.friend_user_id
  WHERE f.user_id = ?
  ORDER BY u.username
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
  $friends[] = $row;
}

$selectedCollectionId = (int)($_GET["view"] ?? 0);
$selectedCollection = null;
$selectedMeasurements = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();
  $action = $_POST["action"] ?? "";

  if ($action === "create") {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $stationId = (int)($_POST["station_id"] ?? 0);
    $start = toSqlDateTime($_POST["start"] ?? "");
    $end = toSqlDateTime($_POST["end"] ?? "");

    if ($name === "") {
      $msg = $tr("Collection name is required.", "Le nom de la collection est obligatoire.");
    } elseif ($stationId <= 0) {
      $msg = $tr("Please choose a station.", "Veuillez choisir une station.");
    } elseif ($start === "" || $end === "") {
      $msg = $tr("Please choose start and end date/time.", "Veuillez choisir la date et l'heure de debut et de fin.");
    } elseif ($start > $end) {
      $msg = $tr("Start date/time must be before end date/time.", "La date/heure de debut doit etre avant la date/heure de fin.");
    } else {
      $measurementIds = [];
      $stmt = mysqli_prepare($conn, "
        SELECT measurement_id
        FROM measurements
        WHERE station_id = ? AND measured_at BETWEEN ? AND ?
        ORDER BY measured_at
      ");
      mysqli_stmt_bind_param($stmt, "iss", $stationId, $start, $end);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      while ($row = mysqli_fetch_assoc($res)) {
        $measurementIds[] = (int)$row["measurement_id"];
      }

      if (count($measurementIds) === 0) {
        $msg = $tr("No measurements found in that range.", "Aucune mesure trouvee dans cet intervalle.");
      } else {
        mysqli_begin_transaction($conn);

        try {
          $stmt = mysqli_prepare($conn, "
            INSERT INTO collections (name, description, user_id, station_id, start_at, end_at)
            VALUES (?, ?, ?, ?, ?, ?)
          ");
          mysqli_stmt_bind_param($stmt, "ssiiss", $name, $description, $currentUserId, $stationId, $start, $end);
          mysqli_stmt_execute($stmt);
          $collectionId = (int)mysqli_insert_id($conn);

          $stmt = mysqli_prepare($conn, "
            INSERT INTO collection_measurements (collection_id, measurement_id)
            VALUES (?, ?)
          ");

          foreach ($measurementIds as $measurementId) {
            mysqli_stmt_bind_param($stmt, "ii", $collectionId, $measurementId);
            mysqli_stmt_execute($stmt);
          }

          mysqli_commit($conn);
          $msg = $tr("Collection created.", "Collection creee.");
        } catch (Throwable $e) {
          mysqli_rollback($conn);
          $msg = $tr("Could not create the collection.", "Impossible de creer la collection.");
        }
      }
    }
  }

  if ($action === "rename") {
    $collectionId = (int)($_POST["collection_id"] ?? 0);
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name === "") {
      $msg = $tr("Collection name is required.", "Le nom de la collection est obligatoire.");
    } else {
      $stmt = mysqli_prepare($conn, "
        UPDATE collections
        SET name = ?, description = ?
        WHERE collection_id = ?
      ");
      mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $collectionId);
      mysqli_stmt_execute($stmt);
      $msg = (mysqli_stmt_affected_rows($stmt) >= 0)
        ? $tr("Collection updated.", "Collection mise a jour.")
        : $tr("Could not update the collection.", "Impossible de mettre a jour la collection.");
    }
  }

  if ($action === "delete") {
    $collectionId = (int)($_POST["collection_id"] ?? 0);
    $stmt = mysqli_prepare($conn, "DELETE FROM collections WHERE collection_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $collectionId);
    mysqli_stmt_execute($stmt);
    $msg = (mysqli_stmt_affected_rows($stmt) === 1)
      ? $tr("Collection deleted.", "Collection supprimee.")
      : $tr("Collection not found.", "Collection introuvable.");
  }

  if ($action === "share") {
    $collectionId = (int)($_POST["collection_id"] ?? 0);
    $friendId = (int)($_POST["friend_user_id"] ?? 0);

    if ($friendId <= 0) {
      $msg = $tr("Please choose a friend.", "Veuillez choisir un ami.");
    } else {
      $stmt = mysqli_prepare($conn, "
        SELECT 1
        FROM collections c
        INNER JOIN friendships f ON f.user_id = c.user_id AND f.friend_user_id = ?
        WHERE c.collection_id = ? AND c.user_id = ?
        LIMIT 1
      ");
      mysqli_stmt_bind_param($stmt, "iii", $friendId, $collectionId, $currentUserId);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);

      if (!mysqli_fetch_assoc($res)) {
        $msg = $tr(
          "Admins can only share collections they created themselves with their friends.",
          "Les admins peuvent seulement partager les collections qu'ils ont creees eux-memes avec leurs amis."
        );
      } else {
        $stmt = mysqli_prepare($conn, "
          INSERT IGNORE INTO collection_shares (collection_id, user_id)
          VALUES (?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "ii", $collectionId, $friendId);
        mysqli_stmt_execute($stmt);
        $msg = $tr("Collection shared.", "Collection partagee.");
      }
    }
  }

  if ($action === "unshare") {
    $collectionId = (int)($_POST["collection_id"] ?? 0);
    $friendId = (int)($_POST["friend_user_id"] ?? 0);

    $stmt = mysqli_prepare($conn, "
      DELETE cs
      FROM collection_shares cs
      INNER JOIN collections c ON c.collection_id = cs.collection_id
      WHERE cs.collection_id = ? AND cs.user_id = ? AND c.user_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "iii", $collectionId, $friendId, $currentUserId);
    mysqli_stmt_execute($stmt);
    $msg = (mysqli_stmt_affected_rows($stmt) === 1)
      ? $tr("Sharing removed.", "Partage supprime.")
      : $tr("Share not found.", "Partage introuvable.");
  }
}

$collections = [];
$res = mysqli_query($conn, "
  SELECT
    c.collection_id,
    c.name,
    c.description,
    c.user_id,
    c.start_at,
    c.end_at,
    s.name AS station_name,
    s.serial_number,
    owner.username AS owner_username,
    COUNT(DISTINCT cm.measurement_id) AS measurement_count,
    GROUP_CONCAT(DISTINCT shared_user.username ORDER BY shared_user.username SEPARATOR ', ') AS shared_with
  FROM collections c
  INNER JOIN stations s ON s.station_id = c.station_id
  INNER JOIN users owner ON owner.user_id = c.user_id
  LEFT JOIN collection_measurements cm ON cm.collection_id = c.collection_id
  LEFT JOIN collection_shares cs ON cs.collection_id = c.collection_id
  LEFT JOIN users shared_user ON shared_user.user_id = cs.user_id
  GROUP BY c.collection_id, c.name, c.description, c.user_id, c.start_at, c.end_at, s.name, s.serial_number, owner.username
  ORDER BY c.collection_id DESC
");
while ($row = mysqli_fetch_assoc($res)) {
  $collections[] = $row;
}

if ($selectedCollectionId > 0) {
  $stmt = mysqli_prepare($conn, "
    SELECT
      c.collection_id,
      c.name,
      c.description,
      c.start_at,
      c.end_at,
      owner.username AS owner_username,
      s.name AS station_name,
      s.serial_number,
      COUNT(DISTINCT cm.measurement_id) AS measurement_count
    FROM collections c
    INNER JOIN users owner ON owner.user_id = c.user_id
    INNER JOIN stations s ON s.station_id = c.station_id
    LEFT JOIN collection_measurements cm ON cm.collection_id = c.collection_id
    WHERE c.collection_id = ?
    GROUP BY c.collection_id, c.name, c.description, c.start_at, c.end_at, owner.username, s.name, s.serial_number
    LIMIT 1
  ");
  mysqli_stmt_bind_param($stmt, "i", $selectedCollectionId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $selectedCollection = mysqli_fetch_assoc($res) ?: null;

  if ($selectedCollection) {
    $stmt = mysqli_prepare($conn, "
      SELECT
        m.measured_at,
        m.temperature,
        m.humidity,
        m.pressure,
        m.light,
        m.gas
      FROM collection_measurements cm
      INNER JOIN measurements m ON m.measurement_id = cm.measurement_id
      WHERE cm.collection_id = ?
      ORDER BY m.measured_at DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $selectedCollectionId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
      $selectedMeasurements[] = $row;
    }
  } else {
    $msg = $tr("Collection not found.", "Collection introuvable.");
  }
}

require_once __DIR__ . "/includes/header.php";
?>

<h1 class="h3 mb-3"><?= esc($tr("Admin - Collections", "Admin - Collections")) ?></h1>

<?php if ($msg !== ""): ?>
  <div class="alert alert-info"><?= esc($msg) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-3">
      <h2 class="h5"><?= esc($tr("Create collection", "Creer une collection")) ?></h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="create">

        <div class="mb-2">
          <label class="form-label"><?= esc($tr("Collection name", "Nom de la collection")) ?></label>
          <input class="form-control" name="name" required>
        </div>

        <div class="mb-2">
          <label class="form-label"><?= esc($tr("Description", "Description")) ?></label>
          <input class="form-control" name="description">
        </div>

        <div class="mb-2">
          <label class="form-label"><?= esc($tr("Station", "Station")) ?></label>
          <select class="form-select" name="station_id" required>
            <option value=""><?= esc($tr("-- choose --", "-- choisir --")) ?></option>
            <?php foreach ($stations as $station): ?>
              <option value="<?= (int)$station["station_id"] ?>">
                <?= esc($station["name"]) ?> (<?= esc($station["serial_number"]) ?>)
                <?= $station["owner_username"]
                  ? " - " . esc($tr("owner", "proprietaire")) . ": " . esc($station["owner_username"])
                  : " - " . esc($tr("available", "disponible")) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label"><?= esc($tr("Start", "Debut")) ?></label>
          <input class="form-control" type="datetime-local" name="start" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><?= esc($tr("End", "Fin")) ?></label>
          <input class="form-control" type="datetime-local" name="end" required>
        </div>

        <button class="btn btn-dark w-100"><?= esc($tr("Create collection", "Creer la collection")) ?></button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card p-3">
      <h2 class="h5"><?= esc($tr("All collections", "Toutes les collections")) ?></h2>

      <?php if (count($collections) === 0): ?>
        <p class="empty-state"><?= esc($tr("No collections yet.", "Aucune collection pour le moment.")) ?></p>
      <?php else: ?>
        <div class="collection-card-list">
          <?php foreach ($collections as $collection): ?>
            <?php $isMine = ((int)$collection["user_id"] === $currentUserId); ?>
            <section class="collection-card">
              <div class="collection-card-header">
                <div>
                  <h3 class="collection-card-title mb-1"><?= esc($collection["name"]) ?></h3>
                  <p class="collection-card-description mb-0">
                    <?= esc($collection["description"] ?: $tr("No description added.", "Aucune description ajoutee.")) ?>
                  </p>
                </div>
                <div class="collection-card-badge">
                  <?= (int)$collection["measurement_count"] ?> <?= esc($tr("rows", "lignes")) ?>
                </div>
              </div>

              <div class="collection-card-meta">
                <div class="collection-meta-item">
                  <span class="collection-meta-label"><?= esc($tr("Station", "Station")) ?></span>
                  <span class="collection-meta-value">
                    <?= esc($collection["station_name"]) ?> (<?= esc($collection["serial_number"]) ?>)
                  </span>
                </div>
                <div class="collection-meta-item">
                  <span class="collection-meta-label"><?= esc($tr("Owner", "Proprietaire")) ?></span>
                  <span class="collection-meta-value">
                    <?= esc($collection["owner_username"]) ?><?= $isMine ? " (" . esc($tr("you", "vous")) . ")" : "" ?>
                  </span>
                </div>
                <div class="collection-meta-item collection-meta-item-wide">
                  <span class="collection-meta-label"><?= esc($tr("Date range", "Periode")) ?></span>
                  <span class="collection-meta-value">
                    <?= esc($collection["start_at"]) ?> <?= esc($tr("to", "a")) ?> <?= esc($collection["end_at"]) ?>
                  </span>
                </div>
                <div class="collection-meta-item">
                  <span class="collection-meta-label"><?= esc($tr("Shared with", "Partage avec")) ?></span>
                  <span class="collection-meta-value"><?= esc($collection["shared_with"] ?: $tr("Nobody yet", "Personne pour le moment")) ?></span>
                </div>
              </div>

              <div class="collection-card-actions">
                <div class="collection-card-toolbar">
                  <a class="btn btn-sm btn-outline-secondary" href="<?= esc(appUrl('/admin/collections.php')) ?>?view=<?= (int)$collection["collection_id"] ?>"><?= esc($tr("View measurements", "Voir les mesures")) ?></a>
                  <form method="post" onsubmit="return confirm('<?= esc($tr("Delete this collection?", "Supprimer cette collection ?")) ?>');" class="m-0">
                    <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="collection_id" value="<?= (int)$collection["collection_id"] ?>">
                    <button class="btn btn-sm btn-outline-danger"><?= esc($tr("Delete", "Supprimer")) ?></button>
                  </form>
                </div>

                <form method="post" class="collection-card-form">
                  <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                  <input type="hidden" name="action" value="rename">
                  <input type="hidden" name="collection_id" value="<?= (int)$collection["collection_id"] ?>">
                  <div class="collection-card-form-grid">
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Name", "Nom")) ?></label>
                      <input class="form-control form-control-sm" name="name" value="<?= esc($collection["name"]) ?>" required>
                    </div>
                    <div>
                      <label class="collection-inline-label"><?= esc($tr("Description", "Description")) ?></label>
                      <input class="form-control form-control-sm" name="description" value="<?= esc($collection["description"] ?? "") ?>" placeholder="<?= esc($tr("Description", "Description")) ?>">
                    </div>
                    <div>
                      <label class="collection-inline-label">&nbsp;</label>
                      <button class="btn btn-sm btn-outline-dark w-100"><?= esc($tr("Save changes", "Enregistrer")) ?></button>
                    </div>
                  </div>
                </form>

                <?php if ($isMine && count($friends) > 0): ?>
                  <form method="post" class="collection-card-form">
                    <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                    <input type="hidden" name="action" value="share">
                    <input type="hidden" name="collection_id" value="<?= (int)$collection["collection_id"] ?>">
                    <div class="collection-card-form-grid collection-card-form-grid-share">
                      <div>
                        <label class="collection-inline-label"><?= esc($tr("Share with a friend", "Partager avec un ami")) ?></label>
                        <select class="form-select form-select-sm" name="friend_user_id" required>
                          <option value=""><?= esc($tr("Choose friend...", "Choisir un ami...")) ?></option>
                          <?php foreach ($friends as $friend): ?>
                            <option value="<?= (int)$friend["user_id"] ?>"><?= esc($friend["username"]) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div>
                        <label class="collection-inline-label">&nbsp;</label>
                        <button class="btn btn-sm btn-outline-primary w-100"><?= esc($tr("Share collection", "Partager la collection")) ?></button>
                      </div>
                    </div>
                  </form>
                <?php endif; ?>

                <?php if ($isMine && !empty($collection["shared_with"])): ?>
                  <div class="collection-share-list">
                    <div class="collection-inline-label mb-2"><?= esc($tr("Undo sharing", "Annuler le partage")) ?></div>
                    <div class="collection-share-pills">
                      <?php foreach (explode(", ", $collection["shared_with"]) as $sharedUsername): ?>
                        <?php
                        $sharedUserId = 0;
                        foreach ($friends as $friend) {
                          if ($friend["username"] === $sharedUsername) {
                            $sharedUserId = (int)$friend["user_id"];
                            break;
                          }
                        }
                        ?>
                        <?php if ($sharedUserId > 0): ?>
                          <form method="post" class="collection-share-pill">
                            <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                            <input type="hidden" name="action" value="unshare">
                            <input type="hidden" name="collection_id" value="<?= (int)$collection["collection_id"] ?>">
                            <input type="hidden" name="friend_user_id" value="<?= $sharedUserId ?>">
                            <span><?= esc($sharedUsername) ?></span>
                            <button class="btn btn-sm btn-outline-secondary"><?= esc($tr("Unshare", "Retirer")) ?></button>
                          </form>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($selectedCollection): ?>
      <div class="card p-3 mt-3">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
          <div>
            <h2 class="h5 mb-1"><?= esc($tr("Collection details", "Details de la collection")) ?></h2>
            <div class="fw-semibold"><?= esc($selectedCollection["name"]) ?></div>
            <div class="text-muted small">
              <?= esc($tr("Owner", "Proprietaire")) ?>: <?= esc($selectedCollection["owner_username"]) ?> |
              <?= esc($tr("Station", "Station")) ?>: <?= esc($selectedCollection["station_name"]) ?> (<?= esc($selectedCollection["serial_number"]) ?>)
            </div>
            <div class="text-muted small">
              <span class="range-cell"><?= esc($selectedCollection["start_at"]) ?><span class="range-end"><?= esc($tr("to", "a")) ?> <?= esc($selectedCollection["end_at"]) ?></span></span>
            </div>
          </div>
          <a class="btn btn-sm btn-outline-secondary" href="<?= esc(appUrl('/admin/collections.php')) ?>"><?= esc($tr("Close", "Fermer")) ?></a>
        </div>

        <?php if (($selectedCollection["description"] ?? "") !== ""): ?>
          <p class="text-muted"><?= esc($selectedCollection["description"]) ?></p>
        <?php endif; ?>

        <?php if (count($selectedMeasurements) === 0): ?>
          <p class="empty-state"><?= esc($tr("No measurements are linked to this collection.", "Aucune mesure n'est liee a cette collection.")) ?></p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th><?= esc($tr("Time", "Heure")) ?></th>
                  <th><?= esc($tr("Temp", "Temp")) ?></th>
                  <th><?= esc($tr("Hum", "Hum")) ?></th>
                  <th><?= esc($tr("Press", "Press")) ?></th>
                  <th><?= esc($tr("Light", "Lumiere")) ?></th>
                  <th><?= esc($tr("Gas", "Gaz")) ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($selectedMeasurements as $measurement): ?>
                  <tr>
                    <td><?= esc($measurement["measured_at"]) ?></td>
                    <td><?= esc((string)$measurement["temperature"]) ?></td>
                    <td><?= esc((string)$measurement["humidity"]) ?></td>
                    <td><?= esc((string)$measurement["pressure"]) ?></td>
                    <td><?= esc((string)$measurement["light"]) ?></td>
                    <td><?= esc((string)$measurement["gas"]) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
