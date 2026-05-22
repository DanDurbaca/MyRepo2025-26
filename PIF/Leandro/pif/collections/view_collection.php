<?php
require "../includes/config.php";
require "../includes/auth_check.php";

if (!isset($_GET['id'])) {
    die("Collection not specified");
}

$cid = $_GET['id'];

// Check access
$stmt = $pdo->prepare("
    SELECT c.*
    FROM collection c
    LEFT JOIN hasaccess h ON h.pkfk_collection = c.pk_collection
    WHERE c.pk_collection = ?
      AND (c.fk_user_creates = ?
           OR h.pkfk_user = ?
           OR ? = 'Admin')
");
$stmt->execute([
    $cid,
    $_SESSION['username'],
    $_SESSION['username'],
    $_SESSION['role']
]);
$collection = $stmt->fetch();

if (!$collection) {
    die("Access denied");
}

// Fetch measurements
$stmt = $pdo->prepare("
    SELECT m.*
    FROM measurement m
    JOIN contains c ON c.pkfk_measurement = m.pk_measurement
    WHERE c.pkfk_collection = ?
    ORDER BY m.timestamp DESC
");
$stmt->execute([$cid]);
$measurements = $stmt->fetchAll();

// Fetch friends (only if creator)
$friends = [];
if ($collection['fk_user_creates'] === $_SESSION['username']) {
    $stmt = $pdo->prepare("
        SELECT pkfk_user_friend
        FROM isfriend
        WHERE pkfk_user_user = ?
    ");
    $stmt->execute([$_SESSION['username']]);
    $friends = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($collection['name']) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2><?= htmlspecialchars($collection['name']) ?></h2>

    <!-- Share form (only for owner) -->
    <?php if ($collection['fk_user_creates'] === $_SESSION['username'] && count($friends) > 0): ?>
        <form method="post" action="share_collection.php" class="mb-4">
            <input type="hidden" name="collection_id" value="<?= $collection['pk_collection'] ?>">
            <label>Share with friend:</label>
            <select name="friend_username" class="form-select" required>
                <option value="">-- Select Friend --</option>
                <?php foreach ($friends as $friend): ?>
                    <option value="<?= htmlspecialchars($friend) ?>">
                        <?= htmlspecialchars($friend) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary">Share</button>
        </form>
    <?php endif; ?>

    <?php if (count($measurements) === 0): ?>
        <p>No measurements in this collection.</p>
    <?php else: ?>
        <table class="table table-dark table-striped">
            <tr>
                <th>Time</th>
                <th>Temp</th>
                <th>Humidity</th>
                <th>Pressure</th>
                <th>Light</th>
                <th>Gas</th>
            </tr>
            <?php foreach ($measurements as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['timestamp']) ?></td>
                    <td><?= $m['temperature'] ?></td>
                    <td><?= $m['humidity'] ?></td>
                    <td><?= $m['pressure'] ?></td>
                    <td><?= $m['light'] ?></td>
                    <td><?= $m['gas'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <a href="collections.php" class="btn btn-secondary mt-3">
        Back to collections
    </a>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
