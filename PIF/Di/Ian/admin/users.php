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
$users = [];

$sortOptions = [
    'user_asc' => 'u.pk_username ASC',
    'user_desc' => 'u.pk_username DESC',
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

    if (isset($_SESSION['admin_user_msg'])) {
        $successMsg = (string) $_SESSION['admin_user_msg'];
        unset($_SESSION['admin_user_msg']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = trim((string) $_POST['action']);

        if ($action === 'create') {
            $username = trim($_POST['username'] ?? '');
            $firstName = trim($_POST['firstName'] ?? '');
            $lastName = trim($_POST['lastName'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!$username || !$firstName || !$lastName || !$email || !$password) {
                $dbError = 'Username, name, email, and password are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $dbError = 'Invalid email address.';
            } elseif (strlen($password) < 6) {
                $dbError = 'Password must be at least 6 characters.';
            } else {
                $checkUserStmt = $pdo->prepare('SELECT pk_username FROM user WHERE pk_username = :username');
                $checkUserStmt->execute([':username' => $username]);

                if ($checkUserStmt->fetch()) {
                    $dbError = 'Username already exists.';
                } else {
                    $checkEmailStmt = $pdo->prepare('SELECT pk_username FROM user WHERE email = :email');
                    $checkEmailStmt->execute([':email' => $email]);

                    if ($checkEmailStmt->fetch()) {
                        $dbError = 'Email already registered.';
                    } else {
                        $insertStmt = $pdo->prepare(
                            'INSERT INTO user (pk_username, firstName, lastName, email, password, role)
                             VALUES (:username, :firstName, :lastName, :email, :password, :role)'
                        );
                        $insertStmt->execute([
                            ':username' => $username,
                            ':firstName' => $firstName,
                            ':lastName' => $lastName,
                            ':email' => $email,
                            ':password' => password_hash($password, PASSWORD_BCRYPT),
                            ':role' => 'User',
                        ]);

                        $_SESSION['admin_user_msg'] = 'User created successfully.';
                        header('Location: users.php?sort=' . urlencode($sort));
                        exit;
                    }
                }
            }
        }
    }

    $usersStmt = $pdo->query(
        'SELECT u.pk_username, u.firstName, u.lastName, u.email
         FROM user u
                WHERE u.role = \'User\'
         ORDER BY ' . $sortOptions[$sort]
    );
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Normal Users</title>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<main class="page">
    <div class="stations-container">
        <section class="card">
            <h2 class="card-title">Normal User Management</h2>

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
                        <option value="user_asc" <?php echo $sort === 'user_asc' ? 'selected' : ''; ?>>Username (A-Z)</option>
                        <option value="user_desc" <?php echo $sort === 'user_desc' ? 'selected' : ''; ?>>Username (Z-A)</option>
                    </select>
                </div>
            </form>

            <?php if (empty($users)): ?>
                <p class="muted">No users found.</p>
            <?php else: ?>
                <ul class="stations-list">
                    <?php foreach ($users as $user): ?>
                        <li class="station-card" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <span class="station-id"><?php echo h($user['pk_username']); ?></span>
                            <span><?php echo h(trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''))); ?></span>
                            <span><?php echo h($user['email'] ?? ''); ?></span>
                            <a
                                href="edit_user.php?username=<?php echo urlencode((string) $user['pk_username']); ?>&sort=<?php echo urlencode((string) $sort); ?>"
                                class="primary-btn"
                                style="margin-left: auto;"
                            >
                                Edit User
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2 class="card-title">Create User</h2>
            <form method="post" class="register-form">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label class="field-label" for="username">Username:</label>
                    <input id="username" type="text" name="username" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="firstName">First Name:</label>
                    <input id="firstName" type="text" name="firstName" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="lastName">Last Name:</label>
                    <input id="lastName" type="text" name="lastName" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="email">Email:</label>
                    <input id="email" type="email" name="email" class="input-text" required>
                </div>

                <div class="form-group">
                    <label class="field-label" for="password">Password:</label>
                    <input id="password" type="password" name="password" class="input-text" required>
                </div>

                <button type="submit" class="primary-btn">Create User</button>
            </form>
        </section>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>