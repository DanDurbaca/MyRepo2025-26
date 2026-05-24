<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) { 
    header("Location: /login.php"); 
    exit(); 
}

$user = $_SESSION['username'];
$msg = "";

// --- 1. HANDLE RENAME ---
if (isset($_POST['rename_coll'])) {
    $id = (int)$_POST['coll_id'];
    $newName = $conn->real_escape_string($_POST['new_name']);
    
    // Security: Only update if the user owns this collection
    $sql = "UPDATE collection SET name = '$newName' WHERE pk_collection = $id AND fk_user_creates = '$user'";
    if ($conn->query($sql)) {
        $msg = "<p style='color: #4ade80;'>✅ Collection renamed to '$newName'!</p>";
    } else {
        $msg = "<p style='color: #f87171;'>⚠️ Error: " . $conn->error . "</p>";
    }
}

// --- 2. HANDLE COLLECTION CREATION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_collection'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $station = $conn->real_escape_string($_POST['station']);
    $start = $_POST['start_dt'];
    $end = $_POST['end_dt'];

    $sql = "INSERT INTO collection (name, start_timestamp, end_timestamp, fk_user_creates, fk_station_source) 
            VALUES ('$name', '$start', '$end', '$user', '$station')";
    
    if ($conn->query($sql)) {
        $msg = "<p style='color: #4ade80;'>✅ Collection '$name' created!</p>";
    } else {
        $msg = "<p style='color: #f87171;'>⚠️ Error: " . $conn->error . "</p>";
    }
}

// --- 3. HANDLE DELETE ---
if (isset($_POST['delete_coll'])) {
    $id = (int)$_POST['coll_id'];
    $conn->query("DELETE FROM collection WHERE pk_collection = $id AND fk_user_creates = '$user'");
    $msg = "<p style='color: #f87171;'>🗑️ Collection removed.</p>";
}
?>

<div class="card">
    <h2>Create New Collection</h2>
    <p style="color:#94a3b8; font-size:0.9em; margin-bottom:20px;">
        Define a time range for a specific station to group data into a collection.
    </p>

    <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #0f172a; padding: 20px; border-radius: 8px; border: 1px solid #1e293b;">
        <div style="grid-column: span 2;">
            <label>Collection Name</label>
            <input type="text" name="name" required placeholder="e.g. Living Room Analysis - Week 5">
        </div>

        <div style="grid-column: span 2;">
            <label>Station Source</label>
            <select name="station" required style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid #334155; border-radius:4px;">
                <?php 
                $my_stations = $conn->query("SELECT pk_serialNumber, name FROM station WHERE fk_user_owns = '$user'");
                if ($my_stations->num_rows > 0) {
                    while($s = $my_stations->fetch_assoc()) {
                        echo "<option value='{$s['pk_serialNumber']}'>{$s['name']} ({$s['pk_serialNumber']})</option>";
                    }
                } else {
                    echo "<option value='' disabled>No stations found - Register one first!</option>";
                }
                ?>
            </select>
        </div>

        <div>
            <label>Start Date & Time</label>
            <input type="datetime-local" name="start_dt" required>
        </div>
        <div>
            <label>End Date & Time</label>
            <input type="datetime-local" name="end_dt" required>
        </div>

        <button type="submit" name="create_collection" style="grid-column: span 2; margin-top:10px;">Create Collection</button>
    </form>

    <hr style="margin: 40px 0; border: 0; border-top: 1px solid #334155;">

    <h2>My Collections</h2>
    <?= $msg ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <?php 
        $colls = $conn->query("SELECT * FROM collection WHERE fk_user_creates = '$user' ORDER BY pk_collection DESC");
        
        if ($colls->num_rows > 0):
            while($c = $colls->fetch_assoc()): ?>
                <div style="background: #1e293b; padding: 20px; border-radius: 8px; border: 1px solid #334155;">
                    
                    <form method="POST" style="display: flex; gap: 8px; margin-bottom: 15px;">
                        <input type="hidden" name="coll_id" value="<?= $c['pk_collection'] ?>">
                        <input type="text" name="new_name" value="<?= htmlspecialchars($c['name']) ?>" 
                               style="margin: 0; padding: 4px 8px; font-size: 1.1em; font-weight: bold; color: #38bdf8; background: #0f172a; border: 1px solid #334155; border-radius: 4px; flex-grow: 1;">
                        <button type="submit" name="rename_coll" style="padding: 4px 10px; font-size: 0.75em; background: #475569;">Rename</button>
                    </form>
                    
                    <div style="font-size: 0.85em; color: #cbd5e1; margin-bottom: 20px;">
                        <strong>Station:</strong> <?= htmlspecialchars($c['fk_station_source']) ?><br>
                        <strong>Start:</strong> <?= $c['start_timestamp'] ?><br>
                        <strong>End:</strong> <?= $c['end_timestamp'] ?>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <a href="/view_collection.php?id=<?= $c['pk_collection'] ?>" class="button-small" style="background: #3b82f6; color:white; padding: 5px 10px; border-radius: 4px; text-decoration:none; font-size:0.85em;">View Data</a>
                        
                        <a href="/share.php?id=<?= $c['pk_collection'] ?>" class="button-small" style="background: #10b981; color:white; padding: 5px 10px; border-radius: 4px; text-decoration:none; font-size:0.85em;">Share</a>
                        
                        <form method="POST" onsubmit="return confirm('Delete this collection?');" style="margin-left: auto;">
                            <input type="hidden" name="coll_id" value="<?= $c['pk_collection'] ?>">
                            <button type="submit" name="delete_coll" style="background: transparent; color: #ef4444; border: 1px solid #ef4444; padding: 4px 8px; font-size: 0.8em;">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile;
        else:
            echo "<p style='color:#64748b;'>No collections found.</p>";
        endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>