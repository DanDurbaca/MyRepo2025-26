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
$successMsg = '';
$station = null;
$users = [];

$sortOptions = [
	'user_asc' => '(s.fk_user_owns IS NULL), s.fk_user_owns ASC, s.pk_serialNumber ASC',
	'user_desc' => '(s.fk_user_owns IS NULL), s.fk_user_owns DESC, s.pk_serialNumber ASC',
	'serial_asc' => 's.pk_serialNumber ASC',
	'serial_desc' => 's.pk_serialNumber DESC',
];

$sort = $_GET['sort'] ?? ($_POST['sort'] ?? 'user_asc');
if (!isset($sortOptions[$sort])) {
	$sort = 'user_asc';
}

$stationId = trim((string) ($_GET['station_id'] ?? $_POST['station_id'] ?? ''));

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
		$action = trim((string) $_POST['action']);

		if (!$stationId) {
			$dbError = 'Station ID is required.';
		} elseif ($action === 'update') {
			$name = trim($_POST['name'] ?? '');
			$description = trim($_POST['description'] ?? '');
			$owner = trim($_POST['owner'] ?? '');

			$existsStmt = $pdo->prepare('SELECT pk_serialNumber FROM station WHERE pk_serialNumber = :id');
			$existsStmt->execute([':id' => $stationId]);

			if (!$existsStmt->fetch()) {
				$dbError = 'Station not found.';
			} else {
				$updateStmt = $pdo->prepare(
					'UPDATE station
					 SET name = :name, description = :description, fk_user_owns = :owner
					 WHERE pk_serialNumber = :id'
				);
				$updateStmt->execute([
					':name' => $name !== '' ? $name : null,
					':description' => $description !== '' ? $description : null,
					':owner' => $owner !== '' ? $owner : null,
					':id' => $stationId,
				]);

				$_SESSION['admin_station_msg'] = 'Station updated successfully.';
				header('Location: edit_station.php?station_id=' . urlencode($stationId) . '&sort=' . urlencode($sort));
				exit;
			}
		} elseif ($action === 'delete') {
			$deleteStmt = $pdo->prepare('DELETE FROM station WHERE pk_serialNumber = :id');
			$deleteStmt->execute([':id' => $stationId]);

			$_SESSION['admin_station_msg'] = 'Station deleted successfully.';
			header('Location: stations.php?sort=' . urlencode($sort));
			exit;
		}
	}

	$usersStmt = $pdo->query('SELECT pk_username, firstName, lastName FROM user ORDER BY pk_username ASC');
	$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

	if ($stationId) {
		$stationStmt = $pdo->prepare(
			'SELECT s.pk_serialNumber, s.name, s.description, s.fk_user_owns
			 FROM station s
			 WHERE s.pk_serialNumber = :id'
		);
		$stationStmt->execute([':id' => $stationId]);
		$station = $stationStmt->fetch(PDO::FETCH_ASSOC);

		if (!$station) {
			$dbError = 'Station not found.';
		}
	} else {
		$dbError = 'No station selected.';
	}
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
	<title>Edit Station</title>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<main class="page">
	<div class="stations-container">
		<section class="card">
			<h2 class="card-title">Edit Station</h2>

			<p class="muted" style="margin-top: -8px; margin-bottom: 12px;">
				<a href="stations.php?sort=<?php echo urlencode((string) $sort); ?>">Back to station list</a>
			</p>

			<?php if ($successMsg): ?>
				<p class="success-text"><?php echo h($successMsg); ?></p>
			<?php endif; ?>

			<?php if ($dbError): ?>
				<p class="error-text"><?php echo h($dbError); ?></p>
			<?php endif; ?>

			<?php if ($station): ?>
				<form method="post" class="register-form">
					<input type="hidden" name="action" value="update">
					<input type="hidden" name="station_id" value="<?php echo h($station['pk_serialNumber']); ?>">
					<input type="hidden" name="sort" value="<?php echo h($sort); ?>">

					<div class="form-group">
						<label class="field-label">Serial:</label>
						<span class="station-id"><?php echo h($station['pk_serialNumber']); ?></span>
					</div>

					<div class="form-group">
						<label class="field-label" for="name">Name:</label>
						<input
							id="name"
							type="text"
							name="name"
							class="input-text"
							value="<?php echo h($station['name'] ?? ''); ?>"
							placeholder="Station name"
						>
					</div>

					<div class="form-group">
						<label class="field-label" for="description">Description:</label>
						<textarea
							id="description"
							name="description"
							class="input-textarea"
							placeholder="Station description"
						><?php echo h($station['description'] ?? ''); ?></textarea>
					</div>

					<div class="form-group">
						<label class="field-label" for="owner">Owner user:</label>
						<select id="owner" name="owner" class="input-select">
							<option value="">Unassigned</option>
							<?php foreach ($users as $user): ?>
								<option value="<?php echo h($user['pk_username']); ?>" <?php echo ($station['fk_user_owns'] ?? '') === $user['pk_username'] ? 'selected' : ''; ?>>
									<?php echo h($user['pk_username']); ?><?php echo !empty($user['firstName']) || !empty($user['lastName']) ? ' (' . h(trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''))) . ')' : ''; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<button type="submit" class="primary-btn">Save Changes</button>
				</form>

				<form method="post" onsubmit="return confirm('Delete this station and its measurements? This cannot be undone.');" style="margin-top: 12px;">
					<input type="hidden" name="action" value="delete">
					<input type="hidden" name="station_id" value="<?php echo h($station['pk_serialNumber']); ?>">
					<input type="hidden" name="sort" value="<?php echo h($sort); ?>">
					<button type="submit" class="danger-btn">Delete Station</button>
				</form>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
