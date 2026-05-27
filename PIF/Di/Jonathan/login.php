<?php
// If already logged in, redirect to home with a flash message
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/csrf.php';
if (isset($_SESSION['username'])) {
    // For AJAX login attempts, return JSON instead of redirecting.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Already logged in.']);
        exit;
    }
    $_SESSION['flash'] = 'You are already logged in.';
    header('Location: index.php');
    exit;
}

// Handle AJAX login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $response['message'] = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $response['message'] = 'Please enter both username and password.';
        } else {
            $stmt = $pdo->prepare("SELECT pk_username, password, role FROM `user` WHERE pk_username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user) {
                $response['message'] = 'User not found. Please register first.';
            } elseif (!password_verify($password, $user['password'])) {
                $response['message'] = 'Incorrect password. Please try again.';
            } else {
                session_regenerate_id(true);
                $_SESSION['username'] = $user['pk_username'];
                $_SESSION['is_admin'] = ($user['role'] === 'Admin');
                $response['success'] = true;
                $response['message'] = 'Login successful!';
            }
        }
    }

    echo json_encode($response);
    exit;
}
$pageTitle = 'Login';
require_once __DIR__ . '/_header.php';
?>
<div class="container">
    <h1>Login</h1>

    <div class="box" style="max-width: 400px;">
        <form id="loginForm">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="ajax" value="1">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary" id="loginBtn">Login</button>
        </form>

        <p class="mt-20 text-small">
            <a href="forgot_password.php">Forgot Password?</a> |
            <a href="register.php">Register</a>
        </p>
    </div>
</div>

<script>
// Setup AJAX login form submission and button/UI state handling
$(document).ready(function() {
    var form = $('#loginForm');
    var loginBtn = $('#loginBtn');

    form.on('submit', function(e) {
        e.preventDefault();

        loginBtn.prop('disabled', true).text('Logging in...');

        ajaxSubmitForm(form, function(response) {
            if (response.success) {
                showToast('Login successful! Redirecting...', 'success');
                setTimeout(function() {
                    window.location.href = 'user/dashboard.php';
                }, 1500);
            } else {
                showToast(response.message || 'Login failed. Please try again.', 'error');
                loginBtn.prop('disabled', false).text('Login');
            }
        }, function() {
            showToast('An error occurred. Please try again.', 'error');
            loginBtn.prop('disabled', false).text('Login');
        });
    });
});
</script>
</body>
</html>