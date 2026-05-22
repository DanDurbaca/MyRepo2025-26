<?php
require_once 'config.php';
require_once 'auth.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$auth = new Auth($pdo);
$pageTitle = 'Dashboard';
?>
<?php include 'includes/header.php'; ?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white rounded mb-4">
        <div class="container-fluid">
            <h2 class="navbar-brand mb-0">Dashboard</h2>
            <span class="text-muted">Welcome back, <?php echo $_SESSION['firstName']; ?>!</span>
        </div>
    </nav>
    
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="card-title text-muted">My Stations</h5>
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM station WHERE fk_user_owns = ?");
                    $stmt->execute([$_SESSION['username']]);
                    $stationCount = $stmt->fetchColumn();
                    ?>
                    <h2 class="text-primary"><?php echo $stationCount; ?></h2>
                    <a href="stations.php" class="btn btn-sm btn-outline-primary">View Stations</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card" style="border-left-color: #2ecc71;">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Measurements</h5>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) 
                        FROM measurement m
                        JOIN station s ON m.fk_station_records = s.pk_serialNumber
                        WHERE s.fk_user_owns = ?
                    ");
                    $stmt->execute([$_SESSION['username']]);
                    $measurementCount = $stmt->fetchColumn();
                    ?>
                    <h2 class="text-success"><?php echo $measurementCount; ?></h2>
                    <a href="measurements.php" class="btn btn-sm btn-outline-success">View Data</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card" style="border-left-color: #e74c3c;">
                <div class="card-body">
                    <h5 class="card-title text-muted">Collections</h5>
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM collection WHERE fk_user_creates = ?");
                    $stmt->execute([$_SESSION['username']]);
                    $collectionCount = $stmt->fetchColumn();
                    ?>
                    <h2 class="text-danger"><?php echo $collectionCount; ?></h2>
                    <a href="collections.php" class="btn btn-sm btn-outline-danger">View Collections</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card" style="border-left-color: #f39c12;">
                <div class="card-body">
                    <h5 class="card-title text-muted">Friends</h5>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) 
                        FROM isfriend 
                        WHERE pkfk_user_user = ? OR pkfk_user_friend = ?
                    ");
                    $stmt->execute([$_SESSION['username'], $_SESSION['username']]);
                    $friendCount = $stmt->fetchColumn();
                    ?>
                    <h2 class="text-warning"><?php echo $friendCount; ?></h2>
                    <a href="friends.php" class="btn btn-sm btn-outline-warning">View Friends</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Measurements -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Measurements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Station</th>
                            <th>Temperature</th>
                            <th>Humidity</th>
                            <th>Pressure</th>
                            <th>Light</th>
                            <th>Gas</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT m.*, s.name as station_name 
                            FROM measurement m
                            JOIN station s ON m.fk_station_records = s.pk_serialNumber
                            WHERE s.fk_user_owns = ?
                            ORDER BY m.timestamp DESC
                            LIMIT 10
                        ");
                        $stmt->execute([$_SESSION['username']]);
                        $measurements = $stmt->fetchAll();
                        
                        if (empty($measurements)) {
                            echo '<tr><td colspan="7" class="text-center">No measurements found</td></tr>';
                        } else {
                            foreach ($measurements as $m) {
                                echo "<tr>";
                                echo "<td>{$m['station_name']}</td>";
                                echo "<td>{$m['temperature']}°C</td>";
                                echo "<td>{$m['humidity']}%</td>";
                                echo "<td>{$m['pressure']} hPa</td>";
                                echo "<td>{$m['light']} lux</td>";
                                echo "<td>{$m['gas']}</td>";
                                echo "<td>" . date('Y-m-d H:i', strtotime($m['timestamp'])) . "</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>