<?php require_once __DIR__ . '/../includes/header.php'; ?> <!-- Include site header/navigation -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="container">

    <!-- Welcome message with user's first name -->
    <h1>Welcome, <?= htmlspecialchars($firstName) ?></h1>

    <!-- Display admin badge if user is an admin -->
    <?php if ($is_admin): ?>
        <p><strong>Administrator</strong></p>
    <?php endif; ?>

    <!-- Display any error message -->
    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Section: User's stations -->
    <h2>Your Stations</h2>

    <div id="live-clock" style="font-size: 1.2rem; text-align: right; margin-bottom: 10px;"></div>
    <script>
    function updateClock() {
        const now = new Date();
        document.getElementById('live-clock').innerHTML = now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();
    </script>

    <div class="station-grid">
<?php foreach ($stations as $station): 
    $station_sn = $station['pk_serialNumber'];
    $data = $dashboardData[$station_sn] ?? null;
    if (!$data) continue;
    $latest = $data['latest'];
    $stats  = $data['stats'];
    $graph  = $data['graph'];
    $status = $data['status'];
    $outdoorTemp = $data['outdoorTemp'];
    $statusClass = str_contains($status, '⚠️') ? 'status-alert' : (str_contains($status, '💧') || str_contains($status, '🔥') ? 'status-warning' : 'status-ok');
?>
    <div class="station-card">
        <h3><?= htmlspecialchars($station['name'] ?? 'Unnamed Station') ?></h3>
        <p class="text-muted"><?= htmlspecialchars($station_sn) ?></p>

        <?php if ($latest): ?>
            <p><strong>🕒 Last update:</strong> <?= htmlspecialchars($latest['timestamp']) ?></p>

            <div class="metric"><span><i class="fas fa-temperature-high"></i> Temperature</span><strong><?= number_format($latest['temperature'],1) ?> °C</strong></div>
            <div class="metric"><span><i class="fas fa-tint"></i> Humidity</span><strong><?= number_format($latest['humidity'],1) ?> %</strong></div>
            <div class="metric"><span><i class="fas fa-wind"></i> Pressure</span><strong><?= number_format($latest['pressure'],1) ?> hPa</strong></div>
            <div class="metric"><span><i class="fas fa-sun"></i> Light</span><strong><?= number_format($latest['light'],1) ?> lux</strong></div>
            <div class="metric"><span><i class="fas fa-wind"></i> Air Quality</span><strong><?= number_format($latest['gas'],0) ?> ppm</strong></div>

            <div class="metric">
                <span><i class="fas fa-chart-bar"></i> Status</span>
                <span class="<?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
            </div>

            <?php if ($outdoorTemp !== null): ?>
                <div class="metric">
                    <span><i class="fas fa-cloud-sun"></i> Outdoor (Luxembourg)</span><strong><?= number_format($outdoorTemp,1) ?> °C</strong>
                </div>
                <div class="metric">
                    <span><i class="fas fa-home"></i> Indoor vs Outdoor</span>
                    <strong><?= number_format($latest['temperature'] - $outdoorTemp,1) ?> °C</strong>
                </div>
            <?php else: ?>
                <div class="metric"><span><i class="fas fa-earth-americas"></i> Outdoor</span><span>unavailable</span></div>
            <?php endif; ?>

            <h4>📈 Temperature trend (last 24h)</h4>
            <canvas id="tempChart-<?= $station_sn ?>" style="max-height: 200px;"></canvas>

            <h4>📊 Statistics</h4>
            <div class="metric"><span><i class="fas fa-chart-line"></i> Average temp</span><strong><?= number_format($stats['avg_temp'],1) ?> °C</strong></div>
            <div class="metric"><span><i class="fas fa-arrow-down"></i> Min temp</span><strong><?= number_format($stats['min_temp'],1) ?> °C</strong></div>
            <div class="metric"><span><i class="fas fa-arrow-up"></i> Max temp</span><strong><?= number_format($stats['max_temp'],1) ?> °C</strong></div>
            <div class="metric"><span><i class="fas fa-database"></i> Total measurements</span><strong><?= $stats['total_records'] ?></strong></div>

        <?php else: ?>
            <p>⏳ No measurements yet.</p>
        <?php endif; ?>
    </div>

    <?php if ($latest && !empty($graph)): ?>
    // Generate Chart.js line chart for temperature trend using the last 24 measurements
    <script>
    (function() {
        const ctx = document.getElementById('tempChart-<?= $station_sn ?>').getContext('2d');
        const labels = <?= json_encode(array_column($graph, 'timestamp')) ?>;
        const temps = <?= json_encode(array_column($graph, 'temperature')) ?>;
        const formattedLabels = labels.map(ts => new Date(ts).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: formattedLabels,
                datasets: [{
                    label: 'Temperature (°C)',
                    data: temps,
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243,156,18,0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: { ticks: { maxRotation: 45, autoSkip: true, maxTicksLimit: 6 } },
                    y: { title: { display: true, text: '°C' } }
                }
            }
        });
    })();
    </script>
    <?php endif; ?>

<?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> <!-- Include site footer -->