<?php
/*
 * collections/create_collection.php
 * Purpose: Build a collection of measurements from a specific station over a time range.
 * Sections:
 *  - Includes: config and auth check
 *  - Fetch stations owned by user
 *  - POST handling: create collection, fetch measurements in range and add them to `contains`
 */
require "../includes/config.php";
require "../includes/auth_check.php";

// Fetch user's stations
$stmt = $pdo->prepare("
    SELECT pk_serialNumber, name
    FROM station
    WHERE fk_user_owns = ?
");
$stmt->execute([$_SESSION['username']]);
$stations = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sn = $_POST['sn'] ?? null;
    $start = $_POST['start'] ?: null;
    $end = $_POST['end'] ?: null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$sn || !$name) {
        die("Missing required data");
    }

    // 1️⃣ Create collection
    $stmt = $pdo->prepare("
        INSERT INTO collection (name, description, fk_user_creates)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $name,
        $description,
        $_SESSION['username']
    ]);

    $collectionId = $pdo->lastInsertId();

    // 2️⃣ Get measurements in range
    $sql = "
        SELECT pk_measurement
        FROM measurement
        WHERE fk_station_records = ?
    ";
    $params = [$sn];

    if ($start) {
        $sql .= " AND timestamp >= ?";
        $params[] = $start;
    }
    if ($end) {
        $sql .= " AND timestamp <= ?";
        $params[] = $end;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $measurements = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3️⃣ Insert into contains
    if ($measurements) {
        $stmt = $pdo->prepare("
            INSERT INTO contains (pkfk_collection, pkfk_measurement)
            VALUES (?, ?)
        ");

        foreach ($measurements as $mid) {
            $stmt->execute([$collectionId, $mid]);
        }
    }

    // Done
    header("Location: collections.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Collection</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>Create Collection</h2>

    <form method="post" class="card p-4">

        <div class="mb-3">
            <label class="form-label">Collection name *</label>
            <input name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Station *</label>
            <select name="sn" class="form-control" required>
                <option value="">Select station</option>
                <?php foreach ($stations as $s): ?>
                    <option value="<?= htmlspecialchars($s['pk_serialNumber']) ?>">
                        <?= htmlspecialchars($s['pk_serialNumber']) ?>
                        <?= $s['name'] ? ' – ' . htmlspecialchars($s['name']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Start date/time</label>
            <input type="datetime-local" name="start" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">End date/time</label>
            <input type="datetime-local" name="end" class="form-control">
        </div>

        <button class="btn btn-primary">Create collection</button>
        <a href="collections.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
