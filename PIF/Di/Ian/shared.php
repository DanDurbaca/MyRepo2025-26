<?php
session_start();
require __DIR__ . '/assets/db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$dbError = null;
$sharedCollections = [];
$measureStmt = null;

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

try {
    $pdo = getDb();

    $sharedWithStmt = $pdo->prepare('SELECT c.pk_collection, c.name, c.description, c.fk_user_creates
                                     FROM hasaccess ha
                                     JOIN collection c ON ha.pkfk_collection = c.pk_collection
                                     WHERE ha.pkfk_user = :u
                                     ORDER BY c.pk_collection DESC');
    $sharedWithStmt->execute([':u' => $username]);
    $sharedCollections = $sharedWithStmt->fetchAll();

    // Optional legacy schemas (collection_share variants)
    foreach ([
        'SELECT c.pk_collection AS pk_collection, c.name, c.description, c.fk_user_creates AS fk_user_creates
         FROM collection_share cs
         JOIN collection c ON cs.fk_collectionID = c.pk_collection
         WHERE cs.shared_with_username = :u
         ORDER BY pk_collection DESC',
        'SELECT c.pk_collectionID AS pk_collection, c.name, c.description, c.fk_ownerUsername AS fk_user_creates
         FROM collection_share cs
         JOIN collection c ON cs.fk_collectionID = c.pk_collectionID
         WHERE cs.shared_with_username = :u
         ORDER BY pk_collection DESC'
    ] as $legacySql) {
        try {
            $legacy = $pdo->prepare($legacySql);
            $legacy->execute([':u' => $username]);
            $legacyRows = $legacy->fetchAll();
            if ($legacyRows) {
                $byId = [];
                foreach (array_merge($sharedCollections, $legacyRows) as $row) {
                    $byId[$row['pk_collection']] = $row;
                }
                $sharedCollections = array_values($byId);
                break;
            }
        } catch (PDOException $e) {
            continue;
        }
    }

    $measureStmt = $pdo->prepare(
        'SELECT m.pk_measurement, m.timestamp, m.temperature, m.humidity, m.pressure, m.light, m.gas
         FROM contains c
         JOIN measurement m ON m.pk_measurement = c.pkfk_measurement
         WHERE c.pkfk_collection = :cid
         ORDER BY m.timestamp DESC
         LIMIT 100'
    );
} catch (PDOException $e) {
    $dbError = 'Database connection failed. Update credentials in assets/db.php.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/style.css">
    <script src="/assets/js/chart.umd.js"></script>
    <title>Shared With Me</title>
</head>
<body>
    <?php include __DIR__ . '/assets/header.php'; ?>

    <main class="container">
        <h1>Collections Shared With Me</h1>
        <?php if ($dbError): ?>
            <div class="alert danger"><?php echo h($dbError); ?></div>
        <?php else: ?>
            <p class="muted">Logged in as <strong><?php echo h($username); ?></strong>. Showing <?php echo count($sharedCollections); ?> shared collection(s).</p>
            <section>
                <?php if (!$sharedCollections): ?>
                    <p class="info-text">No collections shared with you.</p>
                <?php else: ?>
                    <div class="card">
                        <div class="muted"><strong><?php echo count($sharedCollections); ?></strong> shared collection(s)</div>
                        <div class="cards" style="margin-top:12px;">
                            <?php foreach ($sharedCollections as $col): ?>
                                <?php
                                    $meta = json_decode($col['description'] ?? '', true) ?: [];
                                    $ms = [];
                                    if ($measureStmt) {
                                        $measureStmt->execute([':cid' => $col['pk_collection']]);
                                        $ms = $measureStmt->fetchAll(PDO::FETCH_ASSOC);
                                    }
                                    $labels = [];
                                    $series = [
                                        'temperature' => [],
                                        'humidity' => [],
                                        'pressure' => [],
                                        'light' => [],
                                        'gas' => [],
                                    ];
                                    foreach (array_reverse($ms) as $row) {
                                        $labels[] = $row['timestamp'];
                                        $series['temperature'][] = (float)$row['temperature'];
                                        $series['humidity'][] = (float)$row['humidity'];
                                        $series['pressure'][] = (float)$row['pressure'];
                                        $series['light'][] = (float)$row['light'];
                                        $series['gas'][] = (float)$row['gas'];
                                    }
                                    $chartId = 'shared-chart-' . $col['pk_collection'];
                                ?>
                                <details class="card" open>
                                    <summary><strong><?php echo h($col['name']); ?></strong> (by <?php echo h($col['fk_user_creates']); ?>)</summary>
                                    <div class="muted" style="margin-top:6px;">
                                        Station: <?php echo h($meta['station'] ?? ''); ?>
                                        | Start: <?php echo h($meta['start'] ?? ''); ?>
                                        | End: <?php echo h($meta['end'] ?? ''); ?>
                                    </div>
                                    <div class="muted" style="margin-top: 6px;">Measurements in range (latest 100): <?php echo count($ms); ?></div>
                                    <?php if ($ms): ?>
                                        <div class="chart-grid" style="margin:10px 0;">
                                            <canvas id="<?php echo h($chartId); ?>-temp" height="140"></canvas>
                                            <!--<canvas id="<?php echo h($chartId); ?>-humidity" height="140"></canvas> -->
                                            <canvas id="<?php echo h($chartId); ?>-pressure" height="140"></canvas>
                                            <canvas id="<?php echo h($chartId); ?>-light" height="140"></canvas>
                                            <canvas id="<?php echo h($chartId); ?>-gas" height="140"></canvas>
                                        </div>
                                        <div class="muted" id="<?php echo h($chartId); ?>-status" style="margin-bottom:6px;">Charts: pending...</div>
                                        <div class="table-wrapper">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>Timestamp</th>
                                                        <th>Temp</th>
                                                        <!--<th>Humidity</th> -->
                                                        <th>Pressure</th>
                                                        <th>Light</th>
                                                        <th>Gas</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($ms as $row): ?>
                                                        <tr>
                                                            <td><?php echo h($row['timestamp']); ?></td>
                                                            <td><?php echo h($row['temperature']); ?></td>
                                                            <!--<td><?php echo h($row['humidity']); ?></td> -->
                                                            <td><?php echo h($row['pressure']); ?></td>
                                                            <td><?php echo h($row['light']); ?></td>
                                                            <td><?php echo h($row['gas']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <script>
                                            window.addEventListener('load', function() {
                                                const statusEl = document.getElementById('<?php echo h($chartId); ?>-status');
                                                const chartLib = typeof Chart;
                                                const labels = <?php echo json_encode($labels); ?>;
                                                const series = <?php echo json_encode($series); ?>;
                                                const setStatus = (msg) => { if (statusEl) statusEl.textContent = msg; };
                                                console.log('shared chart init', '<?php echo h($chartId); ?>', chartLib, 'labels', labels.length);
                                                if (chartLib === 'undefined') {
                                                    setStatus('Charts: Chart.js missing');
                                                    console.error('Chart.js missing');
                                                    return;
                                                }
                                                if (!labels.length) {
                                                    setStatus('Charts: no data');
                                                    console.warn('No labels for chart', '<?php echo h($chartId); ?>');
                                                    return;
                                                }
                                                const opts = {
                                                    responsive: false,
                                                    maintainAspectRatio: false,
                                                    scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { beginAtZero: false } },
                                                    plugins: { legend: { display: false } }
                                                };
                                                const cfg = (data, label, color) => ({
                                                    type: 'line',
                                                    data: { labels, datasets: [{
                                                        label,
                                                        data,
                                                        tension: 0.25,
                                                        borderColor: color,
                                                        backgroundColor: color + '33',
                                                        fill: true,
                                                        pointRadius: 2,
                                                        pointHoverRadius: 4,
                                                    }]},
                                                    options: opts
                                                });
                                                const build = (id, cfg) => {
                                                    const el = document.getElementById(id);
                                                    if (!el) {
                                                        console.error('Canvas not found', id);
                                                        return null;
                                                    }
                                                    // Fix disappearing charts by avoiding responsive auto-resize to zero
                                                    const parentWidth = el.parentElement ? el.parentElement.clientWidth : 800;
                                                    el.width = parentWidth || 800;
                                                    el.height = 240;
                                                    const existing = Chart.getChart(el);
                                                    if (existing) existing.destroy();
                                                    const chart = new Chart(el, cfg);
                                                    console.log('chart built', id, !!chart);
                                                    return chart;
                                                };
                                                build('<?php echo h($chartId); ?>-temp', cfg(series.temperature, 'Temperature', '#4ab846'));
                                                //build('<?php echo h($chartId); ?>-humidity', cfg(series.humidity, 'Humidity', '#3b82f6'));
                                                build('<?php echo h($chartId); ?>-pressure', cfg(series.pressure, 'Pressure', '#8b5cf6'));
                                                build('<?php echo h($chartId); ?>-light', cfg(series.light, 'Light', '#f59e0b'));
                                                build('<?php echo h($chartId); ?>-gas', cfg(series.gas, 'Gas', '#ef4444'));
                                                setStatus('Charts: rendered');
                                            });
                                        </script>
                                    <?php else: ?>
                                        <div class="muted" style="margin-top: 6px;">No measurements in this collection.</div>
                                    <?php endif; ?>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
  <?php include 'assets/footer.php'; ?>

</body>
</html>
