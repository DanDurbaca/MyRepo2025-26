<?php
/*
 * includes/admin_check.php
 * Purpose: Guard pages that require Admin role.
 * Usage: Include this after `auth_check.php` on admin-only pages.
 * Behavior: Returns HTTP 403 and dies if `$_SESSION['role']` is not `Admin`.
 */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    die("Admin access only");
}
?>