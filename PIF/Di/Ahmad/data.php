<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit();
}

$user = $_SESSION['username'];

// 1. Get filter values from the URL (GET request)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date   = isset($_GET['end_date'])   ? $_GET['end_date']   : '';

// 2. Build the SQL Query
// We JOIN with the station table to verify ownership
$sql = "SELECT m.*, s.name as station_name 
        FROM measurement m 
        JOIN station s ON m.fk_station_records = s.pk_serialNumber 
        WHERE s.fk_user_owns = '$user'";

// Add date filters if they are provided
if (!empty($start_date)) {
    $sql .= " AND m.timestamp >= '$start_date 00:00:00'";
}
if (!empty($end_date)) {
    $sql .= " AND m.timestamp <= '$end_date 23:59:59'";
}

$sql .= " ORDER BY m.timestamp DESC";
$result = $conn->query($sql);
?>



<div class="card">
    <h2>Measurement Data</h2>
    
    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap;">
        <div>
            <label style="display:block; font-size:0.8em; color:#94a3b8;">Start Date</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" style="margin:0; width:auto;">
        </div>
        <div>
            <label style="display:block; font-size:0.8em; color:#94a3b8;">End Date</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" style="margin:0; width:auto;">
        </div>
        <button type="submit">Filter Results</button>
        <?php if(!empty($start_date) || !empty($end_date)): ?>
            <a href="/data.php" style="color:#94a3b8; text-decoration:none; font-size:0.9em; padding-bottom:10px;">Clear Filters</a>
        <?php endif; ?>
    </form>

    <div style="overflow-x: auto;">
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align:left; border-bottom: 2px solid #334155;">
                    <th style="padding:10px;">Station</th>
                    <th style="padding:10px;">Temp</th>
                    <th style="padding:10px;">Humidity</th>
                    <th style="padding:10px;">Pressure</th>
                    <th style="padding:10px;">Light</th>
                    <th style="padding:10px;">Gas</th>
                    <th style="padding:10px;">Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #1e293b;">
                            <td style="padding:10px;"><?= htmlspecialchars($row['station_name']) ?> <br> <small style="color:#64748b;"><?= $row['fk_station_records'] ?></small></td>
                            <td style="padding:10px;"><?= $row['temperature'] ?>°C</td>
                            <td style="padding:10px;"><?= $row['humidity'] ?>%</td>
                            <td style="padding:10px;"><?= $row['pressure'] ?> hPa</td>
                            <td style="padding:10px;"><?= $row['light'] ?> lx</td>
                            <td style="padding:10px;"><?= $row['gas'] ?> kΩ</td>
                            <td style="padding:10px; font-size:0.9em;"><?= $row['timestamp'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="padding:20px; text-align:center; color:#94a3b8;">No measurements found for your stations.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
