<?php
/*
 * dashboard.php
 * Purpose: User dashboard showing summary cards for stations, latest measurement and quick actions.
 * Sections:
 *  - Includes: configuration and authentication check
 *  - DB queries: count stations and fetch latest measurement for the logged-in user
 *  - Renders: HTML dashboard with summary cards and links to related pages
 */
require "includes/config.php";
require "includes/auth_check.php";

// Number of stations
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM station 
    WHERE fk_user_owns = ?
");
$stmt->execute([$_SESSION['username']]);
$stationCount = $stmt->fetchColumn();

// Latest measurement
$stmt = $pdo->prepare("
    SELECT m.timestamp, s.pk_serialNumber
    FROM measurement m
    JOIN station s ON s.pk_serialNumber = m.fk_station_records
    WHERE s.fk_user_owns = ?
    ORDER BY m.timestamp DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['username']]);
$latest = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "includes/navbar.php"; ?>

<div class="container mt-5">

    <h1 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>

    <!-- SUMMARY CARDS -->
    <div class="row g-4 mb-5">

        <div class="col-md-4">
            <div class="card dashboard-card p-4">
                <h5>Your Stations</h5>
                <h2><?= $stationCount ?></h2>
                <p >Registered measurement stations</p>
                <a href="stations/my_stations.php" class="btn btn-primary">
                    Manage stations
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card p-4">
                <h5>Latest Measurement</h5>

                <?php if ($latest): ?>
                    <p class="mb-1">
                        <strong>Station:</strong>
                        <?= htmlspecialchars($latest['pk_serialNumber']) ?>
                    </p>
                    <p >
                        <?= htmlspecialchars($latest['timestamp']) ?>
                    </p>
                <?php else: ?>
                    <p class="">No measurements yet</p>
                <?php endif; ?>

                <a href="stations/my_stations.php" class="btn btn-outline-primary">
                    View data
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card dashboard-card p-4">
                <h5>Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="collections/collections.php" class="btn btn-outline-primary">
                        Collections
                    </a>
                    <a href="friends/friends.php" class="btn btn-outline-primary">
                        Friends
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>
