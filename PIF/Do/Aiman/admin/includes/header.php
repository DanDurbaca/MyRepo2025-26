<?php
require_once __DIR__ . "/CommonCode.php";
/** @var mysqli $conn */
$title = $title ?? "RPIF1";

$currentTheme = getThemePreference($conn);
$currentLanguage = getLanguagePreference($conn);
$cssFile = PIF_APP . "/admin/assets/css/app.css";
$cssVersion = file_exists($cssFile) ? filemtime($cssFile) : time();
$currentPath = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH) ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "toggle_theme") {
  checkCsrf();

  $nextTheme = ($currentTheme === "dark") ? "light" : "dark";
  saveThemePreference($conn, $nextTheme);

  $redirect = $_POST["redirect_to"] ?? $_SERVER["REQUEST_URI"] ?? publicUrl("/login.php");
  header("Location: " . $redirect);
  exit();
}

$pendingFriendRequests = 0;
$unreadChatCount = 0;
if (isLoggedIn() && hasTable($conn, "friend_requests")) {
  $stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS total
    FROM friend_requests
    WHERE receiver_user_id = ? AND status = 'pending'
  ");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $pendingFriendRequests = (int)($row["total"] ?? 0);
  }
}

if (
  isLoggedIn() &&
  hasTable($conn, "chat_rooms") &&
  hasTable($conn, "chat_room_members") &&
  hasTable($conn, "chat_messages") &&
  hasColumn($conn, "chat_room_members", "last_read_message_id")
) {
  $stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS total
    FROM (
      SELECT crm.room_id
      FROM chat_room_members crm
      INNER JOIN chat_messages cm ON cm.room_id = crm.room_id
      WHERE crm.user_id = ?
        AND cm.user_id <> ?
        AND (crm.last_read_message_id IS NULL OR cm.message_id > crm.last_read_message_id)
      GROUP BY crm.room_id
    ) unread_rooms
  ");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $_SESSION["user_id"], $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $unreadChatCount = (int)($row["total"] ?? 0);
  }
}

if (!function_exists("navLinkClass")) {
  function navLinkClass($href, $currentPath) {
    $base = "nav-link pif-nav-link";
    return $base . ($href === $currentPath ? " active" : "");
  }
}
?>
<!DOCTYPE html>
<html lang="<?= esc($currentLanguage) ?>" data-theme="<?= esc($currentTheme) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title) ?></title>

  <!-- Bootstrap for quick clean UI -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= esc(appUrl('/admin/assets/css/app.css')) ?>?v=<?= esc((string)$cssVersion) ?>" rel="stylesheet">
</head>
<body class="theme-<?= esc($currentTheme) ?>">

<nav class="navbar navbar-expand-lg pif-navbar">
  <div class="container">
    <a class="navbar-brand" href="<?= isLoggedIn() ? (isAdmin() ? appUrl('/admin/dashboard.php') : appUrl('/user/welcome.php')) : publicUrl('/login.php') ?>">PIF</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <?php if (isLoggedIn()): ?>
          <li class="nav-item"><a class="<?= navLinkClass(appUrl('/user/welcome.php'), $currentPath) ?>" href="<?= esc(appUrl('/user/welcome.php')) ?>"><?= esc(t("welcome")) ?></a></li>
          <li class="nav-item"><a class="<?= navLinkClass(appUrl('/user/stations.php'), $currentPath) ?>" href="<?= esc(appUrl('/user/stations.php')) ?>"><?= esc(t("stations")) ?></a></li>
          <li class="nav-item"><a class="<?= navLinkClass(appUrl('/user/measurements.php'), $currentPath) ?>" href="<?= esc(appUrl('/user/measurements.php')) ?>"><?= esc(t("measurements")) ?></a></li>
          <li class="nav-item"><a class="<?= navLinkClass(appUrl('/user/collections.php'), $currentPath) ?>" href="<?= esc(appUrl('/user/collections.php')) ?>"><?= esc(t("collections")) ?></a></li>
          <li class="nav-item">
            <a class="<?= navLinkClass(appUrl('/user/friends.php'), $currentPath) ?> d-flex align-items-center gap-2" href="<?= esc(appUrl('/user/friends.php')) ?>">
              <span><?= esc(t("friends")) ?></span>
              <span
                id="friendRequestBadge"
                class="badge rounded-pill bg-danger<?= $pendingFriendRequests > 0 ? "" : " d-none" ?>"
              ><?= $pendingFriendRequests ?></span>
            </a>
          </li>
          <li class="nav-item">
            <a class="<?= navLinkClass(appUrl('/user/chat.php'), $currentPath) ?> d-flex align-items-center gap-2" href="<?= esc(appUrl('/user/chat.php')) ?>">
              <span><?= esc(t("chat")) ?></span>
              <span
                id="chatUnreadDot"
                class="chat-dot-indicator<?= $unreadChatCount > 0 ? "" : " d-none" ?>"
                aria-label="<?= $unreadChatCount ?> unread chats"
              ></span>
            </a>
          </li>
          <li class="nav-item"><a class="<?= navLinkClass(appUrl('/user/account.php'), $currentPath) ?>" href="<?= esc(appUrl('/user/account.php')) ?>"><?= esc(t("account")) ?></a></li>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
          <?php
          $adminPaths = [
            appUrl("/admin/dashboard.php"),
            appUrl("/admin/users.php"),
            appUrl("/admin/stations.php"),
            appUrl("/admin/measurements.php"),
            appUrl("/admin/collections.php"),
          ];
          $adminActive = in_array($currentPath, $adminPaths, true);
          ?>
          <li class="nav-item dropdown">
            <a
              class="nav-link pif-nav-link dropdown-toggle<?= $adminActive ? " active" : "" ?>"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <?= esc(t("admin")) ?>
            </a>
            <ul class="dropdown-menu pif-dropdown-menu">
              <li><a class="dropdown-item<?= $currentPath === appUrl('/admin/dashboard.php') ? " active" : "" ?>" href="<?= esc(appUrl('/admin/dashboard.php')) ?>"><?= esc(t("dashboard")) ?></a></li>
              <li><a class="dropdown-item<?= $currentPath === appUrl('/admin/users.php') ? " active" : "" ?>" href="<?= esc(appUrl('/admin/users.php')) ?>"><?= esc(t("users")) ?></a></li>
              <li><a class="dropdown-item<?= $currentPath === appUrl('/admin/stations.php') ? " active" : "" ?>" href="<?= esc(appUrl('/admin/stations.php')) ?>"><?= esc(t("stations")) ?></a></li>
              <li><a class="dropdown-item<?= $currentPath === appUrl('/admin/measurements.php') ? " active" : "" ?>" href="<?= esc(appUrl('/admin/measurements.php')) ?>"><?= esc(t("measurements")) ?></a></li>
              <li><a class="dropdown-item<?= $currentPath === appUrl('/admin/collections.php') ? " active" : "" ?>" href="<?= esc(appUrl('/admin/collections.php')) ?>"><?= esc(t("collections")) ?></a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav">
        <?php if (!isLoggedIn()): ?>
          <li class="nav-item me-2">
            <form method="post" class="d-inline">
              <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
              <input type="hidden" name="action" value="toggle_theme">
              <input type="hidden" name="redirect_to" value="<?= esc($_SERVER["REQUEST_URI"] ?? publicUrl('/login.php')) ?>">
              <button class="btn btn-sm btn-outline-light" type="submit"><?= $currentTheme === "dark" ? esc(t("light_mode")) : esc(t("dark_mode")) ?></button>
            </form>
          </li>
          <li class="nav-item"><a class="<?= navLinkClass(publicUrl('/register.php'), $currentPath) ?>" href="<?= esc(publicUrl('/register.php')) ?>"><?= esc(t("register")) ?></a></li>
          <li class="nav-item"><a class="<?= navLinkClass(publicUrl('/login.php'), $currentPath) ?>" href="<?= esc(publicUrl('/login.php')) ?>"><?= esc(t("login")) ?></a></li>
        <?php else: ?>
          <li class="nav-item me-2">
            <form method="post" class="d-inline">
              <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
              <input type="hidden" name="action" value="toggle_theme">
              <input type="hidden" name="redirect_to" value="<?= esc($_SERVER["REQUEST_URI"] ?? appUrl('/user/welcome.php')) ?>">
              <button class="btn btn-sm btn-outline-light" type="submit"><?= $currentTheme === "dark" ? esc(t("light_mode")) : esc(t("dark_mode")) ?></button>
            </form>
          </li>
          <li class="nav-item"><span class="navbar-text me-3">Hi, <?= esc($_SESSION["username"]) ?></span></li>
          <li class="nav-item"><a class="<?= navLinkClass(publicUrl('/logout.php'), $currentPath) ?>" href="<?= esc(publicUrl('/logout.php')) ?>"><?= esc(t("logout")) ?></a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
