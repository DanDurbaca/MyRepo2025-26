<?php
/*
 * includes/auth_check.php
 * Purpose: Protect pages by ensuring a user is authenticated.
 * Usage: Include this file on pages that require an authenticated user.
 * Behavior: Redirects to `/login.php` when `$_SESSION['username']` is not set.
 */
if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit;
}
?>