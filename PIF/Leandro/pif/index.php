<?php
/*
 * index.php
 * Purpose: Entry point that redirects users to dashboard or login depending on session state.
 * Sections:
 *  - Starts session
 *  - Redirects authenticated users to `dashboard.php`, others to `login.php`
 */
session_start();

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
