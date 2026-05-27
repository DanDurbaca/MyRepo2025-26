<?php
// If already logged in, redirect to home with a flash message
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/csrf.php';
if (isset($_SESSION['username'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You are already logged in.']);
        exit;
    }
    $_SESSION['flash'] = 'You are already logged in.';
    header('Location: index.php');
    exit;
}

// Handle AJAX registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $response['message'] = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic validation
        if ($username === '' || $fullname === '' || $email === '' || $password === '') {
            $response['message'] = 'All fields are required.';
        } elseif (!preg_match('/^[A-Za-z0-9_\-]{3,64}$/', $username)) {
            $response['message'] = 'Invalid username. Use 3-64 letters, numbers, underscore or dash.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email address.';
        } elseif (strlen($password) < 8) {
            $response['message'] = 'Password must be at least 8 characters.';
        } else {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT pk_username FROM `user` WHERE pk_username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);
            $exists = $stmt->fetch();
            if ($exists) {
                $response['message'] = 'Username or email already in use. Choose another.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                // Split fullname into first and last name
                $parts = preg_split('/\s+/', $fullname, 2);
                $firstName = $parts[0] ?? '';
                $lastName = $parts[1] ?? '';

                $stmt = $pdo->prepare("INSERT INTO `user` (pk_username, firstName, lastName, password, email) VALUES (?, ?, ?, ?, ?)");
                try {
                    $stmt->execute([$username, $firstName, $lastName, $passwordHash, $email]);

                    // Create email verification token and send an email
                    require_once __DIR__ . '/inc/mail.php';
                    $token = bin2hex(random_bytes(16));
                    $expires = (new DateTimeImmutable('now'))->add(new DateInterval('P2D'))->format('Y-m-d H:i:s'); // 2 days
                    $ins = $pdo->prepare("INSERT INTO password_reset (pkfk_username, token, type, expires_at) VALUES (?, ?, 'email_verification', ?)");
                    $ins->execute([$username, hash('sha256', $token), $expires]);
                    $verifyUrl = sprintf('%s/verify_email.php?token=%s', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'), $token);
                    $body = "<p>Hello " . htmlspecialchars($firstName) . ",</p><p>Please verify your email by clicking: <a href=\"$verifyUrl\">Verify email</a></p>";
                    send_mail($email, 'Verify your email', $body);

                    $response['success'] = true;
                    $response['message'] = 'Registration successful! A verification email has been sent to your address.';
                } catch (PDOException $e) {
                    $response['message'] = 'Registration failed: see server logs.';
                    error_log('Registration failed: ' . $e->getMessage());
                }
            }
        }
    }

    echo json_encode($response);
    exit;
}
$pageTitle = 'Register';
require_once __DIR__ . '/_header.php';
?>
<div class="container">
    <h1>Register</h1>

    <div class="box" style="max-width: 400px;">
        <form id="registerForm">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="ajax" value="1">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required
                       pattern="[A-Za-z0-9_\-]{3,64}" title="3-64 letters, numbers, underscore or dash">
            </div>

            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
                <span class="text-small text-muted">At least 8 characters</span>
            </div>

            <button type="submit" class="btn btn-success" id="registerBtn">Register</button>
        </form>

        <p class="mt-20 text-small">
            <a href="login.php">Already have an account? Login</a>
        </p>
    </div>
</div>

<script>
// Wire up AJAX registration form submission using common ajaxSubmitForm helper
$(document).ready(function() {
    var form = $('#registerForm');
    var registerBtn = $('#registerBtn');

    form.on('submit', function(e) {
        e.preventDefault();

        registerBtn.prop('disabled', true).text('Registering...');

        ajaxSubmitForm(form, function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
            } else {
                showToast(response.message, 'error');
                registerBtn.prop('disabled', false).text('Register');
            }
        }, function() {
            showToast('An error occurred. Please try again.', 'error');
            registerBtn.prop('disabled', false).text('Register');
        });
    });
});
</script>
</body>
</html>