<?php
/*
 * logout.php
 * Purpose: Terminate the user session and redirect to login page.
 * Sections:
 *  - Calls `session_start()` then `session_destroy()` to clear session data
 *  - Redirects user to `login.php`
 */
session_start();
session_destroy();
header("Location: login.php");
exit;
