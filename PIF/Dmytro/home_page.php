<?php
session_start();
include "queries.php";
if(!isset($_SESSION["username"])){
    $Link = "<a href='login.php?login'>Please Log-in first</a>";
}
else{
    $usrname = $_SESSION["username"];
}

// Fetch the absolute latest measurement for this user's stations
$dashQuery = $conn->prepare("
    SELECT m.* FROM measurement m
    JOIN station s ON m.fk_station_records = s.pk_serialNumber
    WHERE s.fk_user_owns = ?
    ORDER BY m.timestamp DESC 
    LIMIT 1
");
$dashQuery->bind_param("s", $usrname);
$dashQuery->execute();
$latest = $dashQuery->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="mystyle.css">
    <?php
        include "navbar.php";
    ?>
</head>
<body>
<div class="container">
    <h1>Welcome back, <?php echo htmlspecialchars($usrname); ?>!</h1>
    <p>Here is the latest data from your sensors:</p> <!-- Dashboard with latest measurements -->
    <?php if($latest): ?>
    <div class="dashboard-grid">
        <div class="card">
            <h3>Temperature</h3>
            <div class="value"><?php echo $latest['temperature']; ?><span class="unit">°C</span></div>
        </div>
        <div class="card">
            <h3>Humidity</h3>
            <div class="value"><?php echo $latest['humidity']; ?><span class="unit">%</span></div>
        </div>
        <div class="card">
            <h3>Air Quality (Gas)</h3>
            <div class="value"><?php echo $latest['gas']; ?><span class="unit">ppm</span></div>
        </div>
        <div class="card">
            <h3>Light</h3>
            <div class="value"><?php echo $latest['light']; ?><span class="unit">lux</span></div>
        </div>
    </div>
    <p style="color: #64748b; font-size: 0.8rem; margin-top: 15px;">
        Last updated: <?php echo $latest['timestamp']; ?> (Station: <?php echo $latest['fk_station_records']; ?>)
    </p>
    <?php else: ?>
        <p>No data found. Please link a station to get started.</p>
    <?php endif; ?>

    <hr style="margin: 40px 0; border: 0; border-top: 1px solid var(--border);">
    
    <h3>Quick Actions</h3>
    <a href="stations.php" class="btn">Manage My Stations</a>
    <a href="collection.php" class="btn" style="background: #64748b;">View Collections</a>
</div>
</body>
<footer>
    <?php include "footer.php"; ?>
</footer>
</html>