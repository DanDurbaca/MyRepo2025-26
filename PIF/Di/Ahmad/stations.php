<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit();
}

$user = $_SESSION['username'];
$msg = "";

// --- HANDLE REGISTRATION (Claiming a station) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_station'])) {
    $serial = $conn->real_escape_string($_POST['serial_to_claim']);
    
    // Check if station exists AND is not already owned
    $check = $conn->query("SELECT fk_user_owns FROM station WHERE pk_serialNumber = '$serial'");
    
    if ($check->num_rows > 0) {
        $station = $check->fetch_assoc();
        if ($station['fk_user_owns'] === NULL) {
            // Station is free! Claim it.
            $conn->query("UPDATE station SET fk_user_owns = '$user', name = 'New Station' WHERE pk_serialNumber = '$serial'");
            $msg = "<p style='color: #4ade80;'>Station $serial successfully registered to your account!</p>";
        } else {
            $msg = "<p style='color: #f87171;'>Error: This station is already registered to another user.</p>";
        }
    } else {
        $msg = "<p style='color: #f87171;'>Error: Serial number not found in system.</p>";
    }
}

// --- HANDLE UPDATE (Editing name/desc) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_station'])) {
    $serial = $conn->real_escape_string($_POST['serial']);
    $newName = $conn->real_escape_string($_POST['name']);
    $newDesc = $conn->real_escape_string($_POST['description']);

    $conn->query("UPDATE station SET name = '$newName', description = '$newDesc' 
                  WHERE pk_serialNumber = '$serial' AND fk_user_owns = '$user'");
    $msg = "<p style='color: #4ade80;'>Station updated!</p>";
}
?>

<div class="card">
    <h2>Register a New Station</h2>
    <p style="font-size: 0.9em; color: #94a3b8;">Enter the serial number of your hardware device to link it to your account.</p>
    <form method="POST" style="display: flex; gap: 10px; margin-bottom: 30px;">
        <input type="text" name="serial_to_claim" placeholder="Serial Number (e.g. SN-001)" required>
        <button type="submit" name="register_station">Register Station</button>
    </form>

    <hr style="border: 0; border-top: 1px solid #334155; margin: 40px 0;">

    <h2>My Stations</h2>
    <?= $msg ?>

    <?php
    $res = $conn->query("SELECT * FROM station WHERE fk_user_owns = '$user'");
    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()): ?>
            <div style="background: #0f172a; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #334155;">
                <form method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                    <input type="hidden" name="serial" value="<?= $row['pk_serialNumber'] ?>">
                    <div><strong>Serial:</strong> <?= htmlspecialchars($row['pk_serialNumber']) ?></div>
                    <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" placeholder="Station Name" required>
                    <input type="text" name="description" value="<?= htmlspecialchars($row['description']) ?>" placeholder="Description">
                    <button type="submit" name="update_station" style="width: fit-content;">Save Changes</button>
                </form>
            </div>
        <?php endwhile;
    } else {
        echo "<p>You don't own any stations yet.</p>";
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>