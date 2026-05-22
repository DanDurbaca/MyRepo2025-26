<?php
// admin/dashboard.php
// Main admin overview page showing system statistics and recent activity

require_once __DIR__ . '/../config/database.php';     // Database connection helper
require_once __DIR__ . '/../includes/auth_check.php'; // Ensures user is logged in
require_once __DIR__ . '/admin_functions.php';        // Admin-only helper functions

checkAdminAccess(); // Blocks access if the user is not an admin

$conn = getDbConnection(); // Create a reusable database connection

try {
    // Total number of users in the system
    $stmt = $conn->prepare("SELECT COUNT(*) as user_count FROM user");
    $stmt->execute();
    $user_count = $stmt->fetch()['user_count'];
    
    // Number of users with Admin role
    $stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM user WHERE role = 'Admin'");
    $stmt->execute();
    $admin_count = $stmt->fetch()['admin_count'];
    
    // Total registered stations
    $stmt = $conn->prepare("SELECT COUNT(*) as station_count FROM station");
    $stmt->execute();
    $station_count = $stmt->fetch()['station_count'];
    
    // Total measurement records stored
    $stmt = $conn->prepare("SELECT COUNT(*) as measurement_count FROM measurement");
    $stmt->execute();
    $measurement_count = $stmt->fetch()['measurement_count'];
    
    // Total number of collections
    $stmt = $conn->prepare("SELECT COUNT(*) as collection_count FROM collection");
    $stmt->execute();
    $collection_count = $stmt->fetch()['collection_count'];
    
    // Most recently added users (used for read-only dashboard preview)
    $stmt = $conn->prepare("SELECT * FROM user ORDER BY pk_username DESC LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->fetchAll();
    
    // Most recently added stations, including owner if assigned
    $stmt = $conn->prepare("
        SELECT s.*, u.pk_username as owner_username 
        FROM station s 
        LEFT JOIN user u ON s.fk_user_owns = u.pk_username 
        ORDER BY s.pk_serialNumber DESC LIMIT 5
    ");
    $stmt->execute();
    $recent_stations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Any database failure here affects only dashboard statistics
    $error = "Error loading dashboard statistics: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Portable Indoor Feedback</title>

    <!-- Global site styles -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- Admin-specific layout and UI styles -->
    <link rel="stylesheet" href="../assets/css/admin.css">

    <!-- Font Awesome icons used throughout dashboard cards -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
            <p>System administration and management</p>
        </div>
        
        <!-- Statistic cards showing system totals -->
        <div class="admin-stats-grid">

            <!-- Users statistic -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background: #4a90e2;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $user_count; ?></h3>
                    <p>Total Users</p>
                    <small><?php echo $admin_count; ?> administrators</small>
                </div>
            </div>
            
            <!-- Stations statistic -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background: #2eaf52;">
                    <i class="fas fa-satellite-dish"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $station_count; ?></h3>
                    <p>Stations</p>
                    <small>Registered devices</small>
                </div>
            </div>
            
            <!-- Measurements statistic -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background: #9b59b6;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $measurement_count; ?></h3>
                    <p>Measurements</p>
                    <small>Data points</small>
                </div>
            </div>
            
            <!-- Collections statistic -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background: #e74c3c;">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $collection_count; ?></h3>
                    <p>Collections</p>
                    <small>Grouped data</small>
                </div>
            </div>
        </div>
        
        <!-- Navigation shortcuts to admin-related pages -->
        <div class="admin-actions">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="actions-grid">

                <a href="users.php" class="action-card">
                    <div class="action-icon" style="background: #4a90e2;">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h3>Manage Users</h3>
                    <p>Create, edit, and delete user accounts</p>
                </a>

                <a href="stations.php" class="action-card">
                    <div class="action-icon" style="background: #2eaf52;">
                        <i class="fas fa-satellite"></i>
                    </div>
                    <h3>Manage Stations</h3>
                    <p>Create and manage all stations</p>
                </a>

                <a href="../controller/collections.php" class="action-card">
                    <div class="action-icon" style="background: #9b59b6;">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>View Collections</h3>
                    <p>Browse all user collections</p>
                </a>

                <a href="../station_measurements.php" class="action-card">
                    <div class="action-icon" style="background: #f39c12;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>View Measurements</h3>
                    <p>Access all measurement data</p>
                </a>
            </div>
        </div>

        <!-- Read-only previews of recent system activity -->
        <div class="admin-sections">

            <!-- Recently created users -->
            <div class="admin-section">
                <h2><i class="fas fa-user-clock"></i> Recent Users</h2>
                <div class="recent-list">
                    <?php foreach ($recent_users as $user): ?>
                        <div class="recent-item">
                            <div class="recent-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="recent-info">
                                <h4><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h4>
                                <p>@<?= htmlspecialchars($user['pk_username']); ?></p>
                                <span class="user-role <?= $user['role'] === 'Admin' ? 'admin' : 'user'; ?>">
                                    <?= $user['role']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="users_readonly.php" class="btn btn-primary btn-sm">View All Users</a>
            </div>

            <!-- Recently registered stations -->
            <div class="admin-section">
                <h2><i class="fas fa-satellite"></i> Recent Stations</h2>
                <div class="recent-list">
                    <?php foreach ($recent_stations as $station): ?>
                        <div class="recent-item">
                            <div class="recent-avatar" style="background: #2eaf52;">
                                <i class="fas fa-satellite-dish"></i>
                            </div>
                            <div class="recent-info">
                                <h4><?= htmlspecialchars($station['name'] ?? 'Unnamed Station'); ?></h4>
                                <p><?= htmlspecialchars($station['pk_serialNumber']); ?></p>

                                <!-- Owner badge depends on whether the station is assigned -->
                                <?php if ($station['owner_username']): ?>
                                    <span class="station-owner">
                                        Owner: <?= htmlspecialchars($station['owner_username']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="station-unassigned">Unassigned</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="stations_readonly.php" class="btn btn-primary btn-sm">View All Stations</a>
            </div>
        </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>