<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
/** @var mysqli $conn */
requireLogin();
$title = "Welcome";

$currentUserId = (int)$_SESSION["user_id"];
$username = $_SESSION["username"] ?? "User";

$stationCount = 0;
$measurementCount = 0;
$collectionCount = 0;
$friendCount = 0;
$sharedWithMeCount = 0;
$pendingRequestCount = 0;

$stationActivity = [];
$stationSnapshot = [];
$stationOptions = [];
$stationCharts = [];
$recentCollections = [];
$sharedCollections = [];

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM stations WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$stationCount = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

$stmt = mysqli_prepare($conn, "
  SELECT COUNT(*) AS total
  FROM measurements m
  INNER JOIN stations s ON s.station_id = m.station_id
  WHERE s.user_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$measurementCount = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM collections WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$collectionCount = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM friendships WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$friendCount = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

if (hasTable($conn, "collection_shares")) {
  $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM collection_shares WHERE user_id = ?");
  mysqli_stmt_bind_param($stmt, "i", $currentUserId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $sharedWithMeCount = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);
}

if (hasTable($conn, "friend_requests")) {
  $stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS total
    FROM friend_requests
    WHERE receiver_user_id = ? AND status = 'pending'
  ");
  mysqli_stmt_bind_param($stmt, "i", $currentUserId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $pendingRequestCount = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);
}

$stmt = mysqli_prepare($conn, "
  SELECT
    s.station_id,
    s.name,
    s.serial_number,
    latest.measured_at,
    latest.temperature,
    latest.humidity,
    latest.pressure
  FROM stations s
  LEFT JOIN (
    SELECT m1.station_id, m1.measured_at, m1.temperature, m1.humidity, m1.pressure
    FROM measurements m1
    INNER JOIN (
      SELECT station_id, MAX(measured_at) AS max_measured_at
      FROM measurements
      GROUP BY station_id
    ) m2 ON m2.station_id = m1.station_id AND m2.max_measured_at = m1.measured_at
  ) latest ON latest.station_id = s.station_id
  WHERE s.user_id = ?
  ORDER BY s.name
  LIMIT 6
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $stationActivity[] = $row;

$stmt = mysqli_prepare($conn, "
  SELECT
    s.station_id,
    s.name,
    latest.temperature,
    latest.humidity
  FROM stations s
  LEFT JOIN (
    SELECT m1.station_id, m1.temperature, m1.humidity
    FROM measurements m1
    INNER JOIN (
      SELECT station_id, MAX(measured_at) AS max_measured_at
      FROM measurements
      GROUP BY station_id
    ) m2 ON m2.station_id = m1.station_id AND m2.max_measured_at = m1.measured_at
  ) latest ON latest.station_id = s.station_id
  WHERE s.user_id = ?
  ORDER BY s.name
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $stationSnapshot[] = $row;

foreach ($stationSnapshot as $station) {
  $stationId = (int)$station["station_id"];
  $stationOptions[] = [
    "station_id" => $stationId,
    "name" => $station["name"],
  ];
  $stationCharts[$stationId] = [
    "name" => $station["name"],
    "labels" => [],
    "temperature" => [],
    "humidity" => [],
    "pressure" => [],
  ];
}

$stmt = mysqli_prepare($conn, "
  SELECT
    s.station_id,
    s.name,
    m.measured_at,
    m.temperature,
    m.humidity,
    m.pressure
  FROM stations s
  LEFT JOIN measurements m ON m.station_id = s.station_id
  WHERE s.user_id = ?
  ORDER BY s.station_id, m.measured_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$perStationCounts = [];
while ($row = mysqli_fetch_assoc($res)) {
  $stationId = (int)$row["station_id"];
  if (!isset($stationCharts[$stationId])) {
    $stationCharts[$stationId] = [
      "name" => $row["name"],
      "labels" => [],
      "temperature" => [],
      "humidity" => [],
      "pressure" => [],
    ];
  }

  if ($row["measured_at"] === null) {
    continue;
  }

  $perStationCounts[$stationId] = $perStationCounts[$stationId] ?? 0;
  if ($perStationCounts[$stationId] >= 12) {
    continue;
  }

  $stationCharts[$stationId]["labels"][] = $row["measured_at"];
  $stationCharts[$stationId]["temperature"][] = $row["temperature"] !== null ? (float)$row["temperature"] : null;
  $stationCharts[$stationId]["humidity"][] = $row["humidity"] !== null ? (float)$row["humidity"] : null;
  $stationCharts[$stationId]["pressure"][] = $row["pressure"] !== null ? (float)$row["pressure"] : null;
  $perStationCounts[$stationId]++;
}

foreach ($stationCharts as $stationId => $series) {
  $stationCharts[$stationId]["labels"] = array_reverse($series["labels"]);
  $stationCharts[$stationId]["temperature"] = array_reverse($series["temperature"]);
  $stationCharts[$stationId]["humidity"] = array_reverse($series["humidity"]);
  $stationCharts[$stationId]["pressure"] = array_reverse($series["pressure"]);
}

$stmt = mysqli_prepare($conn, "
  SELECT
    c.collection_id,
    c.name,
    c.description,
    c.start_at,
    c.end_at,
    s.name AS station_name,
    COUNT(cm.measurement_id) AS measurement_count
  FROM collections c
  INNER JOIN stations s ON s.station_id = c.station_id
  LEFT JOIN collection_measurements cm ON cm.collection_id = c.collection_id
  WHERE c.user_id = ?
  GROUP BY c.collection_id, c.name, c.description, c.start_at, c.end_at, s.name
  ORDER BY c.collection_id DESC
  LIMIT 4
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $recentCollections[] = $row;

if (hasTable($conn, "collection_shares")) {
  $stmt = mysqli_prepare($conn, "
    SELECT
      c.collection_id,
      c.name,
      owner.username AS owner_username,
      s.name AS station_name
    FROM collection_shares cs
    INNER JOIN collections c ON c.collection_id = cs.collection_id
    INNER JOIN users owner ON owner.user_id = c.user_id
    INNER JOIN stations s ON s.station_id = c.station_id
    WHERE cs.user_id = ?
    ORDER BY c.collection_id DESC
    LIMIT 4
  ");
  mysqli_stmt_bind_param($stmt, "i", $currentUserId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($res)) $sharedCollections[] = $row;
}

require_once __DIR__ . "/../admin/includes/header.php";

$selectedStationId = count($stationOptions) > 0 ? (int)$stationOptions[0]["station_id"] : 0;
$hasChartData = false;
foreach ($stationCharts as $series) {
  if (count($series["labels"]) > 0) {
    $hasChartData = true;
    break;
  }
}
?>

<section class="soft-panel p-4 p-lg-5 mb-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-7">
      <p class="text-uppercase fw-bold text-secondary small mb-2"><?= esc(t("station_platform")) ?></p>
      <h1 class="display-6 fw-bold mb-2"><?= esc(t("your_station_workspace")) ?></h1>
      <div class="action-group">
      <a class="btn btn-dark" href="<?= esc(appUrl('/user/stations.php')) ?>"><?= esc(t("my_stations")) ?></a>
      <a class="btn btn-outline-dark" href="<?= esc(appUrl('/user/measurements.php')) ?>"><?= esc(t("measurements")) ?></a>
      <a class="btn btn-outline-dark" href="<?= esc(appUrl('/user/collections.php')) ?>"><?= esc(t("collections")) ?></a>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card p-4 h-100">
        <div class="section-title mb-2"><?= esc(t("account_overview")) ?></div>
        <div class="d-flex flex-column gap-2">
          <div class="d-flex justify-content-between"><span class="text-muted"><?= esc(t("pending_friend_requests")) ?></span><strong><?= $pendingRequestCount ?></strong></div>
          <div class="d-flex justify-content-between"><span class="text-muted"><?= esc(t("shared_with_me")) ?></span><strong><?= $sharedWithMeCount ?></strong></div>
          <div class="d-flex justify-content-between"><span class="text-muted"><?= esc(t("next_step")) ?></span><strong><?= $stationCount > 0 ? esc(t("review_activity")) : esc(t("register_a_station")) ?></strong></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="metric-card">
      <div class="metric-label"><?= esc(t("stations")) ?></div>
      <div class="metric-value"><?= $stationCount ?></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="metric-card">
      <div class="metric-label"><?= esc(t("measurements")) ?></div>
      <div class="metric-value"><?= $measurementCount ?></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="metric-card">
      <div class="metric-label"><?= esc(t("collections")) ?></div>
      <div class="metric-value"><?= $collectionCount ?></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="metric-card">
      <div class="metric-label"><?= esc(t("friends")) ?></div>
      <div class="metric-value"><?= $friendCount ?></div>
    </div>
  </div>
</div>

<div class="card p-4 mb-4">
  <div class="card-header-inline">
    <div>
      <h2 class="section-title"><?= esc(t("latest_measurement_trend")) ?></h2>
    </div>
    <div class="chart-controls">
      <label class="chart-label" for="stationChartSelect"><?= esc(t("station")) ?></label>
      <select class="form-select chart-select" id="stationChartSelect">
        <?php foreach ($stationOptions as $station): ?>
          <option value="<?= (int)$station["station_id"] ?>"<?= (int)$station["station_id"] === $selectedStationId ? " selected" : "" ?>>
            <?= esc($station["name"]) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <?php if (!$hasChartData): ?>
    <p class="empty-state"><?= esc(t("no_chart_data")) ?></p>
  <?php else: ?>
    <div class="chart-shell">
      <div class="chart-summary" id="stationChartSummary"></div>
      <canvas id="stationSnapshotChart" aria-label="<?= esc(t("station_snapshot")) ?>"></canvas>
    </div>
  <?php endif; ?>
</div>

<div class="row g-3">
  <div class="col-xl-7">
    <div class="card p-4 h-100">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc(t("latest_station_activity")) ?></h2>
        </div>
            <a class="btn btn-sm btn-outline-dark" href="<?= esc(appUrl('/user/stations.php')) ?>"><?= esc(t("manage_stations")) ?></a>
      </div>

      <?php if (count($stationActivity) === 0): ?>
        <p class="empty-state"><?= esc(t("no_registered_stations")) ?></p>
      <?php else: ?>
        <div class="management-card-list">
          <?php foreach ($stationActivity as $station): ?>
            <section class="management-card">
              <div class="management-card-header">
                <div>
                  <h3 class="management-card-title"><?= esc($station["name"]) ?></h3>
                  <p class="management-card-description"><?= esc($station["serial_number"]) ?></p>
                </div>
                <span class="badge rounded-pill <?= $station["measured_at"] ? "text-bg-success" : "text-bg-secondary" ?>">
                  <?= $station["measured_at"] ? esc(t("active")) : esc(t("no_data")) ?>
                </span>
              </div>
              <div class="management-card-meta">
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("latest_reading")) ?></span>
                  <span class="management-meta-value"><?= esc($station["measured_at"] ?? t("no_data")) ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("temperature")) ?></span>
                  <span class="management-meta-value"><?= $station["temperature"] !== null ? esc((string)$station["temperature"]) . " C" : "-" ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("humidity")) ?></span>
                  <span class="management-meta-value"><?= $station["humidity"] !== null ? esc((string)$station["humidity"]) . " %" : "-" ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("pressure")) ?></span>
                  <span class="management-meta-value"><?= $station["pressure"] !== null ? esc((string)$station["pressure"]) . " hPa" : "-" ?></span>
                </div>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="d-flex flex-column gap-3">
      <div class="card p-4">
        <div class="card-header-inline">
          <div>
            <h2 class="section-title"><?= esc(t("recent_collections")) ?></h2>
          </div>
            <a class="btn btn-sm btn-outline-dark" href="<?= esc(appUrl('/user/collections.php')) ?>"><?= esc(t("open")) ?></a>
        </div>

        <?php if (count($recentCollections) === 0): ?>
          <p class="empty-state"><?= esc(t("no_collections_yet")) ?></p>
        <?php else: ?>
          <div class="collection-card-list">
            <?php foreach ($recentCollections as $collection): ?>
              <section class="collection-card">
                <div class="collection-card-header">
                  <div>
                    <h3 class="collection-card-title mb-1"><?= esc($collection["name"]) ?></h3>
                    <p class="collection-card-description mb-0"><?= esc($collection["description"] ?: t("no_description_added")) ?></p>
                  </div>
                  <div class="collection-card-badge"><?= (int)$collection["measurement_count"] ?> <?= esc(t("rows")) ?></div>
                </div>
                <div class="collection-card-meta">
                  <div class="collection-meta-item">
                    <span class="collection-meta-label"><?= esc(t("station")) ?></span>
                    <span class="collection-meta-value"><?= esc($collection["station_name"]) ?></span>
                  </div>
                  <div class="collection-meta-item collection-meta-item-wide">
                    <span class="collection-meta-label"><?= esc(t("date_range")) ?></span>
                    <span class="collection-meta-value"><?= esc($collection["start_at"]) ?> to <?= esc($collection["end_at"]) ?></span>
                  </div>
                </div>
              </section>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="card p-4">
        <div class="card-header-inline">
          <div>
            <h2 class="section-title"><?= esc(t("sharing_and_requests")) ?></h2>
          </div>
            <a class="btn btn-sm btn-outline-dark" href="<?= esc(appUrl('/user/friends.php')) ?>"><?= esc(t("open")) ?></a>
        </div>

        <div class="management-card-meta three-col mb-3">
          <div class="management-meta-item">
            <span class="management-meta-label"><?= esc(t("pending_friend_requests")) ?></span>
            <span class="management-meta-value"><?= $pendingRequestCount ?></span>
          </div>
          <div class="management-meta-item">
            <span class="management-meta-label"><?= esc(t("friends")) ?></span>
            <span class="management-meta-value"><?= $friendCount ?></span>
          </div>
          <div class="management-meta-item">
            <span class="management-meta-label"><?= esc(t("shared_with_me")) ?></span>
            <span class="management-meta-value"><?= $sharedWithMeCount ?></span>
          </div>
        </div>

        <?php if (count($sharedCollections) === 0): ?>
          <p class="empty-state"><?= esc(t("no_shared_collections")) ?></p>
        <?php else: ?>
          <div class="management-card-list">
            <?php foreach ($sharedCollections as $collection): ?>
              <section class="management-card">
                <div class="management-card-header">
                  <div>
                    <h3 class="management-card-title"><?= esc($collection["name"]) ?></h3>
                    <p class="management-card-description"><?= esc(t("shared_by", ["name" => $collection["owner_username"]])) ?></p>
                  </div>
                  <a class="btn btn-sm btn-outline-secondary" href="<?= esc(appUrl('/user/collections.php')) ?>?view=<?= (int)$collection["collection_id"] ?>"><?= esc(t("view")) ?></a>
                </div>
                <div class="management-card-meta three-col">
                  <div class="management-meta-item">
                    <span class="management-meta-label"><?= esc(t("owner")) ?></span>
                    <span class="management-meta-value"><?= esc($collection["owner_username"]) ?></span>
                  </div>
                  <div class="management-meta-item">
                    <span class="management-meta-label"><?= esc(t("station")) ?></span>
                    <span class="management-meta-value"><?= esc($collection["station_name"]) ?></span>
                  </div>
                  <div class="management-meta-item">
                    <span class="management-meta-label">Collection ID</span>
                    <span class="management-meta-value"><?= (int)$collection["collection_id"] ?></span>
                  </div>
                </div>
              </section>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($hasChartData): ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script>
    (() => {
      const canvas = document.getElementById("stationSnapshotChart");
      const select = document.getElementById("stationChartSelect");
      const summary = document.getElementById("stationChartSummary");
      if (!canvas || !select) return;

      const root = document.documentElement;
      const styles = getComputedStyle(root);
      const textColor = styles.getPropertyValue("--text-color").trim() || "#101828";
      const mutedColor = styles.getPropertyValue("--muted-color").trim() || "#667085";
      const gridColor = "rgba(148, 163, 184, 0.16)";
      const chartData = <?= json_encode($stationCharts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const summaryLabels = {
        temperature: <?= json_encode(t("temperature"), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        humidity: <?= json_encode(t("humidity"), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        pressure: <?= json_encode(t("pressure"), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
      };

      function renderSummary(series) {
        const lastTemperature = series.temperature.filter(value => value !== null).slice(-1)[0];
        const lastHumidity = series.humidity.filter(value => value !== null).slice(-1)[0];
        const lastPressure = series.pressure.filter(value => value !== null).slice(-1)[0];

        summary.innerHTML = `
          <div class="chart-summary-item">
            <span class="chart-summary-label">${summaryLabels.temperature}</span>
            <strong>${lastTemperature !== undefined ? `${lastTemperature} C` : "-"}</strong>
          </div>
          <div class="chart-summary-item">
            <span class="chart-summary-label">${summaryLabels.humidity}</span>
            <strong>${lastHumidity !== undefined ? `${lastHumidity} %` : "-"}</strong>
          </div>
          <div class="chart-summary-item">
            <span class="chart-summary-label">${summaryLabels.pressure}</span>
            <strong>${lastPressure !== undefined ? `${lastPressure} hPa` : "-"}</strong>
          </div>
        `;
      }

      function seriesFor(stationId) {
        return chartData[stationId] || { labels: [], temperature: [], humidity: [], pressure: [] };
      }

      const initialSeries = seriesFor(select.value);

      const chart = new Chart(canvas, {
        data: {
          labels: initialSeries.labels,
          datasets: [
            {
              type: "line",
              label: <?= json_encode(t("temperature_c"), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
              data: initialSeries.temperature,
              backgroundColor: "rgba(59, 130, 246, 0.72)",
              borderColor: "rgba(59, 130, 246, 1)",
              borderWidth: 3,
              pointRadius: 3,
              pointHoverRadius: 5,
              tension: 0.32,
              yAxisID: "y"
            },
            {
              type: "line",
              label: <?= json_encode(t("humidity_percent"), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
              data: initialSeries.humidity,
              borderColor: "rgba(16, 185, 129, 1)",
              backgroundColor: "rgba(16, 185, 129, 0.18)",
              tension: 0.3,
              fill: false,
              pointRadius: 4,
              pointHoverRadius: 5,
              yAxisID: "y1"
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: "index",
            intersect: false
          },
          plugins: {
            legend: {
              labels: {
                color: textColor,
                boxWidth: 12,
                usePointStyle: true
              }
            }
          },
          scales: {
            x: {
              ticks: {
                color: mutedColor
              },
              grid: {
                display: false
              }
            },
            y: {
              position: "left",
              ticks: {
                color: mutedColor
              },
              grid: {
                color: gridColor
              }
            },
            y1: {
              position: "right",
              ticks: {
                color: mutedColor
              },
              grid: {
                drawOnChartArea: false
              }
            }
          }
        }
      });

      renderSummary(initialSeries);

      select.addEventListener("change", () => {
        const nextSeries = seriesFor(select.value);
        chart.data.labels = nextSeries.labels;
        chart.data.datasets[0].data = nextSeries.temperature;
        chart.data.datasets[1].data = nextSeries.humidity;
        chart.update();
        renderSummary(nextSeries);
      });
    })();
  </script>
<?php endif; ?>

<?php require_once __DIR__ . "/../admin/includes/footer.php"; ?>
