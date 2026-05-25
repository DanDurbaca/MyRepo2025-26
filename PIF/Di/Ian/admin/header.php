<header class="topbar">
	<div class="brand">
		<div class="brand-mark">PI</div>
	</div>
	<button id="mobile-menu-btn" class="mobile-menu-btn" type="button" aria-label="Menu">☰</button>
	<nav class="nav-links" id="mobile-nav">
		<a href="/admin/index.php">Home</a>
        <a href="/admin/users.php">Users</a>
        <a href="/admin/stations.php">Stations</a>
        <a href="/admin/measurements.php">Measurements</a>
        <a href="/admin/collections.php">Collections</a>
        
	</nav>
	<div class="user-actions">
		<button id="dark-mode-toggle" class="dark-mode-btn" type="button" title="Toggle dark mode">🌙</button>
		<?php if (isset($_SESSION['username'])): ?>
			<form method="post" style="display: inline;">
				<button class="logout-btn" type="submit" name="logout" value="1">
					Logout (<?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>)
				</button>
			</form>
		<?php else: ?>
			<a href="/login.php" class="login-btn" style="text-decoration: none;">Login</a>
		<?php endif; ?>
	</div>
</header>

<script>
// Dark mode toggle with localStorage persistence
(function() {
    const toggle = document.getElementById('dark-mode-toggle');
    const html = document.documentElement;
    
    // Load dark mode preference from localStorage
    const isDark = localStorage.getItem('darkMode') === 'true';
    if (isDark) {
        html.setAttribute('data-theme', 'dark');
        toggle.textContent = '☀️';
    }
    
    // Toggle dark mode
    toggle.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('darkMode', newTheme === 'dark');
        toggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
    });

    // Mobile menu toggle
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileNav = document.getElementById('mobile-nav');
    if (menuBtn && mobileNav) {
        menuBtn.addEventListener('click', function() {
            mobileNav.classList.toggle('mobile-open');
            menuBtn.textContent = mobileNav.classList.contains('mobile-open') ? '✕' : '☰';
        });
        // Close menu when clicking a link
        mobileNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileNav.classList.remove('mobile-open');
                menuBtn.textContent = '☰';
            });
        });
    }
})();
</script>

<?php
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: /login.php');
    exit;
}
?>