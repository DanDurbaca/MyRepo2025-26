<?php
session_start();
require __DIR__ . '/../assets/db.php';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$dbError = '';
$stations = [];
$users = [];
$successMsg = '';

$sortOptions = [
    'user_asc' => '(s.fk_user_owns IS NULL), s.fk_user_owns ASC, s.pk_serialNumber ASC',
    'user_desc' => '(s.fk_user_owns IS NULL), s.fk_user_owns DESC, s.pk_serialNumber ASC',
    'serial_asc' => 's.pk_serialNumber ASC',
    'serial_desc' => 's.pk_serialNumber DESC',
];

$sort = $_GET['sort'] ?? 'user_asc';
if (!isset($sortOptions[$sort])) {
    $sort = 'user_asc';
}

try {
    $pdo = getDb();

    $roleStmt = $pdo->prepare('SELECT role FROM user WHERE pk_username = :username');
    $roleStmt->execute([':username' => $_SESSION['username']]);
    $me = $roleStmt->fetch(PDO::FETCH_ASSOC);

    if (($me['role'] ?? '') !== 'Admin') {
        header('Location: index.php');
        exit;
    }

    if (isset($_SESSION['admin_station_msg'])) {
        $successMsg = (string) $_SESSION['admin_station_msg'];
        unset($_SESSION['admin_station_msg']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create') {
            $stationId = trim($_POST['station_serial'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $owner = trim($_POST['owner'] ?? '');

            if (!$stationId) {
                $dbError = 'Serial number is required.';
            } else {
                $insertStmt = $pdo->prepare(
                    'INSERT INTO station (pk_serialNumber, name, description, fk_user_owns) VALUES (:id, :name, :description, :owner)'
                );
                $insertStmt->execute([
                    ':id' => $stationId,
                    ':name' => $name !== '' ? $name : null,
                    ':description' => $description !== '' ? $description : null,
                    ':owner' => $owner !== '' ? $owner : null,
                ]);

                $_SESSION['admin_station_msg'] = 'Station created successfully.';
                header('Location: stations.php?sort=' . urlencode($sort));
                exit;
            }
        }
    }

    $usersStmt = $pdo->query('SELECT pk_username, firstName, lastName FROM user ORDER BY pk_username ASC');
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

    $stationsStmt = $pdo->query(
        'SELECT s.pk_serialNumber, s.name, s.description, s.fk_user_owns, u.firstName, u.lastName
         FROM station s
         LEFT JOIN user u ON u.pk_username = s.fk_user_owns
         ORDER BY ' . $sortOptions[$sort]
    );
    $stations = $stationsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dbError = 'Database error. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
    <title>Admin Stations</title>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<main class="page">
    <div class="stations-container">
        <section class="card">
            <h2 class="card-title">Station Management</h2>

            <?php if ($successMsg): ?>
                <p class="success-text"><?php echo h($successMsg); ?></p>
            <?php endif; ?>

            <?php if ($dbError): ?>
                <p class="error-text"><?php echo h($dbError); ?></p>
            <?php endif; ?>

            <form method="get" class="register-form" style="margin-bottom: 12px;">
                <div class="form-group">
                    <label class="field-label" for="sort">Sort</label>
                    <select id="sort" name="sort" class="input-select" onchange="this.form.submit()">
                        <option value="user_asc" <?php echo $sort === 'user_asc' ? 'selected' : ''; ?>>User (A-Z)</option>
                        <option value="user_desc" <?php echo $sort === 'user_desc' ? 'selected' : ''; ?>>User (Z-A)</option>
                        <option value="serial_asc" <?php echo $sort === 'serial_asc' ? 'selected' : ''; ?>>Serial (A-Z)</option>
                        <option value="serial_desc" <?php echo $sort === 'serial_desc' ? 'selected' : ''; ?>>Serial (Z-A)</option>
                    </select>
                </div>
            </form>

            <?php if (empty($stations)): ?>
                <p class="muted">No stations found.</p>
            <?php else: ?>
                <ul class="stations-list">
                    <?php foreach ($stations as $station): ?>
                        <li class="station-card" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <span class="station-id"><?php echo h($station['pk_serialNumber']); ?></span>
                            <span><?php echo h($station['name'] ?? ''); ?></span>
                            <span><?php echo h($station['fk_user_owns'] ?? 'Unassigned'); ?></span>
                            <a
                                href="edit_station.php?station_id=<?php echo urlencode((string) $station['pk_serialNumber']); ?>&sort=<?php echo urlencode((string) $sort); ?>"
                                class="primary-btn"
                                style="margin-left: auto;"
                            >
                                Edit Station
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2 class="card-title">Create Station</h2>
            <form method="post" class="register-form">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label class="field-label" for="station-serial">Serial Number:</label>
                    <input
                        id="station-serial"
                        type="text"
                        name="station_serial"
                        class="input-text"
                        placeholder="Enter station serial number"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="field-label" for="station-name">Name:</label>
                    <input id="station-name" type="text" name="name" class="input-text" placeholder="Optional station name">
                </div>

                <div class="form-group">
                    <label class="field-label" for="station-description">Description:</label>
                    <textarea id="station-description" name="description" class="input-textarea" placeholder="Optional station description"></textarea>
                </div>

                <div class="form-group">
                    <label class="field-label" for="station-owner">Owner user:</label>
                    <select id="station-owner" name="owner" class="input-select">
                        <option value="">Unassigned</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo h($user['pk_username']); ?>">
                                <?php echo h($user['pk_username']); ?><?php echo !empty($user['firstName']) || !empty($user['lastName']) ? ' (' . h(trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''))) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="primary-btn">Create Station</button>
            </form>
        </section>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>