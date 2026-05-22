<?php
/*
 * admin_dashboard.php
 * Purpose: Admin landing page providing quick links to manage users, stations and collections.
 * Sections:
 *  - Includes: configuration, authentication and admin permission checks
 *  - Renders: HTML with navbar, dashboard cards and footer
 */
require "../includes/config.php";
require "../includes/auth_check.php";
require "../includes/admin_check.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pif/assets/css/dark.css" rel="stylesheet">
</head>

<body>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2 class="mb-4">Admin Dashboard</h2>

    <div class="row g-4">

        <!-- USERS -->
        <div class="col-md-4">
            <div class="card dashboard-card p-4 h-100">
                <h5>Users</h5>
                <p class="">
                    Manage user accounts, roles and permissions.
                </p>
                <a href="users.php" class="btn btn-primary mt-auto">
                    Manage users
                </a>
            </div>
        </div>

        <!-- STATIONS -->
        <div class="col-md-4">
            <div class="card dashboard-card p-4 h-100">
                <h5>Stations</h5>
                <p class="">
                    Create, edit and assign measurement stations.
                </p>
                <a href="stations.php" class="btn btn-primary mt-auto">
                    Manage stations
                </a>
            </div>
        </div>

        <!-- COLLECTIONS -->
        <div class="col-md-4">
            <div class="card dashboard-card p-4 h-100">
                <h5>Collections</h5>
                <p class="">
                    View and manage all user collections.
                </p>
                <a href="collections.php" class="btn btn-primary mt-auto">
                    Manage collections
                </a>
            </div>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
