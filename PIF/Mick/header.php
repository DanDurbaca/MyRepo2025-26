<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';

// Initialize language and theme from localStorage or database
$current_language = 'en';
$current_theme = 'light';

if (is_logged_in()) {
    $uid = current_user_id();
    $mysqli = db_connect();
    $safe_uid = $mysqli->real_escape_string($uid);
    $res = $mysqli->query("SELECT theme, language FROM env_user_settings WHERE usr_ref='". $safe_uid ."' LIMIT 1");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        $current_theme = $row['theme'];
        $current_language = $row['language'];
    } else {
        // Create default settings if not exist
        $mysqli->query("INSERT INTO env_user_settings (usr_ref, theme, language) VALUES ('". $safe_uid ."', 'light', 'en') ON DUPLICATE KEY UPDATE theme=theme");
    }
}

// Handle theme/language changes via POST/GET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
        $uid = current_user_id();
        $mysqli = db_connect();
        $safe_uid = $mysqli->real_escape_string($uid);
        
        if (isset($_POST['theme'])) {
            $theme = $mysqli->real_escape_string($_POST['theme']);
            $current_theme = $theme;
            $mysqli->query("UPDATE env_user_settings SET theme='". $theme ."' WHERE usr_ref='". $safe_uid ."'");
        }
        
        if (isset($_POST['language'])) {
            $lang = $mysqli->real_escape_string($_POST['language']);
            if (in_array($lang, ['en', 'de', 'fr'])) {
                $current_language = $lang;
                $mysqli->query("UPDATE env_user_settings SET language='". $lang ."' WHERE usr_ref='". $safe_uid ."'");
            }
        }
    }
}

// Get translations for current language
$t = get_translations($current_language);
?>
<!doctype html>
<html lang="<?php echo htmlspecialchars($current_language); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($t['title']); ?></title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
  <script>
    // Initialize theme from localStorage or database
    const currentTheme = '<?php echo htmlspecialchars($current_theme); ?>';
    if (currentTheme === 'dark') {
      document.documentElement.classList.add('dark-mode');
      document.body.classList.add('dark-mode');
    }
    localStorage.setItem('theme', currentTheme);
    localStorage.setItem('language', '<?php echo htmlspecialchars($current_language); ?>');
  </script>
</head>
<body<?php if ($current_theme === 'dark') echo ' class="dark-mode"'; ?>>
<header>
  <div class="wrap">
    <h1><a href="index.php"><?php echo htmlspecialchars($t['app_name']); ?></a></h1>
    <nav>
      <?php if (is_logged_in()): ?>
        <a href="welcome.php"><?php echo htmlspecialchars($t['nav_welcome']); ?></a>
        <a href="stations.php"><?php echo htmlspecialchars($t['nav_stations']); ?></a>
        <a href="measurements.php"><?php echo htmlspecialchars($t['nav_measurements']); ?></a>
        <a href="collections.php"><?php echo htmlspecialchars($t['nav_collections']); ?></a>
        <a href="friends.php"><?php echo htmlspecialchars($t['nav_friends']); ?></a>
        <a href="chat.php"><?php echo htmlspecialchars($t['nav_chat']); ?></a>
        <?php if (is_admin()): ?>
          <a href="admin_users.php"><?php echo htmlspecialchars($t['nav_admin']); ?></a>
        <?php endif; ?>
        <div class="header-controls">
          <select onchange="changeLanguage(this.value)">
            <option value="en" <?php if ($current_language === 'en') echo 'selected'; ?>>English</option>
            <option value="de" <?php if ($current_language === 'de') echo 'selected'; ?>>Deutsch</option>
            <option value="fr" <?php if ($current_language === 'fr') echo 'selected'; ?>>Français</option>
          </select>
          <button onclick="toggleTheme()" title="<?php echo htmlspecialchars($t['toggle_theme']); ?>">🌙</button>
          <a href="logout.php"><?php echo htmlspecialchars($t['nav_logout']); ?></a>
        </div>
      <?php else: ?>
        <a href="index.php"><?php echo htmlspecialchars($t['nav_login']); ?></a>
        <a href="register.php"><?php echo htmlspecialchars($t['nav_register']); ?></a>
        <div class="header-controls">
          <select onchange="changeLanguage(this.value)">
            <option value="en" <?php if ($current_language === 'en') echo 'selected'; ?>>English</option>
            <option value="de" <?php if ($current_language === 'de') echo 'selected'; ?>>Deutsch</option>
            <option value="fr" <?php if ($current_language === 'fr') echo 'selected'; ?>>Français</option>
          </select>
          <button onclick="toggleTheme()" title="<?php echo htmlspecialchars($t['toggle_theme']); ?>">🌙</button>
        </div>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="wrap">
<script>
function toggleTheme() {
  const html = document.documentElement;
  const body = document.body;
  const isDark = body.classList.contains('dark-mode');
  const newTheme = isDark ? 'light' : 'dark';
  
  if (isDark) {
    body.classList.remove('dark-mode');
  } else {
    body.classList.add('dark-mode');
  }
  
  localStorage.setItem('theme', newTheme);
  
  <?php if (is_logged_in()): ?>
  // Update in database
  fetch('api_settings.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=update_theme&theme=' + encodeURIComponent(newTheme)
  });
  <?php endif; ?>
}

function changeLanguage(lang) {
  localStorage.setItem('language', lang);
  <?php if (is_logged_in()): ?>
  fetch('api_settings.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=update_language&language=' + encodeURIComponent(lang)
  }).then(() => location.reload());
  <?php else: ?>
  location.reload();
  <?php endif; ?>
}
</script>
