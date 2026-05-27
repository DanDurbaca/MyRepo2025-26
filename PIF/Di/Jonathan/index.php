<?php
// index.php - Public landing page showing platform stats and quick links
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/csrf.php';

$pageTitle = 'Home';
require_once __DIR__ . '/_header.php';

// Get some basic stats for display
$stats = ['stations' => 0, 'measurements' => 0, 'users' => 0, 'collections' => 0];
try {
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
    $stats['stations'] = $pdo->query("SELECT COUNT(*) FROM station")->fetchColumn();
    $stats['measurements'] = $pdo->query("SELECT COUNT(*) FROM measurement")->fetchColumn();
    $stats['collections'] = $pdo->query("SELECT COUNT(*) FROM collection")->fetchColumn();
} catch (Exception $e) {
    // Ignore stats errors
}

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
?>

<div class="container">
    <h1>Indoor Climate Data Platform</h1>
    <p>Monitor, analyze, and share your indoor environmental data.</p>

    <?php if ($isLoggedIn): ?>
    <div class="alert alert-info">Welcome back, <strong><?php echo htmlspecialchars($username); ?></strong>!</div>

    <h2>Quick Links</h2>
    <p>
        <a href="user/dashboard.php" class="btn btn-primary">Dashboard</a>
        <a href="user/stations.php" class="btn btn-success">Stations</a>
        <a href="user/collections.php" class="btn btn-secondary">Collections</a>
        <a href="user/friends.php" class="btn btn-warning">Friends</a>
    </p>

    <h2>Your Stats</h2>
    <?php
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM station WHERE fk_user_owns = ?");
        $stmt->execute([$username]);
        $myStations = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM collection WHERE fk_user_creates = ?");
        $stmt->execute([$username]);
        $myCollections = $stmt->fetchColumn();
    } catch (Exception $e) {
        $myStations = 0;
        $myCollections = 0;
    }
    ?>
    <div class="row">
        <div class="col">
            <div class="stat-box">
                <div class="number"><?php echo $myStations; ?></div>
                <div class="label">Your Stations</div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <div class="number"><?php echo $myCollections; ?></div>
                <div class="label">Your Collections</div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <h2>Get Started</h2>
    <p>
        <a href="register.php" class="btn btn-success">Register</a>
        <a href="login.php" class="btn btn-primary">Login</a>
    </p>

    <h2>Platform Stats</h2>
    <div class="row">
        <div class="col">
            <div class="stat-box">
                <div class="number"><?php echo number_format($stats['users']); ?></div>
                <div class="label">Users</div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <div class="number"><?php echo number_format($stats['stations']); ?></div>
                <div class="label">Stations</div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <div class="number"><?php echo number_format($stats['measurements']); ?></div>
                <div class="label">Measurements</div>
            </div>
        </div>
        <div class="col">
            <div class="stat-box">
                <div class="number"><?php echo number_format($stats['collections']); ?></div>
                <div class="label">Collections</div>
            </div>
        </div>
    </div>

    <h2>Features</h2>
    <ul>
        <li><strong>Real-time Monitoring</strong> - Track temperature, humidity, and air quality</li>
        <li><strong>Data Collections</strong> - Organize measurements into collections</li>
        <li><strong>Sharing</strong> - Share data with friends</li>
        <li><strong>API Access</strong> - Submit data from your devices</li>
    </ul>
    <?php endif; ?>
</div>

<?php
// Show flash message if set
if (isset($_SESSION['flash'])) {
    echo '<script>Swal.fire({title: "Notice", text: "' . addslashes($_SESSION['flash']) . '", icon: "info", confirmButtonText: "OK"});</script>';
    unset($_SESSION['flash']);
}
?>
</body>
</html>