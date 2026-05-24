<!--
  includes/navbar.php
  Purpose: Reusable navigation bar included on pages. Shows links to dashboard, stations, collections and friends.
  Notes:
  - Uses `$_SESSION['role']` to show an Admin link when appropriate.
  - Displays logged-in username via `$_SESSION['username']`.
-->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand text-primary" href="/pif/dashboard.php">PIF</a>

    <ul class="navbar-nav me-auto">
      <li class="nav-item">
        <a class="nav-link" href="/pif/dashboard.php">Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/pif/stations/my_stations.php">Stations</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/pif/collections/collections.php">Collections</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/pif/friends/friends.php">Friends</a>
      </li>

      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
      <li class="nav-item">
        <a class="nav-link text-warning" href="/pif/admin/admin_dashboard.php">Admin</a>
      </li>
      <?php endif; ?>
    </ul>

    <span class="navbar-text me-3">
        <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
    </span>

    <a class="btn btn-outline-danger btn-sm" href="/pif/logout.php">
        Logout
    </a>
  </div>
</nav>
