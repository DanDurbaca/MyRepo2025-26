<?php
/*
 * admin/stations.php
 * Purpose: Admin UI to create stations and assign owners.
 * Sections:
 *  - Handles POST actions to create stations and assign ownership
 *  - Lists all stations and users for admin management
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";

/* Create station */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_serial'])) {
    $serial = trim($_POST['new_serial']);

    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO station (pk_serialNumber) VALUES (?)"
    );
    $stmt->execute([$serial]);
}

/* Assign owner */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['assign_serial'])) {
    $stmt = $pdo->prepare(
        "UPDATE station SET fk_user_owns = ? WHERE pk_serialNumber = ?"
    );
    $stmt->execute([
        $_POST['owner'],
        $_POST['assign_serial']
    ]);
}

/* Fetch stations */
$stations = $pdo->query("
    SELECT pk_serialNumber, fk_user_owns
    FROM station
    ORDER BY pk_serialNumber
")->fetchAll();

/* Fetch users */
$users = $pdo->query("
    SELECT pk_username FROM user ORDER BY pk_username
")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin – Stations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Stations</h2>

    <!-- CREATE STATION -->
    <form method="post" class="mb-4">
        <input name="new_serial"
               class="form-control mb-2"
               placeholder="New station serial number"
               required>
        <button class="btn btn-primary">Create station</button>
    </form>

    <!-- STATIONS TABLE -->
    <table class="table table-dark table-striped align-middle">
        <thead>
            <tr>
                <th>Serial</th>
                <th>Owner</th>
                <th style="width: 260px;">Assign owner</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($stations as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['pk_serialNumber']) ?></td>
                <td><?= $s['fk_user_owns'] ?? '<span class="">Unassigned</span>' ?></td>
                <td>
                    <form method="post" class="d-flex gap-2">
                        <input type="hidden" name="assign_serial"
                               value="<?= htmlspecialchars($s['pk_serialNumber']) ?>">

                        <select name="owner" class="form-select form-select-sm">
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u ?>" <?= $u === $s['fk_user_owns'] ? 'selected' : '' ?>>
                                    <?= $u ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button class="btn btn-sm btn-outline-primary">
                            Assign
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
