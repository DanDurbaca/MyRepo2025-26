<?php
/*
 * stations/create_station.php
 * Purpose: Allow a user to create/register a new station record they own.
 * Sections:
 *  - Includes: config and auth check
 *  - POST handling: validate input and insert new `station` row
 *  - On success: redirect to `my_stations.php`
 */
require "../includes/config.php";
require "../includes/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $serial = trim($_POST['serial']);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$serial) {
        header("Location: create_station.php");
        exit;
    }

    // Create station owned by user
    $stmt = $pdo->prepare("
        INSERT INTO station (pk_serialNumber, name, description, fk_user_owns)
        VALUES (?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $serial,
            $name,
            $description,
            $_SESSION['username']
        ]);
    } catch (PDOException $e) {
        // Duplicate serial
        header("Location: create_station.php");
        exit;
    }

    header("Location: my_stations.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Station</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>Create Station</h2>

    <form method="post" class="card p-4">
        <div class="mb-3">
            <label class="form-label">Serial Number *</label>
            <input name="serial" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Station Name</label>
            <input name="name" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <button class="btn btn-primary">Create station</button>
        <a href="my_stations.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
