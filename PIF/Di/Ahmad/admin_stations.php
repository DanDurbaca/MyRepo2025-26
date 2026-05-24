<?php 
include 'includes/header.php'; 

// 1. Force lowercase and remove spaces from the session role
$current_role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

// 2. Simple check: if it's not 'admin', block them
if ($current_role !== 'admin') {
    echo "<div class='card'>
            <h2 style='color: #ef4444;'>🚫 Access Denied (DEBUG VERSION)</h2>
            <p>Your session says your role is: <strong>[" . $current_role . "]</strong></p>
            <p>To fix this, the database must say 'admin' and you must log out/in.</p>
          </div>";
    include 'includes/footer.php';
    exit();
}

$msg = "";

// --- 1. HANDLE NEW STATION CREATION ---
if (isset($_POST['create_station'])) {
    $sn = $conn->real_escape_string($_POST['new_sn']);
    $name = $conn->real_escape_string($_POST['new_name']);
    $owner = $conn->real_escape_string($_POST['new_owner']);

    // Check if Serial Number already exists to prevent duplicates
    $check = $conn->query("SELECT pk_serialNumber FROM station WHERE pk_serialNumber = '$sn'");
    if ($check->num_rows > 0) {
        $msg = "<p style='background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 10px; border-radius: 5px;'>❌ Error: Station with Serial Number $sn already exists.</p>";
    } else {
        $sql = "INSERT INTO station (pk_serialNumber, name, fk_user_owns) VALUES ('$sn', '$name', '$owner')";
        if ($conn->query($sql)) {
            $msg = "<p style='background: rgba(74, 222, 128, 0.2); color: #4ade80; padding: 10px; border-radius: 5px;'>✨ New Station $sn created successfully!</p>";
        } else {
            $msg = "<p style='color: #f87171;'>Error: " . $conn->error . "</p>";
        }
    }
}

// --- 2. HANDLE STATION DELETE ---
if (isset($_POST['delete_station'])) {
    $sn = $conn->real_escape_string($_POST['serial_number']);
    $conn->query("DELETE FROM station WHERE pk_serialNumber = '$sn'");
    $msg = "<p style='background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 10px; border-radius: 5px;'>🗑️ Station $sn deleted.</p>";
}

// --- 3. HANDLE STATION EDIT (Rename/Reassign) ---
if (isset($_POST['edit_station'])) {
    $sn = $conn->real_escape_string($_POST['serial_number']);
    $newName = $conn->real_escape_string($_POST['new_name']);
    $newOwner = $conn->real_escape_string($_POST['new_owner']);
    
    $sql = "UPDATE station SET name = '$newName', fk_user_owns = '$newOwner' WHERE pk_serialNumber = '$sn'";
    if ($conn->query($sql)) {
        $msg = "<p style='background: rgba(74, 222, 128, 0.2); color: #4ade80; padding: 10px; border-radius: 5px;'>✅ Station $sn updated.</p>";
    } else {
        $msg = "<p style='color: #f87171;'>Error: " . $conn->error . "</p>";
    }
}
?>

<div class="card" style="margin-bottom: 25px; border: 1px dashed #334155;">
    <h3 style="margin-top: 0;">➕ Register New Station</h3>
    <form method="POST" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 150px;">
            <label style="display:block; font-size: 0.8em; color: #94a3b8; margin-bottom: 5px;">Serial Number</label>
            <input type="text" name="new_sn" placeholder="SN-XXXX" required 
                   style="width:100%; background: #1e293b; border: 1px solid #334155; color: white; padding: 8px; border-radius: 4px;">
        </div>
        <div style="flex: 2; min-width: 200px;">
            <label style="display:block; font-size: 0.8em; color: #94a3b8; margin-bottom: 5px;">Station Name</label>
            <input type="text" name="new_name" placeholder="Station Location" required 
                   style="width:100%; background: #1e293b; border: 1px solid #334155; color: white; padding: 8px; border-radius: 4px;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display:block; font-size: 0.8em; color: #94a3b8; margin-bottom: 5px;">Assign Owner</label>
            <select name="new_owner" style="width:100%; background: #1e293b; border: 1px solid #334155; color: white; padding: 8px; border-radius: 4px;">
                <?php 
                $users = $conn->query("SELECT pk_username FROM user");
                while($u = $users->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($u['pk_username']) . "'>" . htmlspecialchars($u['pk_username']) . "</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" name="create_station" style="background: #10b981; color: white; border: none; padding: 9px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Create Station</button>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">🛠️ Global Station Management</h2>
        <span style="background: #4338ca; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8em;">Admin Mode</span>
    </div>

    <?= $msg ?>

    <div style="overflow-x: auto; margin-top: 20px;">
        <table style="width:100%; border-collapse: collapse; background: #0f172a; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="text-align:left; background: #1e293b; color:#94a3b8;">
                    <th style="padding:15px;">Serial Number</th>
                    <th style="padding:15px;">Station Name</th>
                    <th style="padding:15px;">Owner</th>
                    <th style="padding:15px; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $res = $conn->query("SELECT * FROM station");
                while($s = $res->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #1e293b;">
                        <form method="POST">
                            <td style="padding:15px; font-family:monospace; color: #38bdf8;"><?= $s['pk_serialNumber'] ?></td>
                            <td style="padding:15px;">
                                <input type="text" name="new_name" value="<?= htmlspecialchars($s['name']) ?>" 
                                       style="background: #1e293b; border: 1px solid #334155; color: white; padding: 8px; border-radius: 4px; width: 80%;">
                            </td>
                            <td style="padding:15px;">
                                <select name="new_owner" style="background: #1e293b; border: 1px solid #334155; color: white; padding: 8px; border-radius: 4px;">
                                    <?php 
                                    $users = $conn->query("SELECT pk_username FROM user");
                                    while($u = $users->fetch_assoc()) {
                                        $sel = ($u['pk_username'] == $s['fk_user_owns']) ? "selected" : "";
                                        echo "<option value='" . htmlspecialchars($u['pk_username']) . "' $sel>" . htmlspecialchars($u['pk_username']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td style="padding:15px; text-align:right;">
                                <input type="hidden" name="serial_number" value="<?= $s['pk_serialNumber'] ?>">
                                <button type="submit" name="edit_station" style="background:#3b82f6; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">Save</button>
                                <button type="submit" name="delete_station" onclick="return confirm('Delete this station?');" 
                                        style="background:#ef4444; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">Delete</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>