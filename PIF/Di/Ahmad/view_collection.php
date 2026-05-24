<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) { header("Location: /login.php"); exit(); }
$user = $_SESSION['username'];
$coll_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Fetch Collection Details and Verify Access
// A user can view if they OWN it OR if it was SHARED with them
$sql_check = "SELECT c.* FROM collection c 
              LEFT JOIN hasaccess h ON c.pk_collection = h.fk_collection
              WHERE c.pk_collection = $coll_id 
              AND (c.fk_user_creates = '$user' OR h.fk_user_recipient = '$user')";

$res_check = $conn->query($sql_check);

if ($res_check->num_rows == 0) {
    echo "<div class='card'><h2>Access Denied</h2><p>You do not have permission to view this collection.</p></div>";
    include 'includes/footer.php';
    exit();
}

$coll = $res_check->fetch_assoc();

// 2. Fetch Measurements based on Collection parameters
$station = $coll['fk_station_source'];
$start = $coll['start_timestamp'];
$end = $coll['end_timestamp'];

$sql_data = "SELECT * FROM measurement 
             WHERE fk_station_records = '$station' 
             AND timestamp >= '$start' 
             AND timestamp <= '$end' 
             ORDER BY timestamp DESC";

$measurements = $conn->query($sql_data);
?>



<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <a href="/collections.php" style="color:#94a3b8; text-decoration:none; font-size:0.9em;">&larr; Back to Collections</a>
            <h2 style="margin:5px 0;"><?= htmlspecialchars($coll['name']) ?></h2>
            <p style="color:#64748b; font-size:0.85em;">
                Station: <strong><?= $station ?></strong> | 
                Range: <strong><?= $start ?></strong> to <strong><?= $end ?></strong>
            </p>
        </div>
        <button onclick="window.print()" style="background:#334155; font-size:0.8em;">Print Report</button>
    </div>

    <div style="overflow-x: auto;">
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align:left; border-bottom: 2px solid #334155; color:#94a3b8; font-size:0.9em;">
                    <th style="padding:12px;">Timestamp</th>
                    <th style="padding:12px;">Temp</th>
                    <th style="padding:12px;">Humid</th>
                    <th style="padding:12px;">Press</th>
                    <th style="padding:12px;">Light</th>
                    <th style="padding:12px;">Gas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($measurements->num_rows > 0): ?>
                    <?php while($row = $measurements->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #1e293b;">
                            <td style="padding:12px; font-size:0.85em;"><?= $row['timestamp'] ?></td>
                            <td style="padding:12px;"><?= number_format($row['temperature'], 1) ?>°C</td>
                            <td style="padding:12px;"><?= number_format($row['humidity'], 1) ?>%</td>
                            <td style="padding:12px;"><?= number_format($row['pressure'], 0) ?> hPa</td>
                            <td style="padding:12px;"><?= number_format($row['light'], 0) ?> lx</td>
                            <td style="padding:12px;"><?= number_format($row['gas'], 1) ?> kΩ</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding:30px; text-align:center; color:#64748b;">
                            No data found for this station in this time range.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
