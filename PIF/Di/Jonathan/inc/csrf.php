<?php
// Simple CSRF helper
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Generate or return the current CSRF token stored in session (creates if missing)
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

// Return an HTML hidden input element containing the CSRF token
function csrf_input() {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"$t\">";
}

// Validate provided CSRF token against session token and expiry (1 hour)
function validate_csrf($token) {
    if (empty($_SESSION['csrf_token'])) return false;
    if (!hash_equals($_SESSION['csrf_token'], $token)) return false;
    // expiry (1 hour)
    if (!empty($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > 3600)) return false;
    return true;
}

?>
