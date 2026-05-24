<?php 
mysqli_report(MYSQLI_REPORT_OFF); 
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/header.php'; 

$current_role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';
if ($current_role !== 'admin') {
    echo "<div class='card'><h2>🚫 Access Denied</h2></div>";
    include 'includes/footer.php'; exit();
}

$msg = "";

// --- 1. HANDLE ADD TO COLLECTION ---
if (isset($_POST['add_to_collection'])) {
    $mid = $conn->real_escape_string($_POST['measurement_id']);
    $cid = $conn->real_escape_string($_POST['target_collection']);
    
    // Attempting to insert into the linking table
    $sql = "INSERT INTO collection_measurement (fk_collection_id, fk_measurement_id) VALUES ('$cid', '$mid')";
    if ($conn->query($sql)) {
        $msg = "<p style='color:#4ade80;'>✅ Measurement #$mid added to collection.</p>";
    } else {
        $msg = "<p style='color:#f87171;'>❌ Error adding to collection: " . $conn->error . "</p>";
    }
}

// --- 2. HANDLE DELETE ---
if (isset($_POST['delete_measurement'])) {
    $id = $conn->real_escape_string($_POST['measurement_id']);
    // Updated WHERE clause to use pk_measurement
    if ($conn->query("DELETE FROM measurement WHERE pk_measurement = '$id'")) {
        $msg = "<p style='color:#f87171;'>🗑️ Measurement #$id deleted successfully.</p>";
    }
}

// --- 3. COLUMN DETECTION & QUERY ---
// We check if the column is 'timestamp' instead of 'time'
$cols_check = $conn->query("SHOW COLUMNS FROM measurement LIKE 'timestamp'");
$time_col = ($cols_check->num_rows > 0) ? "timestamp" : "time";

$where = "1=1";
if (!empty($_GET['filter_start'])) {
    $s = $conn->real_escape_string(str_replace('T', ' ', $_GET['filter_start']));
    $where .= " AND $time_col >= '$s'";
}
if (!empty($_GET['filter_end'])) {
    $e = $conn->real_escape_string(str_replace('T', ' ', $_GET['filter_end']));
    $where .= " AND $time_col <= '$e'";
}

$res = $conn->query("SELECT * FROM measurement WHERE $where ORDER BY $time_col DESC LIMIT 100");

// Fetch collections ONCE here to prevent querying the database repeatedly inside the while loop
$collections = [];
$col_query = $conn->query("SELECT pk_id, name FROM collection");
if ($col_query) {
    while($c = $col_query->fetch_assoc()) {
        $collections[] = $c;
    }
}
?>

<div class="card">
    <h2>📊 Admin: Measurement Data</h2>
    <?= $msg ?>

    <form method="GET" style="display:flex; gap:10px; margin-bottom:20px; align-items:flex-end; background:#1e293b; padding:15px; border-radius:8px;">
        <div>
            <label style="font-size:0.8em; color:#94a3b8;">Start Date</label><br>
            <input type="datetime-local" name="filter_start" style="background:#0f172a; border:1px solid #334155; color:white; padding:5px;">
        </div>
        <div>
            <label style="font-size:0.8em; color:#94a3b8;">End Date</label><br>
            <input type="datetime-local" name="filter_end" style="background:#0f172a; border:1px solid #334155; color:white; padding:5px;">
        </div>
        <button type="submit" style="background:#3b82f6; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">Filter</button>
        <a href="admin_measurements.php" style="color:#94a3b8; text-decoration:none; font-size:0.9em;">Reset</a>
    </form>

    <div style="overflow-x: auto;">
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align:left; color:#94a3b8; border-bottom:2px solid #334155;">
                    <th style="padding:10px;">ID</th>
                    <th style="padding:10px;">Time</th>
                    <th style="padding:10px;">Value</th>
                    <th style="padding:10px;">Station</th>
                    <th style="padding:10px;">Collection</th>
                    <th style="padding:10px; text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr style="border-bottom:1px solid #1e293b;">
                    <td style="padding:10px;"><?= $row['pk_measurement'] ?></td>
                    <td style="padding:10px;"><?= $row[$time_col] ?></td>
                    <td style="padding:10px; color:#38bdf8; font-weight:bold;">
                        T: <?= $row['temperature'] ?>°C | H: <?= $row['humidity'] ?>%
                    </td>
                    <td style="padding:10px; font-family:monospace;"><?= $row['fk_station_records'] ?></td>
                    <td style="padding:10px;">
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="measurement_id" value="<?= $row['pk_measurement'] ?>">
                            <select name="target_collection" required style="background:#0f172a; color:white; border:1px solid #334155;">
                                <option value="">Select...</option>
                                <?php 
                                // Output the collections array we built earlier
                                foreach($collections as $c) {
                                    echo "<option value='{$c['pk_id']}'>{$c['name']}</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" name="add_to_collection" style="background:#10b981; color:white; border:none; padding:2px 8px; cursor:pointer;">+</button>
                        </form>
                    </td>
                    <td style="padding:10px; text-align:right;">
                        <form method="POST" onsubmit="return confirm('Delete measurement?');">
                            <input type="hidden" name="measurement_id" value="<?= $row['pk_measurement'] ?>">
                            <button type="submit" name="delete_measurement" style="background:#ef4444; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>