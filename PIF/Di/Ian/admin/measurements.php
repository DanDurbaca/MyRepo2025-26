<?php
session_start();
require __DIR__ . '/../assets/db.php';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function parseDatetimeLocal(?string $value): ?string
{
    if (!$value) {
        return null;
    }

    $value = trim($value);
    $formats = ['Y-m-d\\TH:i:s', 'Y-m-d\\TH:i'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $value);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d H:i:s');
        }
    }

    $ts = strtotime($value);
    if ($ts === false) {
        return null;
    }

    return date('Y-m-d H:i:s', $ts);
}

function toDatetimeLocal(?string $value): string
{
    if (!$value) {
        return '';
    }

    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    if (!$dt) {
        $ts = strtotime($value);
        if ($ts === false) {
            return '';
        }
        return date('Y-m-d\\TH:i', $ts);
    }

    return $dt->format('Y-m-d\\TH:i');
}

function postNumber(string $key, array &$errors): ?float
{
    $raw = trim((string) ($_POST[$key] ?? ''));
    if ($raw === '' || !is_numeric($raw)) {
        $errors[] = ucfirst($key) . ' must be a numeric value.';
        return null;
    }
    return (float) $raw;
}

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$dbError = '';
$successMsg = '';
$measurements = [];
$stations = [];
$editMeasurement = null;

$sortOptions = [
    'user_asc' => '(s.fk_user_owns IS NULL), s.fk_user_owns ASC, m.timestamp DESC',
    'user_desc' => '(s.fk_user_owns IS NULL), s.fk_user_owns DESC, m.timestamp DESC',
    'station_asc' => 'm.fk_station_records ASC, m.timestamp DESC',
    'station_desc' => 'm.fk_station_records DESC, m.timestamp DESC',
    'time_desc' => 'm.timestamp DESC',
    'time_asc' => 'm.timestamp ASC',
];

$sort = $_GET['sort'] ?? 'user_asc';
if (!isset($sortOptions[$sort])) {
    $sort = 'user_asc';
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

try {
    $pdo = getDb();

    $roleStmt = $pdo->prepare('SELECT role FROM user WHERE pk_username = :username');
    $roleStmt->execute([':username' => $_SESSION['username']]);
    $me = $roleStmt->fetch(PDO::FETCH_ASSOC);

    if (($me['role'] ?? '') !== 'Admin') {
        header('Location: index.php');
        exit;
    }

    if (isset($_SESSION['admin_measurement_msg'])) {
        $successMsg = (string) $_SESSION['admin_measurement_msg'];
        unset($_SESSION['admin_measurement_msg']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = trim((string) $_POST['action']);

        if ($action === 'create' || $action === 'update') {
            $errors = [];
            $stationId = trim((string) ($_POST['station_id'] ?? ''));
            $timestamp = parseDatetimeLocal($_POST['timestamp'] ?? null);
            $temperature = postNumber('temperature', $errors);
            $humidity = postNumber('humidity', $errors);
            $pressure = postNumber('pressure', $errors);
            $light = postNumber('light', $errors);
            $gas = postNumber('gas', $errors);

            if ($stationId === '') {
                $errors[] = 'Station is required.';
            }
            if (!$timestamp) {
                $errors[] = 'Timestamp is invalid.';
            }

            if (empty($errors)) {
                $stationCheck = $pdo->prepare('SELECT pk_serialNumber FROM station WHERE pk_serialNumber = :id');
                $stationCheck->execute([':id' => $stationId]);
                if (!$stationCheck->fetch()) {
                    $errors[] = 'Station not found.';
                }
            }

            if (!empty($errors)) {
                $dbError = implode(' ', $errors);
            } elseif ($action === 'create') {
                $insertStmt = $pdo->prepare(
                    'INSERT INTO measurement (temperature, humidity, pressure, light, gas, timestamp, fk_station_records)
                     VALUES (:temperature, :humidity, :pressure, :light, :gas, :timestamp, :station)'
                );
                $insertStmt->execute([
                    ':temperature' => $temperature,
                    ':humidity' => $humidity,
                    ':pressure' => $pressure,
                    ':light' => $light,
                    ':gas' => $gas,
                    ':timestamp' => $timestamp,
                    ':station' => $stationId,
                ]);

                $_SESSION['admin_measurement_msg'] = 'Measurement created successfully.';
                header('Location: measurements.php?sort=' . urlencode($sort));
                exit;
            } else {
                $measurementId = (int) ($_POST['measurement_id'] ?? 0);
                if ($measurementId < 1) {
                    $dbError = 'Measurement ID is required.';
                } else {
                    $updateStmt = $pdo->prepare(
                        'UPDATE measurement
                         SET temperature = :temperature,
                             humidity = :humidity,
                             pressure = :pressure,
                             light = :light,
                             gas = :gas,
                             timestamp = :timestamp,
                             fk_station_records = :station
                         WHERE pk_measurement = :id'
                    );
                    $updateStmt->execute([
                        ':temperature' => $temperature,
                        ':humidity' => $humidity,
                        ':pressure' => $pressure,
                        ':light' => $light,
                        ':gas' => $gas,
                        ':timestamp' => $timestamp,
                        ':station' => $stationId,
                        ':id' => $measurementId,
                    ]);

                    $_SESSION['admin_measurement_msg'] = 'Measurement updated successfully.';
                    header('Location: measurements.php?sort=' . urlencode($sort));
                    exit;
                }
            }
        } elseif ($action === 'delete') {
            $measurementId = (int) ($_POST['measurement_id'] ?? 0);

            if ($measurementId < 1) {
                $dbError = 'Measurement ID is required.';
            } else {
                $deleteStmt = $pdo->prepare('DELETE FROM measurement WHERE pk_measurement = :id');
                $deleteStmt->execute([':id' => $measurementId]);

                $_SESSION['admin_measurement_msg'] = 'Measurement deleted successfully.';
                header('Location: measurements.php?sort=' . urlencode($sort));
                exit;
            }
        }
    }

    $stationsStmt = $pdo->query(
        'SELECT s.pk_serialNumber, s.name, s.fk_user_owns, u.firstName, u.lastName
         FROM station s
         LEFT JOIN user u ON u.pk_username = s.fk_user_owns
         ORDER BY (s.fk_user_owns IS NULL), s.fk_user_owns ASC, s.pk_serialNumber ASC'
    );
    $stations = $stationsStmt->fetchAll(PDO::FETCH_ASSOC);

    $measurementsStmt = $pdo->query(
        'SELECT m.pk_measurement, m.temperature, m.humidity, m.pressure, m.light, m.gas, m.timestamp, m.fk_station_records,
                s.fk_user_owns AS owner_user, s.name AS station_name
         FROM measurement m
         LEFT JOIN station s ON s.pk_serialNumber = m.fk_station_records
         ORDER BY ' . $sortOptions[$sort]
    );
    $measurements = $measurementsStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($editId > 0) {
        foreach ($measurements as $row) {
            if ((int) $row['pk_measurement'] === $editId) {
                $editMeasurement = $row;
                break;
            }
        }
    }
} catch (PDOException $e) {
    $dbError = 'Database error. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
    <title>Admin Measurements</title>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<main class="page">
    <div class="stations-container">
        <section class="card">
            <h2 class="card-title">Measurement Management</h2>

            <?php if ($successMsg): ?>
                <p class="success-text"><?php echo h($successMsg); ?></p>
            <?php endif; ?>

            <?php if ($dbError): ?>
                <p class="error-text"><?php echo h($dbError); ?></p>
            <?php endif; ?>

            <form method="get" class="register-form" style="margin-bottom: 12px;">
                <div class="form-group">
                    <label class="field-label" for="sort">Sort</label>
                    <select id="sort" name="sort" class="input-select" onchange="this.form.submit()">
                        <option value="user_asc" <?php echo $sort === 'user_asc' ? 'selected' : ''; ?>>User (A-Z)</option>
                        <option value="user_desc" <?php echo $sort === 'user_desc' ? 'selected' : ''; ?>>User (Z-A)</option>
                        <option value="station_asc" <?php echo $sort === 'station_asc' ? 'selected' : ''; ?>>Station (A-Z)</option>
                        <option value="station_desc" <?php echo $sort === 'station_desc' ? 'selected' : ''; ?>>Station (Z-A)</option>
                        <option value="time_desc" <?php echo $sort === 'time_desc' ? 'selected' : ''; ?>>Newest first</option>
                        <option value="time_asc" <?php echo $sort === 'time_asc' ? 'selected' : ''; ?>>Oldest first</option>
                    </select>
                </div>
            </form>

            <?php if (empty($measurements)): ?>
                <p class="muted">No measurements found.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Station</th>
                                <th>Timestamp</th>
                                <th>Temp</th>
                                <!--<th>Humidity</th> -->
                                <th>Pressure</th>
                                <th>Light</th>
                                <th>Gas</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($measurements as $row): ?>
                                <tr>
                                    <td><?php echo h($row['pk_measurement']); ?></td>
                                    <td><?php echo h($row['owner_user'] ?? 'Unassigned'); ?></td>
                                    <td>
                                        <?php
                                            $stationLabel = $row['fk_station_records'] ?? '';
                                            if (!empty($row['station_name'])) {
                                                $stationLabel .= ' - ' . $row['station_name'];
                                            }
                                            echo h($stationLabel);
                                        ?>
                                    </td>
                                    <td><?php echo h($row['timestamp']); ?></td>
                                    <td><?php echo h($row['temperature']); ?></td>
                                    <!--<td><?#php echo h($row['humidity']); ?></td> -->
                                    <td><?php echo h($row['pressure']); ?></td>
                                    <td><?php echo h($row['light']); ?></td>
                                    <td><?php echo h($row['gas']); ?></td>
                                    <td>
                                        <a class="primary-btn" style="display:inline-block; text-decoration:none;" href="measurements.php?sort=<?php echo urlencode($sort); ?>&edit=<?php echo h($row['pk_measurement']); ?>">Edit</a>
                                        <form method="post" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Delete this measurement? This cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="measurement_id" value="<?php echo h($row['pk_measurement']); ?>">
                                            <button type="submit" class="danger-btn">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($editMeasurement): ?>
        <section class="card">
            <h2 class="card-title">Edit Measurement #<?php echo h($editMeasurement['pk_measurement']); ?></h2>
            <form method="post" class="register-form">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="measurement_id" value="<?php echo h($editMeasurement['pk_measurement']); ?>">

                <div class="form-group">
                    <label class="field-label" for="edit-station">Station:</label>
                    <select id="edit-station" name="station_id" class="input-select" required>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo h($station['pk_serialNumber']); ?>" <?php echo ($editMeasurement['fk_station_records'] ?? '') === $station['pk_serialNumber'] ? 'selected' : ''; ?>>
                                <?php
                                    $label = $station['pk_serialNumber'];
                                    if (!empty($station['name'])) {
                                        $label .= ' - ' . $station['name'];
                                    }
                                    if (!empty($station['fk_user_owns'])) {
                                        $label .= ' (' . $station['fk_user_owns'] . ')';
                                    }
                                    echo h($label);
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="edit-timestamp">Timestamp:</label>
                    <input id="edit-timestamp" type="datetime-local" name="timestamp" class="input-text" value="<?php echo h(toDatetimeLocal($editMeasurement['timestamp'] ?? '')); ?>" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="edit-temperature">Temperature:</label>
                    <input id="edit-temperature" type="number" step="0.01" name="temperature" class="input-text" value="<?php echo h($editMeasurement['temperature']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="edit-humidity">Humidity:</label>
                    <input id="edit-humidity" type="number" step="0.01" name="humidity" class="input-text" value="<?php echo h($editMeasurement['humidity']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="edit-pressure">Pressure:</label>
                    <input id="edit-pressure" type="number" step="0.01" name="pressure" class="input-text" value="<?php echo h($editMeasurement['pressure']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="edit-light">Light:</label>
                    <input id="edit-light" type="number" step="0.01" name="light" class="input-text" value="<?php echo h($editMeasurement['light']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="edit-gas">Gas:</label>
                    <input id="edit-gas" type="number" step="0.01" name="gas" class="input-text" value="<?php echo h($editMeasurement['gas']); ?>" required>
                </div>

                <button type="submit" class="primary-btn">Save Changes</button>
                <a class="login-btn" style="margin-left:8px; text-decoration:none;" href="measurements.php?sort=<?php echo urlencode($sort); ?>">Cancel</a>
            </form>
        </section>
        <?php endif; ?>

        <section class="card">
            <h2 class="card-title">Create Measurement</h2>
            <form method="post" class="register-form">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label class="field-label" for="create-station">Station:</label>
                    <select id="create-station" name="station_id" class="input-select" required>
                        <option value="" selected disabled>Select a station</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo h($station['pk_serialNumber']); ?>">
                                <?php
                                    $label = $station['pk_serialNumber'];
                                    if (!empty($station['name'])) {
                                        $label .= ' - ' . $station['name'];
                                    }
                                    if (!empty($station['fk_user_owns'])) {
                                        $label .= ' (' . $station['fk_user_owns'] . ')';
                                    }
                                    echo h($label);
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="field-label" for="create-timestamp">Timestamp:</label>
                    <input id="create-timestamp" type="datetime-local" name="timestamp" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="create-temperature">Temperature:</label>
                    <input id="create-temperature" type="number" step="0.01" name="temperature" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="create-humidity">Humidity:</label>
                    <input id="create-humidity" type="number" step="0.01" name="humidity" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="create-pressure">Pressure:</label>
                    <input id="create-pressure" type="number" step="0.01" name="pressure" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="create-light">Light:</label>
                    <input id="create-light" type="number" step="0.01" name="light" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="create-gas">Gas:</label>
                    <input id="create-gas" type="number" step="0.01" name="gas" class="input-text" required>
                </div>

                <button type="submit" class="primary-btn">Create Measurement</button>
            </form>
        </section>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>