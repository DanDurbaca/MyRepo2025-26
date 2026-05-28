<?php
require_once __DIR__ . "/includes/CommonCode.php";
/** @var mysqli $conn */
requireAdmin();
$title = "Admin Dashboard";

$stats = [
  "users" => 0,
  "stations" => 0,
  "assigned_stations" => 0,
  "measurements" => 0,
  "collections" => 0,
  "friend_requests" => 0,
];

// These summary counters drive the admin dashboard cards and quick overview panel.
$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$stats["users"] = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stations");
$stats["stations"] = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stations WHERE user_id IS NOT NULL");
$stats["assigned_stations"] = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM measurements");
$stats["measurements"] = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM collections");
$stats["collections"] = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);

if (hasTable($conn, "friend_requests")) {
  $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM friend_requests WHERE status = 'pending'");
  $stats["friend_requests"] = (int)(mysqli_fetch_assoc($res)["total"] ?? 0);
}

$recentUsers = [];
$res = mysqli_query($conn, "
  SELECT user_id, username, full_name, role
  FROM users
  ORDER BY user_id DESC
  LIMIT 5
");
while ($row = mysqli_fetch_assoc($res)) $recentUsers[] = $row;

$recentStations = [];
$res = mysqli_query($conn, "
  SELECT s.station_id, s.name, s.serial_number, u.username AS owner_username
  FROM stations s
  LEFT JOIN users u ON u.user_id = s.user_id
  ORDER BY s.station_id DESC
  LIMIT 5
");
while ($row = mysqli_fetch_assoc($res)) $recentStations[] = $row;

$stationVolume = [];
// The admin chart compares measurement volume per station to highlight which
// devices are currently producing the most data.
$res = mysqli_query($conn, "
  SELECT
    s.name,
    COUNT(m.measurement_id) AS measurement_count
  FROM stations s
  LEFT JOIN measurements m ON m.station_id = s.station_id
  GROUP BY s.station_id, s.name
  ORDER BY measurement_count DESC, s.name ASC
  LIMIT 8
");
while ($row = mysqli_fetch_assoc($res)) $stationVolume[] = $row;

$volumeLabels = [];
$volumeValues = [];
foreach ($stationVolume as $station) {
  $volumeLabels[] = $station["name"];
  $volumeValues[] = (int)$station["measurement_count"];
}

$assignmentChart = [
  "assigned" => (int)$stats["assigned_stations"],
  "available" => max(0, (int)$stats["stations"] - (int)$stats["assigned_stations"]),
];

require_once __DIR__ . "/includes/header.php";
?>

<section class="soft-panel p-4 p-lg-5 mb-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-8">
      <p class="text-uppercase fw-bold text-secondary small mb-2"><?= esc(t("admin_workspace")) ?></p>
      <h1 class="display-6 fw-bold mb-2"><?= esc(t("platform_control_center")) ?></h1>
      <div class="action-group">
        <a class="btn btn-dark" href="<?= esc(appUrl('/admin/users.php')) ?>"><?= esc(t("users")) ?></a>
        <a class="btn btn-outline-dark" href="<?= esc(appUrl('/admin/stations.php')) ?>"><?= esc(t("stations")) ?></a>
        <a class="btn btn-outline-dark" href="<?= esc(appUrl('/admin/measurements.php')) ?>"><?= esc(t("measurements")) ?></a>
        <a class="btn btn-outline-dark" href="<?= esc(appUrl('/admin/collections.php')) ?>"><?= esc(t("collections")) ?></a>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card p-4 h-100">
        <div class="section-title mb-2"><?= esc(t("system_snapshot")) ?></div>
        <div class="d-flex flex-column gap-2">
          <div class="d-flex justify-content-between"><span class="text-muted"><?= esc(t("registered_users")) ?></span><strong><?= $stats["users"] ?></strong></div>
          <div class="d-flex justify-content-between"><span class="text-muted"><?= esc(t("assigned_stations")) ?></span><strong><?= $stats["assigned_stations"] ?>/<?= $stats["stations"] ?></strong></div>
          <div class="d-flex justify-content-between"><span class="text-muted"><?= esc(t("pending_friend_requests")) ?></span><strong><?= $stats["friend_requests"] ?></strong></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="metric-card">
      <div class="metric-label"><?= esc(t("users")) ?></div>
      <div class="metric-value"><?= $stats["users"] ?></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="metric-card">
      <div class="metric-label"><?= esc(t("stations")) ?></div>
      <div class="metric-value"><?= $stats["stations"] ?></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="metric-card">
      <div class="metric-label"><?= esc(t("measurements")) ?></div>
      <div class="metric-value"><?= $stats["measurements"] ?></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="metric-card">
      <div class="metric-label"><?= esc(t("collections")) ?></div>
      <div class="metric-value"><?= $stats["collections"] ?></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-xl-8">
    <div class="card p-4 h-100">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc(t("platform_measurement_volume")) ?></h2>
        </div>
        <a class="btn btn-sm btn-outline-dark" href="<?= esc(appUrl('/admin/measurements.php')) ?>"><?= esc(t("measurements")) ?></a>
      </div>
      <?php if (count($volumeLabels) === 0): ?>
        <p class="empty-state"><?= esc(t("no_chart_data")) ?></p>
      <?php else: ?>
        <div class="chart-shell">
          <canvas id="adminVolumeChart" aria-label="<?= esc(t("platform_measurement_volume")) ?>"></canvas>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card p-4 h-100">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc(t("station_assignment_split")) ?></h2>
        </div>
        <a class="btn btn-sm btn-outline-dark" href="<?= esc(appUrl('/admin/stations.php')) ?>"><?= esc(t("stations")) ?></a>
      </div>
      <?php if ((int)$stats["stations"] === 0): ?>
        <p class="empty-state"><?= esc(t("no_stations_available")) ?></p>
      <?php else: ?>
        <div class="chart-shell chart-shell-compact">
          <canvas id="adminAssignmentChart" aria-label="<?= esc(t("station_assignment_split")) ?>"></canvas>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-xl-6">
    <div class="card p-4 h-100">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc(t("recent_users")) ?></h2>
        </div>
        <a class="btn btn-sm btn-outline-dark" href="<?= esc(appUrl('/admin/users.php')) ?>"><?= esc(t("manage")) ?></a>
      </div>

      <?php if (count($recentUsers) === 0): ?>
        <p class="empty-state"><?= esc(t("no_users_available")) ?></p>
      <?php else: ?>
        <div class="management-card-list">
          <?php foreach ($recentUsers as $user): ?>
            <section class="management-card">
              <div class="management-card-header">
                <div>
                  <h3 class="management-card-title"><?= esc($user["username"]) ?></h3>
                  <p class="management-card-description"><?= esc($user["full_name"]) ?></p>
                </div>
                <span class="badge rounded-pill <?= $user["role"] === "admin" ? "text-bg-dark" : "text-bg-secondary" ?>"><?= esc($user["role"]) ?></span>
              </div>
              <div class="management-card-meta three-col">
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("user_id")) ?></span>
                  <span class="management-meta-value"><?= (int)$user["user_id"] ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("username")) ?></span>
                  <span class="management-meta-value"><?= esc($user["username"]) ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("role")) ?></span>
                  <span class="management-meta-value"><?= esc($user["role"]) ?></span>
                </div>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card p-4 h-100">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc(t("recent_stations")) ?></h2>
        </div>
        <a class="btn btn-sm btn-outline-dark" href="<?= esc(appUrl('/admin/stations.php')) ?>"><?= esc(t("manage")) ?></a>
      </div>

      <?php if (count($recentStations) === 0): ?>
        <p class="empty-state"><?= esc(t("no_stations_available")) ?></p>
      <?php else: ?>
        <div class="management-card-list">
          <?php foreach ($recentStations as $station): ?>
            <section class="management-card">
              <div class="management-card-header">
                <div>
                  <h3 class="management-card-title"><?= esc($station["name"]) ?></h3>
                  <p class="management-card-description"><?= esc($station["serial_number"]) ?></p>
                </div>
                <span class="badge rounded-pill <?= $station["owner_username"] ? "text-bg-danger" : "text-bg-success" ?>">
                  <?= $station["owner_username"] ? esc(t("assigned")) : esc(t("available")) ?>
                </span>
              </div>
              <div class="management-card-meta three-col">
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("station")) ?> ID</span>
                  <span class="management-meta-value"><?= (int)$station["station_id"] ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("serial")) ?></span>
                  <span class="management-meta-value"><?= esc($station["serial_number"]) ?></span>
                </div>
                <div class="management-meta-item">
                  <span class="management-meta-label"><?= esc(t("owner")) ?></span>
                  <span class="management-meta-value"><?= esc($station["owner_username"] ?: t("no_owner")) ?></span>
                </div>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (count($volumeLabels) > 0 || (int)$stats["stations"] > 0): ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script>
    (() => {
      const root = document.documentElement;
      const styles = getComputedStyle(root);
      const textColor = styles.getPropertyValue("--text-color").trim() || "#101828";
      const mutedColor = styles.getPropertyValue("--muted-color").trim() || "#667085";
      const gridColor = "rgba(148, 163, 184, 0.16)";

      const volumeCanvas = document.getElementById("adminVolumeChart");
      if (volumeCanvas) {
        new Chart(volumeCanvas, {
          type: "bar",
          data: {
            labels: <?= json_encode($volumeLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            datasets: [{
              label: <?= json_encode(t("measurements"), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
              data: <?= json_encode($volumeValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
              backgroundColor: "rgba(59, 130, 246, 0.74)",
              borderColor: "rgba(59, 130, 246, 1)",
              borderRadius: 12,
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              x: {
                ticks: { color: mutedColor },
                grid: { display: false }
              },
              y: {
                ticks: { color: mutedColor, precision: 0 },
                grid: { color: gridColor }
              }
            }
          }
        });
      }

      const assignmentCanvas = document.getElementById("adminAssignmentChart");
      if (assignmentCanvas) {
        new Chart(assignmentCanvas, {
          type: "doughnut",
          data: {
            labels: [
              <?= json_encode(t("assigned"), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
              <?= json_encode(t("available"), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
            ],
            datasets: [{
              data: <?= json_encode([(int)$assignmentChart["assigned"], (int)$assignmentChart["available"]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
              backgroundColor: [
                "rgba(239, 68, 68, 0.82)",
                "rgba(16, 185, 129, 0.8)"
              ],
              borderColor: [
                "rgba(239, 68, 68, 1)",
                "rgba(16, 185, 129, 1)"
              ],
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "62%",
            plugins: {
              legend: {
                position: "bottom",
                labels: {
                  color: textColor,
                  usePointStyle: true,
                  boxWidth: 12
                }
              }
            }
          }
        });
      }
    })();
  </script>
<?php endif; ?>

<?php require_once __DIR__ . "/includes/footer.php";
?>
