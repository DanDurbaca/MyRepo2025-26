<?php if (!isset($pdo)) require_once 'config.php'; ?>
<div class="col-md-3 col-lg-2 px-0 sidebar">
    <div class="d-flex flex-column p-3">
        <h4 class="text-center mb-4">
            <i class="bi bi-house-door"></i> P.I.F.
        </h4>
        
        <!-- Theme Toggle Button in Sidebar - Only one needed -->
        <div class="text-center mb-3">
            <button id="sidebar-theme-toggle" class="btn btn-outline-light btn-sm theme-toggle-btn w-100">
                <i class="bi bi-moon-fill"></i> <span class="theme-text ms-1">Dark Mode</span>
            </button>
        </div>
        
        <hr class="text-white">
        
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'stations.php' ? 'active' : ''; ?>" 
                   href="stations.php">
                    <i class="bi bi-wifi"></i> My Stations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'measurements.php' ? 'active' : ''; ?>" 
                   href="measurements.php">
                    <i class="bi bi-graph-up"></i> Measurements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'collections.php' ? 'active' : ''; ?>" 
                   href="collections.php">
                    <i class="bi bi-collection"></i> Collections
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'friends.php' ? 'active' : ''; ?>" 
                   href="friends.php">
                    <i class="bi bi-people"></i> Friends
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" 
                   href="profile.php">
                    <i class="bi bi-person-circle"></i> Profile
                </a>
            </li>
            <?php if (isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active text-warning' : 'text-warning'; ?>" 
                   href="admin.php">
                    <i class="bi bi-shield-check"></i> Admin Panel
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
        
        <div class="mt-auto text-center text-white small">
            <div>Logged in as: <strong><?php echo $_SESSION['username']; ?></strong></div>
            <div>Role: <?php echo $_SESSION['role']; ?></div>
        </div>
    </div>
</div>

<script>
// Synchronize sidebar theme button with main theme button
document.addEventListener('DOMContentLoaded', function() {
    const sidebarThemeBtn = document.getElementById('sidebar-theme-toggle');
    const mainThemeBtn = document.getElementById('theme-toggle');
    
    if (sidebarThemeBtn && mainThemeBtn) {
        // Sync button clicks
        sidebarThemeBtn.addEventListener('click', () => {
            mainThemeBtn.click();
        });
        
        mainThemeBtn.addEventListener('click', () => {
            // Update sidebar button text after a short delay
            setTimeout(() => {
                const isDark = document.documentElement.classList.contains('dark-mode');
                const themeText = sidebarThemeBtn.querySelector('.theme-text');
                const icon = sidebarThemeBtn.querySelector('i');
                
                if (themeText) {
                    themeText.textContent = isDark ? 'Light Mode' : 'Dark Mode';
                }
                if (icon) {
                    if (isDark) {
                        icon.classList.remove('bi-moon');
                        icon.classList.add('bi-sun');
                    } else {
                        icon.classList.remove('bi-sun');
                        icon.classList.add('bi-moon');
                    }
                }
            }, 100);
        });
        
        // Initial setup of sidebar button text
        const isDark = document.documentElement.classList.contains('dark-mode');
        const themeText = sidebarThemeBtn.querySelector('.theme-text');
        const icon = sidebarThemeBtn.querySelector('i');
        
        if (themeText) {
            themeText.textContent = isDark ? 'Light Mode' : 'Dark Mode';
        }
        if (icon) {
            if (isDark) {
                icon.classList.remove('bi-moon');
                icon.classList.add('bi-sun');
            } else {
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon');
            }
        }
    }
});
</script>