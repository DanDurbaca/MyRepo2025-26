<?php
// commonphp.php - database connection and simple auth gate
session_start();

$host = 'localhost';
$userName = 'root';
$pswd = '';
$dbName = 'PIF';

$conn = mysqli_connect($host, $userName, $pswd, $dbName);
if (!$conn) {
    // In production, avoid showing DB errors.
    die('Database connection error.');
}

// If user is logged in by id but session username not set, fetch UName and admin state from DB
if (empty($_SESSION['username']) && !empty($_SESSION['user_id'])) {
    $stmt = mysqli_prepare($conn, 'SELECT UName, administrator FROM `User` WHERE user_ID = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $uname, $adminFlag);
        if (mysqli_stmt_fetch($stmt) && $uname !== null) {
            // keep both naming styles so code stays simple and compatible with your old site
            $_SESSION['username'] = $uname;        // current code expects this
            $_SESSION['User'] = $uname;            // old Website used $_SESSION['User']
            $_SESSION['UserLoggedIn'] = true;      // old Website used this flag
            $_SESSION['is_admin'] = (int)$adminFlag === 1;
        }
        mysqli_stmt_close($stmt);
    }
}

// Simple access control: allow only index.php and Register.php for anonymous users
$publicPages = ['index.php', 'Register.php', 'recieving.php'];
$currentPage = basename($_SERVER['PHP_SELF']);

// allow access only when either new or old session flag exists
$loggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['UserLoggedIn']);

if (!$loggedIn && !in_array($currentPage, $publicPages, true)) {
    header('Location: index.php');
    exit;
}

// Show navigation bar on all pages except the public pages (login/register)
if (!in_array($currentPage, $publicPages, true)) {
    // prefer the old simple 'User' session name if present
    if (!empty($_SESSION['User'])) {
        $username = htmlspecialchars($_SESSION['User'], ENT_QUOTES, 'UTF-8');
    } elseif (!empty($_SESSION['username'])) {
        $username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
    } else {
        $username = '';
    }

    $activeHome = $currentPage === 'Homepage.php' ? 'active' : '';
    $activeFriends = $currentPage === 'Friendlist.php' ? 'active' : '';
    $activeStations = $currentPage === 'Stations.php' ? 'active' : '';
    $activeMeasurments = $currentPage === 'Measurments.php' ? 'active' : '';
    $activeCollections = $currentPage === 'collections.php' ? 'active' : '';
    $activeAccount = $currentPage === 'Account.php' ? 'active' : '';
    $activeLogout = $currentPage === 'Logout.php' ? 'active' : '';
    $activeAdmin = $currentPage === 'Admin.php' ? 'active' : '';
    $isAdmin = !empty($_SESSION['is_admin']);
?>
<!-- Theme support: lightweight CSS + JS inserted here so all pages that include commonphp.php get it -->
<style>
/* minimal theme variables and overrides */
:root {
  --page-bg: #f8fafc;
  --page-color: #0b1220;
  --nav-bg: #111827;
  --nav-color: #e6edf3;
  --link-color: #cbd5e1;
  --link-hover-bg: rgba(255,255,255,0.03);
  --active-bg: #2563eb;
  --username-color: #9ca3af;
}

/* dark theme */
body.dark {
  background-color: #071019;
  color: #dce8f2;
}
body.dark .navbar { background: #0b1220; color: var(--nav-color); }
body.dark .nav-link { color: var(--link-color); }
body.dark .nav-link:hover { background: rgba(255,255,255,0.04); color: #fff; }
body.dark .nav-link.active { background: #1e40af; color: #fff; }
body.dark .navbar-user { color: var(--username-color); }

/* small toggle button in navbar */
.toggle-btn {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.06);
  color: inherit;
  padding: 0.35rem 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  margin-left: 0.5rem;
}
@media (max-width:680px){
  .toggle-btn { margin-top: 0.4rem; }
}
</style>

<script>
(function () {
  const KEY = 'theme';
  const DARK_CLASS = 'dark';
  const btnId = 'theme-toggle';

  function applyInitial() {
    const stored = localStorage.getItem(KEY);
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const startDark = stored ? stored === 'dark' : prefersDark;
    if (startDark) document.body.classList.add(DARK_CLASS);
  }

  window.toggleTheme = function () {
    const isDark = document.body.classList.toggle(DARK_CLASS);
    localStorage.setItem(KEY, isDark ? 'dark' : 'light');
    updateButton();
  };

  function updateButton() {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    const isDark = document.body.classList.contains(DARK_CLASS);
    btn.textContent = isDark ? 'Light' : 'Dark';
    btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
  }

  // apply initial theme as early as possible
  try { applyInitial(); } catch (e) { /* ignore */ }
  document.addEventListener('DOMContentLoaded', updateButton);
})();
</script>

<nav class="navbar">
  <div class="navbar-brand"><a href="Homepage.php">PIF</a></div>
  <ul class="navbar-list">
    <li class="nav-item"><a class="nav-link {$activeHome}" href="Homepage.php">Home</a></li>
    <li class="nav-item"><a class="nav-link {$activeFriends}" href="Friendlist.php">Friends</a></li>
    <li class="nav-item"><a class="nav-link {$activeStations}" href="Stations.php">Stations</a></li>
    <li class="nav-item"><a class="nav-link {$activeMeasurments}" href="Measurments.php">Measurements</a></li>
    <li class="nav-item"><a class="nav-link {$activeCollections}" href="collections.php">Collections</a></li>
    <li class="nav-item"><a class="nav-link {$activeAccount}" href="Account.php">Account</a></li>
    <?php if (!empty($isAdmin)): ?>
      <li class="nav-item"><a class="nav-link {$activeAdmin}" href="Admin.php">Admin</a></li>
    <?php endif; ?>
    <li class="nav-item"><a class="nav-link {$activeLogout}" href="Logout.php">Logout</a></li>
  </ul>
  <div style="display:flex;align-items:center;">
     <!-- theme toggle button -->
    <button id="theme-toggle" class="toggle-btn" onclick="toggleTheme()" aria-label="Toggle theme">Dark</button>
  
    <div class="navbar-user"><?= $username ?></div>
    </div>
</nav>
<?php
}
?>