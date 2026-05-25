<?php
session_start();
require __DIR__ . '/../assets/db.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$pdo = getDb();

$userCount = 0;
$stationCount = 0;
$collectionCount = 0;
$dbError = '';

try {
    $userStmt = $pdo->query('SELECT COUNT(*) AS count FROM user');
    $userCount = (int) ($userStmt->fetch()['count'] ?? 0);

    $stationStmt = $pdo->query('SELECT COUNT(*) AS count FROM station');
    $stationCount = (int) ($stationStmt->fetch()['count'] ?? 0);

    $collectionStmt = $pdo->query('SELECT COUNT(*) AS count FROM collection');
    $collectionCount = (int) ($collectionStmt->fetch()['count'] ?? 0);
} catch (Throwable $e) {
    $dbError = 'Could not load dashboard totals right now.';
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
    <title>Admin Dashboard</title>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<main class="page">
    <div class="container">
        <section class="card">
            <h2 class="card-title">Admin Overview</h2>

            <?php if ($dbError): ?>
                <p class="error-text"><?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php else: ?>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stationCount; ?></div>
                        <div class="stat-label">Stations</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value"><?php echo $userCount; ?></div>
                        <div class="stat-label">Users</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-value"><?php echo $collectionCount; ?></div>
                        <div class="stat-label">Collections</div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
    
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>