<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Measurements';
$message = '';

// Handle delete measurement (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_measurement' && isAdmin()) {
        $measurementId = (int)$_POST['measurement_id'];
        $stmt = $pdo->prepare("DELETE FROM measurement WHERE pk_measurement = ?");
        if ($stmt->execute([$measurementId])) {
            $message = 'Measurement deleted successfully!';
        } else {
            $message = 'Failed to delete measurement.';
        }
    }
}

// Filter parameters
$stationFilter = $_GET['station'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build query based on user role
$whereConditions = [];
$params = [];

if (isAdmin()) {
    $baseQuery = "SELECT m.*, s.name as station_name, s.fk_user_owns as station_owner 
                  FROM measurement m 
                  JOIN station s ON m.fk_station_records = s.pk_serialNumber";
    
    if ($stationFilter) {
        $whereConditions[] = "m.fk_station_records = ?";
        $params[] = $stationFilter;
    }
} else {
    $baseQuery = "SELECT m.*, s.name as station_name 
                  FROM measurement m 
                  JOIN station s ON m.fk_station_records = s.pk_serialNumber 
                  WHERE s.fk_user_owns = ?";
    $params[] = $_SESSION['username'];
    
    if ($stationFilter) {
        $whereConditions[] = "m.fk_station_records = ?";
        $params[] = $stationFilter;
    }
}

// Add date filters
if ($dateFrom) {
    $whereConditions[] = "m.timestamp >= ?";
    $params[] = $dateFrom . ' 00:00:00';
}
if ($dateTo) {
    $whereConditions[] = "m.timestamp <= ?";
    $params[] = $dateTo . ' 23:59:59';
}

// Build final query
if (!empty($whereConditions)) {
    $baseQuery .= (isAdmin() ? " WHERE " : " AND ") . implode(" AND ", $whereConditions);
}

$baseQuery .= " ORDER BY m.timestamp DESC";

// Get stations for filter dropdown
$stations = [];
if (isAdmin()) {
    // Added condition to exclude stations with null names
    $stmt = $pdo->query("SELECT pk_serialNumber, name FROM station WHERE name IS NOT NULL ORDER BY name");
} else {
    // Added condition to exclude stations with null names
    $stmt = $pdo->prepare("SELECT pk_serialNumber, name FROM station WHERE fk_user_owns = ? AND name IS NOT NULL ORDER BY name");
    $stmt->execute([$_SESSION['username']]);
}
$stations = $stmt->fetchAll();

// Execute main query
$stmt = $pdo->prepare($baseQuery);
$stmt->execute($params);
$measurements = $stmt->fetchAll();

// Calculate statistics
$stats = [
    'avg_temp' => 0,
    'avg_humidity' => 0,
    'avg_pressure' => 0,
    'avg_light' => 0,
    'avg_gas' => 0,
    'count' => count($measurements)
];

if (!empty($measurements)) {
    $temps = array_column($measurements, 'temperature');
    $humidities = array_column($measurements, 'humidity');
    $pressures = array_column($measurements, 'pressure');
    $lights = array_column($measurements, 'light');
    $gases = array_column($measurements, 'gas');
    
    $stats['avg_temp'] = round(array_sum($temps) / count($temps), 2);
    $stats['avg_humidity'] = round(array_sum($humidities) / count($humidities), 2);
    $stats['avg_pressure'] = round(array_sum($pressures) / count($pressures), 2);
    $stats['avg_light'] = round(array_sum($lights) / count($lights), 2);
    $stats['avg_gas'] = round(array_sum($gases) / count($gases), 2);
}

$pageJS = 'measurements.js';
?>
<?php include 'includes/header.php'; ?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white rounded mb-4">
        <div class="container-fluid">
            <h2 class="navbar-brand mb-0">Measurements</h2>
            <span class="text-muted"><?php echo $stats['count']; ?> records found</span>
        </div>
    </nav>
    
    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Avg Temp</h6>
                    <h3 class="text-primary"><?php echo $stats['avg_temp']; ?>°C</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Avg Humidity</h6>
                    <h3 class="text-success"><?php echo $stats['avg_humidity']; ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Avg Pressure</h6>
                    <h3 class="text-warning"><?php echo $stats['avg_pressure']; ?> hPa</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Avg Light</h6>
                    <h3 class="text-info"><?php echo $stats['avg_light']; ?> lux</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Avg Gas</h6>
                    <h3 class="text-danger"><?php echo $stats['avg_gas']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Total Records</h6>
                    <h3 class="text-secondary"><?php echo $stats['count']; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Station</label>
                <select name="station" class="form-select">
                    <option value="">All Stations</option>
                        <?php foreach ($stations as $station): ?>
                        <?php if (!empty($station['name'])): ?> <!-- Additional check -->
                    <option value="<?php echo $station['pk_serialNumber']; ?>" 
                        <?php echo (isset($stationFilter) && $stationFilter == $station['pk_serialNumber']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($station['name']); ?>
                    </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="measurements.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Measurements Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Measurement Data</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="measurementsTable">
                    <thead>
                        <tr>
                            <?php if (isAdmin()): ?>
                                <th>Owner</th>
                            <?php endif; ?>
                            <th>Station</th>
                            <th>Temperature (°C)</th>
                            <th>Humidity (%)</th>
                            <th>Pressure (hPa)</th>
                            <th>Light (lux)</th>
                            <th>Gas</th>
                            <th>Timestamp</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($measurements)): ?>
                            <tr>
                                <td colspan="<?php echo isAdmin() ? 9 : 8; ?>" class="text-center">No measurements found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($measurements as $measurement): ?>
                            <tr>
                                <?php if (isAdmin()): ?>
                                    <td>
                                        <?php 
                                        if ($measurement['station_owner']) {
                                            echo htmlspecialchars($measurement['station_owner']);
                                        } else {
                                            echo '<span class="text-muted">Unassigned</span>';
                                        }
                                        ?>
                                    </td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($measurement['station_name']); ?></td>
                                <td><?php echo $measurement['temperature']; ?></td>
                                <td><?php echo $measurement['humidity']; ?></td>
                                <td><?php echo $measurement['pressure']; ?></td>
                                <td><?php echo $measurement['light']; ?></td>
                                <td><?php echo $measurement['gas']; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($measurement['timestamp'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick="viewMeasurement(<?php echo $measurement['pk_measurement']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (isAdmin()): ?>
                                            <button class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $measurement['pk_measurement']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Measurement Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Measurement Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="measurementDetails">
                <!-- Details will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this measurement? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete_measurement">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="measurement_id" id="deleteMeasurementId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>