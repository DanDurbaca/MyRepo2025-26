<?php
// Reorganized dashboard: header/nav comes from _header.php; Chart.js loaded below
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../_header.php';

// ensure user is logged in and username is available for queries
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}
$username = $_SESSION['username'];

// Load stations the user owns or has accepted shares for
$stmt = $pdo->prepare("
    SELECT s.pk_serialNumber, s.name, s.fk_user_owns,
           CASE WHEN s.fk_user_owns = ? THEN 1 ELSE 0 END AS is_owner
    FROM station s
    LEFT JOIN station_share ss
        ON ss.station_serial = s.pk_serialNumber
       AND ss.shared_with = ?
       AND ss.status = 'accepted'
    WHERE s.fk_user_owns = ? OR ss.id IS NOT NULL
    ORDER BY is_owner DESC, s.pk_serialNumber
");
$stmt->execute([$username, $username, $username]);
$first = '';
$stations = [];
while ($s = $stmt->fetch()) {
    if ($first === '') $first = $s['pk_serialNumber'];
    $stations[] = $s;
}
?>

<div class="container">
    <h1>Dashboard</h1>
    <p>Welcome to your dashboard! Monitor your indoor climate data.</p>

    <!-- Stat row: three summary cards across the top -->
    <div id="quick-stats" class="stat-row">
        <?php if (count($stations) === 0): ?>
            <div class="stat-box">
                <div class="number" id="stat-temp-val">—</div>
                <div class="label">Temperature</div>
                <div class="text-small text-muted" id="stat-temp-sub">No station</div>
            </div>
            <div class="stat-box">
                <div class="number" id="stat-hum-val">—</div>
                <div class="label">Humidity</div>
                <div class="text-small text-muted" id="stat-hum-sub">No station</div>
            </div>
            <div class="stat-box">
                <div class="number" id="stat-air-val">—</div>
                <div class="label">Air Quality</div>
                <div class="text-small text-muted" id="stat-air-sub">No station</div>
            </div>
            <div class="stat-box">
                <div class="number" id="stat-light-val">—</div>
                <div class="label">Light</div>
                <div class="text-small text-muted" id="stat-light-sub">No station</div>
            </div>
        <?php else: ?>
            <div class="stat-box"><div class="number" id="stat-temp-val">—</div><div class="label">Temperature</div><div class="text-small text-muted" id="stat-temp-sub"><?php echo htmlspecialchars($stations[0]['name']); ?></div></div>
            <div class="stat-box"><div class="number" id="stat-hum-val">—</div><div class="label">Humidity</div><div class="text-small text-muted" id="stat-hum-sub"><?php echo htmlspecialchars($stations[0]['name']); ?></div></div>
            <div class="stat-box"><div class="number" id="stat-air-val">—</div><div class="label">Air Quality</div><div class="text-small text-muted" id="stat-air-sub"><?php echo htmlspecialchars($stations[0]['name']); ?></div></div>
            <div class="stat-box"><div class="number" id="stat-light-val">—</div><div class="label">Light</div><div class="text-small text-muted" id="stat-light-sub"><?php echo htmlspecialchars($stations[0]['name']); ?></div></div>
        <?php endif; ?>
    </div>

    <!-- Main grid: charts (left) and a narrow sidebar (right) -->
    <div class="main-grid">
        <div>
            <div class="box charts">
                <div class="box-header">Charts <span class="badge-live">Live · updates every 10s</span></div>
                <?php if ($first === ''): ?>
                    <p class="text-muted">You don't have any stations yet. <a href="stations.php">Claim or register a station</a> to see live measurements.</p>
                <?php else: ?>
                    <div class="chart-container" id="charts" style="overflow:hidden;">
                        <div class="chart-box"><canvas id="tempChart"></canvas></div>
                        <div class="chart-box"><canvas id="humChart"></canvas></div>
                        <div class="chart-box"><canvas id="airChart"></canvas></div>
                        <div class="chart-box"><canvas id="lightChart"></canvas></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="sidebar">
            <div class="box">
                <div class="box-header">Live Measurements</div>
                <div class="form-group">
                    <label for="station_select">Select Station</label>
                    <select id="station_select">
                        <?php foreach ($stations as $s) {
                            $label = $s['name'] . ' (' . $s['pk_serialNumber'] . ')';
                            if ((int)$s['is_owner'] !== 1) {
                                $label .= ' - shared by ' . $s['fk_user_owns'];
                            }
                            echo "<option value='" . htmlspecialchars($s['pk_serialNumber']) . "'" . ($s['pk_serialNumber'] === $first ? ' selected' : '') . ">" . htmlspecialchars($label) . "</option>";
                        } ?>
                    </select>
                </div>

<div class="form-group">
    <label for="start_date">Start Date</label>
    <input type="datetime-local" id="start_date" value="<?php echo date('Y-m-d\\TH:i', strtotime('-1 hour')); ?>">
</div>
<div class="form-group">
    <label for="end_date">End Date</label>
    <input type="datetime-local" id="end_date" value="<?php echo date('Y-m-d\\TH:i'); ?>">
</div>

                <p>
                    <button type="button" class="btn btn-primary" id="updateChartBtn">Update Chart</button>
                    <button type="button" class="btn btn-secondary" id="resetDatesBtn">Reset</button>
                </p>
            </div>

            <div class="box">
                <div class="box-header">Quick Links</div>
                <p>
                    <a href="stations.php" class="btn btn-primary">Stations</a>
                    <a href="collections.php" class="btn btn-success">Collections</a>
                    <a href="data.php" class="btn btn-secondary">View Data</a>
                    <a href="friends.php" class="btn btn-warning">Friends</a>
                </p>
            </div>
        </aside>
    </div>
</div>

<!-- Load Chart.js and then the dashboard script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dashboard JS: fetches measurements and updates Chart.js charts and quick-stats
$(function(){
    var select = $('#station_select');
    var tempCtx = document.getElementById('tempChart') ? document.getElementById('tempChart').getContext('2d') : null;
    var humCtx = document.getElementById('humChart') ? document.getElementById('humChart').getContext('2d') : null;
    var airCtx = document.getElementById('airChart') ? document.getElementById('airChart').getContext('2d') : null;
    var lightCtx = document.getElementById('lightChart') ? document.getElementById('lightChart').getContext('2d') : null;

    function createLine(ctx, label, borderColor, bgColor, fill){
        if (!ctx) return null;
        return new Chart(ctx, {
            type: 'line',
            data: { labels: [], datasets: [{ label: label, data: [], borderColor: borderColor, backgroundColor: bgColor, tension: 0.2, fill: !!fill }] },
            options: { responsive: true, maintainAspectRatio: false, animation: false, scales: { x: { display: true }, y: { display: true } }, plugins: { legend: { display: true } } }
        });
    }

    var tempChart = createLine(tempCtx, 'Temperature (°C)', 'rgba(255,99,132,1)', 'rgba(255,99,132,0.08)', true);
    var humChart = createLine(humCtx, 'Humidity (%)', 'rgba(54,162,235,1)', 'rgba(54,162,235,0.08)', true);
    var airChart = createLine(airCtx, 'Air Quality (ppm)', 'rgba(75,192,192,1)', 'rgba(75,192,192,0.04)', false);
    var lightChart = createLine(lightCtx, 'Light (lux)', 'rgba(255,206,86,1)', 'rgba(255,206,86,0.06)', true);

    function updateChartsFromData(data){
        var labels = data.map(d => new Date(d.timestamp).toLocaleTimeString());
        var temps = data.map(d => d.temperature);
        var hums = data.map(d => d.humidity);
        var gases = data.map(d => d.air_quality);
        var lights = data.map(d => d.light);

        if (tempChart){ tempChart.data.labels = labels; tempChart.data.datasets[0].data = temps; tempChart.update(); }
        if (humChart){ humChart.data.labels = labels; humChart.data.datasets[0].data = hums; humChart.update(); }
        if (airChart){ airChart.data.labels = labels; airChart.data.datasets[0].data = gases; airChart.update(); }
        if (lightChart){ lightChart.data.labels = labels; lightChart.data.datasets[0].data = lights; lightChart.update(); }
    }

    function fetchAndRender(){
        var station = select.val(); if (!station) return;
        $.getJSON('../api/station_data.php', { station: station, limit: 200, start_date: $('#start_date').val(), end_date: $('#end_date').val() }, function(resp){
            if (resp.ok && resp.measurements){
                updateChartsFromData(resp.measurements);
                var latest = resp.measurements.length ? resp.measurements[resp.measurements.length - 1] : null;
                if (latest) {
                    var stationName = $('#station_select option:selected').text() || '';
                    $('#stat-temp-val').text(latest.temperature + '°C');
                    $('#stat-hum-val').text(latest.humidity + '%');
                    $('#stat-air-val').text(latest.air_quality + ' ppm');
                    $('#stat-light-val').text(latest.light + ' lx');
                    var sub = stationName + ' · ' + new Date(latest.timestamp).toLocaleString();
                    $('#stat-temp-sub').text(sub);
                    $('#stat-hum-sub').text(sub);
                    $('#stat-air-sub').text(sub);
                } else {
                    $('#stat-temp-val').text('—');
                    $('#stat-hum-val').text('—');
                    $('#stat-air-val').text('—');
                    $('#stat-temp-sub').text('No data');
                    $('#stat-hum-sub').text('No data');
                    $('#stat-air-sub').text('No data');
                }
            }
        });
    }

    if (select.val()) fetchAndRender();
    select.on('change', fetchAndRender);
    $('#updateChartBtn').on('click', fetchAndRender);
    $('#resetDatesBtn').on('click', function(){
        var now = new Date(), oneHourAgo = new Date(now.getTime() - 60*60*1000);
        $('#start_date').val(oneHourAgo.toISOString().slice(0,16)); $('#end_date').val(now.toISOString().slice(0,16));
        fetchAndRender();
    });
    setInterval(function(){ if (select.val()) fetchAndRender(); }, 10000);
    // ensure charts size correctly after initial render and on window resize
    setTimeout(function(){ if (tempChart) tempChart.resize(); if (humChart) humChart.resize(); if (airChart) airChart.resize(); if (lightChart) lightChart.resize(); }, 150);
    window.addEventListener('resize', function(){ if (tempChart) tempChart.resize(); if (humChart) humChart.resize(); if (airChart) airChart.resize(); if (lightChart) lightChart.resize(); });
});
</script>

</body>
</html>
