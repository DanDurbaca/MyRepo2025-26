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
$user = null;

$sortOptions = [
	'user_asc' => 'u.pk_username ASC',
	'user_desc' => 'u.pk_username DESC',
];

$sort = $_GET['sort'] ?? ($_POST['sort'] ?? 'user_asc');
if (!isset($sortOptions[$sort])) {
	$sort = 'user_asc';
}

$username = trim((string) ($_GET['username'] ?? $_POST['username'] ?? ''));

try {
	$pdo = getDb();

	$roleStmt = $pdo->prepare('SELECT role FROM user WHERE pk_username = :username');
	$roleStmt->execute([':username' => $_SESSION['username']]);
	$me = $roleStmt->fetch(PDO::FETCH_ASSOC);

	if (($me['role'] ?? '') !== 'Admin') {
		header('Location: index.php');
		exit;
	}

	if (isset($_SESSION['admin_user_msg'])) {
		$successMsg = (string) $_SESSION['admin_user_msg'];
		unset($_SESSION['admin_user_msg']);
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
		$action = trim((string) $_POST['action']);

		if (!$username) {
			$dbError = 'Username is required.';
		} elseif ($action === 'update') {
			$firstName = trim($_POST['firstName'] ?? '');
			$lastName = trim($_POST['lastName'] ?? '');
			$email = trim($_POST['email'] ?? '');
			$password = trim($_POST['password'] ?? '');

			if (!$firstName || !$lastName || !$email) {
				$dbError = 'First name, last name, and email are required.';
			} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$dbError = 'Invalid email address.';
			} elseif ($password !== '' && strlen($password) < 6) {
				$dbError = 'New password must be at least 6 characters.';
			} else {
				$checkTargetStmt = $pdo->prepare('SELECT role FROM user WHERE pk_username = :username');
				$checkTargetStmt->execute([':username' => $username]);
				$targetUser = $checkTargetStmt->fetch(PDO::FETCH_ASSOC);

				if (!$targetUser || ($targetUser['role'] ?? '') !== 'User') {
					$dbError = 'Only normal users can be updated here.';
				} else {
					$checkEmailStmt = $pdo->prepare(
						'SELECT pk_username FROM user WHERE email = :email AND pk_username != :username'
					);
					$checkEmailStmt->execute([
						':email' => $email,
						':username' => $username,
					]);

					if ($checkEmailStmt->fetch()) {
						$dbError = 'Email already in use by another account.';
					} else {
						if ($password !== '') {
							$updateStmt = $pdo->prepare(
								'UPDATE user
								 SET firstName = :firstName, lastName = :lastName, email = :email, password = :password
								 WHERE pk_username = :username'
							);
							$updateStmt->execute([
								':firstName' => $firstName,
								':lastName' => $lastName,
								':email' => $email,
								':password' => password_hash($password, PASSWORD_BCRYPT),
								':username' => $username,
							]);
						} else {
							$updateStmt = $pdo->prepare(
								'UPDATE user
								 SET firstName = :firstName, lastName = :lastName, email = :email
								 WHERE pk_username = :username'
							);
							$updateStmt->execute([
								':firstName' => $firstName,
								':lastName' => $lastName,
								':email' => $email,
								':username' => $username,
							]);
						}

						$_SESSION['admin_user_msg'] = 'User updated successfully.';
						header('Location: edit_user.php?username=' . urlencode($username) . '&sort=' . urlencode($sort));
						exit;
					}
				}
			}
		} elseif ($action === 'delete') {
			if ($username === $_SESSION['username']) {
				$dbError = 'You cannot delete your own account from admin panel.';
			} else {
				$deleteStmt = $pdo->prepare('DELETE FROM user WHERE pk_username = :username AND role = :role');
				$deleteStmt->execute([
					':username' => $username,
					':role' => 'User',
				]);

				if ($deleteStmt->rowCount() < 1) {
					$dbError = 'Only normal users can be deleted here.';
				} else {
					$_SESSION['admin_user_msg'] = 'User deleted successfully.';
					header('Location: users.php?sort=' . urlencode($sort));
					exit;
				}
			}
		}
	}

	if ($username) {
		$userStmt = $pdo->prepare(
			'SELECT pk_username, firstName, lastName, email, role
			 FROM user
			 WHERE pk_username = :username'
		);
		$userStmt->execute([':username' => $username]);
		$user = $userStmt->fetch(PDO::FETCH_ASSOC);

		if (!$user || ($user['role'] ?? '') !== 'User') {
			$dbError = 'User not found.';
			$user = null;
		}
	} else {
		$dbError = 'No user selected.';
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
	<title>Edit User</title>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<main class="page">
	<div class="stations-container">
		<section class="card">
			<h2 class="card-title">Edit User</h2>

			<p class="muted" style="margin-top: -8px; margin-bottom: 12px;">
				<a href="users.php?sort=<?php echo urlencode((string) $sort); ?>">Back to user list</a>
			</p>

			<?php if ($successMsg): ?>
				<p class="success-text"><?php echo h($successMsg); ?></p>
			<?php endif; ?>

			<?php if ($dbError): ?>
				<p class="error-text"><?php echo h($dbError); ?></p>
			<?php endif; ?>

			<?php if ($user): ?>
				<form method="post" class="register-form">
					<input type="hidden" name="action" value="update">
					<input type="hidden" name="username" value="<?php echo h($user['pk_username']); ?>">
					<input type="hidden" name="sort" value="<?php echo h($sort); ?>">

					<div class="form-group">
						<label class="field-label">Username:</label>
						<span class="station-id"><?php echo h($user['pk_username']); ?></span>
					</div>

					<div class="form-group">
						<label class="field-label" for="firstName">First Name:</label>
						<input
							id="firstName"
							type="text"
							name="firstName"
							class="input-text"
							value="<?php echo h($user['firstName'] ?? ''); ?>"
							required
						>
					</div>

					<div class="form-group">
						<label class="field-label" for="lastName">Last Name:</label>
						<input
							id="lastName"
							type="text"
							name="lastName"
							class="input-text"
							value="<?php echo h($user['lastName'] ?? ''); ?>"
							required
						>
					</div>

					<div class="form-group">
						<label class="field-label" for="email">Email:</label>
						<input
							id="email"
							type="email"
							name="email"
							class="input-text"
							value="<?php echo h($user['email'] ?? ''); ?>"
							required
						>
					</div>

					<div class="form-group">
						<label class="field-label" for="password">New Password:</label>
						<input
							id="password"
							type="password"
							name="password"
							class="input-text"
							placeholder="Leave blank to keep current password"
						>
					</div>

					<button type="submit" class="primary-btn">Save Changes</button>
				</form>

				<form method="post" onsubmit="return confirm('Delete this user account? This cannot be undone.');" style="margin-top: 12px;">
					<input type="hidden" name="action" value="delete">
					<input type="hidden" name="username" value="<?php echo h($user['pk_username']); ?>">
					<input type="hidden" name="sort" value="<?php echo h($sort); ?>">
					<button type="submit" class="danger-btn">Delete User</button>
				</form>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
