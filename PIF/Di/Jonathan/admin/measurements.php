<?php
$pageTitle = 'Manage Measurements';
require_once __DIR__ . '/_header.php';

$username = $_SESSION['username'];

$message = '';
$messageType = 'info';

// Delete a measurement (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_measurement'])) {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
        $messageType = 'danger';
    } else {
        $id = intval($_POST['delete_measurement']);
        if ($id <= 0) {
            $message = 'Invalid measurement id.';
            $messageType = 'danger';
        } else {
            $del = $pdo->prepare('DELETE FROM measurement WHERE pk_measurement = ?');
            $del->execute([$id]);
            if ($del->rowCount() > 0) {
                $message = 'Measurement deleted.';
                $messageType = 'success';
            } else {
                $message = 'Measurement not found (already deleted?).';
                $messageType = 'warning';
            }
        }
    }
}

// Filters (GET)
$station = trim($_GET['station'] ?? '');
$startRaw = trim($_GET['start'] ?? '');
$endRaw = trim($_GET['end'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 100;
$offset = ($page - 1) * $perPage;

$conditions = [];
$params = [];

if ($station !== '' && $station !== 'all') {
    if (preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $station)) {
        $conditions[] = 'm.fk_station_records = ?';
        $params[] = $station;
    } else {
        $message = 'Invalid station identifier.';
        $messageType = 'danger';
        $station = '';
    }
}

$start = null;
$end = null;
if ($startRaw !== '') {
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $startRaw);
    if ($dt) {
        $start = $dt->format('Y-m-d H:i:s');
        $conditions[] = 'm.timestamp >= ?';
        $params[] = $start;
    } else {
        $message = 'Invalid start date.';
        $messageType = 'danger';
    }
}
if ($endRaw !== '') {
    $dt2 = DateTime::createFromFormat('Y-m-d\TH:i', $endRaw);
    if ($dt2) {
        $end = $dt2->format('Y-m-d H:i:s');
        $conditions[] = 'm.timestamp <= ?';
        $params[] = $end;
    } else {
        $message = 'Invalid end date.';
        $messageType = 'danger';
    }
}

$whereSql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

// Count for pagination
$countSql = "SELECT COUNT(*) FROM measurement m $whereSql";
$total = 0;
try {
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = intval($stmt->fetchColumn());
} catch (Exception $e) {
    error_log('Admin measurements count failed: ' . $e->getMessage());
}

$pages = max(1, (int)ceil($total / $perPage));
if ($page > $pages) {
    $page = $pages;
    $offset = ($page - 1) * $perPage;
}

// Fetch rows
$rows = [];
$dataSql = "SELECT m.pk_measurement, m.timestamp, m.temperature, m.humidity, m.gas AS air_quality, m.fk_station_records
            FROM measurement m
            $whereSql
            ORDER BY m.timestamp DESC
            LIMIT $perPage OFFSET $offset";
try {
    $stmt = $pdo->prepare($dataSql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Admin measurements query failed: ' . $e->getMessage());
    $message = 'Failed to load measurements.';
    $messageType = 'danger';
}

// Station list
$stations = [];
try {
    $stations = $pdo->query('SELECT pk_serialNumber, name FROM station ORDER BY pk_serialNumber')->fetchAll();
} catch (Exception $e) {
    error_log('Admin stations list failed: ' . $e->getMessage());
}

// Simple HTML-escape helper for rendering values safely
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="container">
    <h1>Measurement Data</h1>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo h($messageType); ?>">
            <?php echo h($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Filters</h3>
        <form method="get">
            <div class="form-group">
                <label for="station">Station</label>
                <select id="station" name="station">
                    <option value="all" <?php echo ($station === 'all' || $station === '') ? 'selected' : ''; ?>>All stations</option>
                    <?php foreach ($stations as $s): ?>
                        <?php $val = $s['pk_serialNumber']; ?>
                        <option value="<?php echo h($val); ?>" <?php echo ($station === $val) ? 'selected' : ''; ?>>
                            <?php echo h(($s['name'] ?? '') . ' (' . $val . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="start">Start</label>
                <input type="datetime-local" id="start" name="start" value="<?php echo h($startRaw); ?>">
            </div>
            <div class="form-group">
                <label for="end">End</label>
                <input type="datetime-local" id="end" name="end" value="<?php echo h($endRaw); ?>">
            </div>
            <button class="btn" type="submit">Apply Filter</button>
        </form>
    </div>

    <div class="card">
        <h3>Results (Total: <?php echo h(number_format($total)); ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Timestamp</th>
                    <th>Station</th>
                    <th>Temp (°C)</th>
                    <th>Humidity (%)</th>
                    <th>Air Quality</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr><td colspan="7">No measurements found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo h($r['pk_measurement']); ?></td>
                            <td><?php echo h($r['timestamp']); ?></td>
                            <td><?php echo h($r['fk_station_records']); ?></td>
                            <td><?php echo h($r['temperature']); ?></td>
                            <td><?php echo h($r['humidity']); ?></td>
                            <td><?php echo h($r['air_quality']); ?></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this measurement?');">
                                    <?php echo csrf_input(); ?>
                                    <button class="btn btn-danger btn-small" type="submit" name="delete_measurement" value="<?php echo h($r['pk_measurement']); ?>">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        // Build pagination query string preserving current filter parameters
        $mk = function($p) use ($station, $startRaw, $endRaw) {
            $q = [];
            if ($station !== '') $q['station'] = $station;
            if ($startRaw !== '') $q['start'] = $startRaw;
            if ($endRaw !== '') $q['end'] = $endRaw;
            $q['page'] = $p;
            return '?' . http_build_query($q);
        };
        ?>

        <?php if ($pages > 1): ?>
            <p>
                <?php if ($page > 1): ?>
                    <a href="<?php echo h($mk($page - 1)); ?>" class="btn btn-small">Previous</a>
                <?php endif; ?>
                Page <?php echo $page; ?> of <?php echo $pages; ?>
                <?php if ($page < $pages): ?>
                    <a href="<?php echo h($mk($page + 1)); ?>" class="btn btn-small">Next</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
