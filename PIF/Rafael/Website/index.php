<?php
require_once 'config.php';
require_once 'auth.php';

$auth = new Auth($pdo);
$message = "";

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        
        $result = $auth->login($username, $password);
        $message = $result['message'];
        
        if ($result['success']) {
            redirect('dashboard.php');
        }
    }
    
    // Handle registration
    if ($_POST['action'] === 'register') {
        $username = sanitize($_POST['reg_username']);
        $email = sanitize($_POST['reg_email']);
        $password = $_POST['reg_password'];
        $firstName = sanitize($_POST['reg_firstName']);
        $lastName = sanitize($_POST['reg_lastName']);
        
        $result = $auth->register($username, $email, $password, $firstName, $lastName);
        $message = $result['message'];
        
        if ($result['success']) {
            $message .= " Please login.";
        }
    }
}

$pageCSS = 'auth.css';
$pageJS = 'auth.js';
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <!-- Theme Toggle Button for Login Page -->
    <button id="login-theme-toggle" class="btn btn-sm">
        <i class="bi bi-moon-fill"></i> Toggle Theme
    </button>
    
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="auth-container">
                <!-- Tabs -->
                <ul class="nav nav-tabs auth-tabs" id="authTabs">
                    <li class="nav-item w-50 text-center">
                        <a class="nav-link active" data-bs-toggle="tab" href="#login">Login</a>
                    </li>
                    <li class="nav-item w-50 text-center">
                        <a class="nav-link" data-bs-toggle="tab" href="#register">Register</a>
                    </li>
                </ul>
                
                <div class="tab-content p-4">
                    <!-- Login Tab -->
                    <div class="tab-pane fade show active" id="login">
                        <h3 class="mb-4">Welcome Back</h3>
                        <?php if ($message && strpos($message, 'Login') !== false): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <form id="loginForm" method="POST">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                    
                    <!-- Register Tab -->
                    <div class="tab-pane fade" id="register">
                        <h3 class="mb-4">Create Account</h3>
                        <?php if ($message && strpos($message, 'Register') !== false): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <form id="registerForm" method="POST">
                            <input type="hidden" name="action" value="register">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reg_username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="reg_username" name="reg_username" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reg_email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="reg_email" name="reg_email" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reg_firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="reg_firstName" name="reg_firstName">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reg_lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="reg_lastName" name="reg_lastName">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reg_password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="reg_password" name="reg_password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reg_confirmPassword" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="reg_confirmPassword" name="reg_confirmPassword" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Theme toggle for login page
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('login-theme-toggle');
    const html = document.documentElement;
    
    // Check current theme
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Set initial theme
    if (savedTheme) {
        html.classList.add(savedTheme === 'dark' ? 'dark-mode' : 'light-mode');
    } else if (systemPrefersDark) {
        html.classList.add('dark-mode');
    } else {
        html.classList.add('light-mode');
    }
    
    // Update button text
    updateThemeButton();
    
    // Theme toggle functionality
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const isDark = html.classList.contains('dark-mode');
            
            if (isDark) {
                html.classList.remove('dark-mode');
                html.classList.add('light-mode');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.remove('light-mode');
                html.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            }
            
            updateThemeButton();
        });
    }
    
    function updateThemeButton() {
        if (!themeToggle) return;
        
        const icon = themeToggle.querySelector('i');
        const isDark = html.classList.contains('dark-mode');
        
        if (icon) {
            icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
        themeToggle.innerHTML = `<i class="${isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill'}"></i> ${isDark ? 'Light Mode' : 'Dark Mode'}`;
    }
    
    // Also apply theme to form elements
    function applyThemeToForms() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (html.classList.contains('dark-mode')) {
                    input.style.backgroundColor = '#2d2d2d';
                    input.style.color = '#e0e0e0';
                    input.style.borderColor = '#404040';
                } else {
                    input.style.backgroundColor = '';
                    input.style.color = '';
                    input.style.borderColor = '';
                }
            });
        });
    }
    
    // Apply theme to forms initially
    applyThemeToForms();
    
    // Reapply when theme changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                applyThemeToForms();
                updateThemeButton();
            }
        });
    });
    
    observer.observe(html, { attributes: true });
});
</script>

<?php include 'includes/footer.php'; ?>