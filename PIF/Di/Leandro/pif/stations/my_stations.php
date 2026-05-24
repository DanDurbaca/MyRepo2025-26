<?php
/*
 * stations/my_stations.php
 * Purpose: List stations owned by the logged-in user and provide links to view measurements or register/create new stations.
 */
require "../includes/config.php";
require "../includes/auth_check.php";

// Fetch stations owned by the user
$stmt = $pdo->prepare("
    SELECT *
    FROM station
    WHERE fk_user_owns = ?
");
$stmt->execute([$_SESSION['username']]);
$stations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Stations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>My Stations</h2>

    <a href="register_station.php" class="btn btn-primary mb-3">
        Register new station
    </a>

    <a href="create_station.php" class="btn btn-primary mb-3">
    Create new station
</a>


    <?php if (count($stations) === 0): ?>
        <p>You don’t have any stations yet.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($stations as $s): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <strong><?= htmlspecialchars($s['pk_serialNumber']) ?></strong>
                        <?= $s['name'] ? ' – ' . htmlspecialchars($s['name']) : '' ?>
                    </span>
                    <a class="btn btn-sm btn-outline-primary"
                       href="station_measurements.php?sn=<?= urlencode($s['pk_serialNumber']) ?>">
                        View measurements
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
