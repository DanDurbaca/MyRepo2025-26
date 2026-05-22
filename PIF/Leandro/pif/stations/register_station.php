<?php
/*
 * stations/register_station.php
 * Purpose: Claim ownership of an existing station by assigning it to the current user when unowned.
 * Sections:
 *  - Includes: config and auth check
 *  - POST handling: attempt to set `fk_user_owns` for a station if currently NULL
 */
require "../includes/config.php";
require "../includes/auth_check.php";

$message = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $serial = trim($_POST['serial']);

    // Try to claim the station ONLY if it exists and is unowned
    $stmt = $pdo->prepare("
        UPDATE station
        SET fk_user_owns = ?
        WHERE pk_serialNumber = ?
          AND fk_user_owns IS NULL
    ");
    $stmt->execute([$_SESSION['username'], $serial]);

    if ($stmt->rowCount() === 1) {
        // Success
        header("Location: my_stations.php");
        exit;
    } else {
        // Station does not exist OR already owned
        $message = "Station not found or already registered.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Station</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <div class="card p-4">
        <h4 class="mb-3">Register Station</h4>

        <?php if ($message): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input name="serial"
                   class="form-control mb-3"
                   placeholder="Serial number"
                   required>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Register</button>
                <a href="my_stations.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
