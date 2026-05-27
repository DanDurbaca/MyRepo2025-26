<?php
$pageTitle = 'Manage Stations';
require_once __DIR__ . '/_header.php';

$message = '';
$messageType = 'info';

// Handle station creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_station'])) {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
        $messageType = 'danger';
    } else {
        $serial = trim($_POST['serial']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $serial)) {
            $message = 'Invalid serial format.';
            $messageType = 'danger';
        } elseif ($name === '' || strlen($name) > 191) {
            $message = 'Name required (max 191 characters).';
            $messageType = 'danger';
        } else {
            $stmt = $pdo->prepare("INSERT INTO station (pk_serialNumber, name, description, fk_user_owns) VALUES (?, ?, ?, NULL)");
            try {
                $stmt->execute([$serial, $name, $description]);
                $message = 'Station created successfully!';
                $messageType = 'success';
            } catch (PDOException $e) {
                error_log('Admin create station error: ' . $e->getMessage());
                $message = 'Failed to create station.';
                $messageType = 'danger';
            }
        }
    }
}

// Handle provision token generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_token'])) {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
        $messageType = 'danger';
    } else {
        $serial = trim($_POST['provision_serial']);
        if ($serial === '') {
            $message = 'Station serial required.';
            $messageType = 'danger';
        } else {
            $token = bin2hex(random_bytes(16));
            $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));

            try {
                $stmt = $pdo->prepare("INSERT INTO provision_token (token, fk_station_serial, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
                $stmt->execute([$token, $serial, $expiry]);
                $message = "Provision token generated: <code>$token</code> (expires: $expiry)";
                $messageType = 'success';
            } catch (PDOException $e) {
                error_log('Admin generate token error: ' . $e->getMessage());
                $message = 'Failed to generate token.';
                $messageType = 'danger';
            }
        }
    }
}

// Handle station deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
        $messageType = 'danger';
    } else {
        $serial = trim($_POST['station_serial'] ?? '');
        if ($serial) {
            $pdo->prepare("DELETE FROM station WHERE pk_serialNumber = ?")->execute([$serial]);
            $message = "Station '$serial' deleted.";
            $messageType = 'success';
        }
    }
}
?>

<div class="container">
    <h1>Manage Stations</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>Create New Station</h3>
        <form method="post">
            <div class="form-group">
                <label for="serial">Serial Number</label>
                <input type="text" id="serial" name="serial" required placeholder="e.g., STATION001">
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required placeholder="e.g., Living Room Sensor">
            </div>
            <div class="form-group">
                <label for="description">Description (Optional)</label>
                <textarea id="description" name="description" rows="2" placeholder="Optional description..."></textarea>
            </div>
            <?php echo csrf_input(); ?>
            <button class="btn" type="submit" name="create_station">Create Station</button>
        </form>
    </div>

    <div class="card">
        <h3>Generate Provision Token</h3>
        <p>Generate a temporary provisioning token for a station (7 day expiry).</p>
        <form method="post">
            <div class="form-group">
                <label for="provision_serial">Station Serial</label>
                <input type="text" id="provision_serial" name="provision_serial" required placeholder="Enter station serial number">
            </div>
            <?php echo csrf_input(); ?>
            <button class="btn" type="submit" name="generate_token">Generate Token</button>
        </form>
    </div>

    <div class="card">
        <h3>All Stations</h3>
        <table>
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>Name</th>
                    <th>Owner</th>
                    <th>Description</th>
                    <th>Measurements</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("
                    SELECT s.pk_serialNumber, s.name, s.description, s.fk_user_owns,
                           COUNT(m.fk_station_records) as measurement_count
                    FROM station s
                    LEFT JOIN measurement m ON s.pk_serialNumber = m.fk_station_records
                    GROUP BY s.pk_serialNumber, s.name, s.description, s.fk_user_owns
                    ORDER BY s.pk_serialNumber
                ");
                $stmt->execute();

                while ($station = $stmt->fetch()) {
                    $serial = htmlspecialchars($station['pk_serialNumber']);
                    $name = htmlspecialchars($station['name']);
                    $description = htmlspecialchars($station['description'] ?? '');
                    $owner = htmlspecialchars($station['fk_user_owns'] ?? 'Unassigned');
                    $measurements = number_format($station['measurement_count']);

                    echo "<tr>";
                    echo "<td><code>$serial</code></td>";
                    echo "<td><strong>$name</strong></td>";
                    echo "<td>$owner</td>";
                    echo "<td>$description</td>";
                    echo "<td>$measurements</td>";
                    echo "<td>";
                    echo "<form method='post' style='display:inline;' onsubmit='return confirm(\"Delete station $serial and all its data?\")'>";
                    echo csrf_input();
                    echo "<input type='hidden' name='action' value='delete'>";
                    echo "<input type='hidden' name='station_serial' value='$serial'>";
                    echo "<button type='submit' class='btn btn-danger btn-small'>Delete</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>