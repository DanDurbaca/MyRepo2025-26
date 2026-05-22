<?php
session_start(); // Start session to access user data and store preferences

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Handle POST request when user submits the preferences form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only allow 'dark' or 'light' theme, default to 'light' if anything else is sent
    $theme = $_POST['theme'] === 'dark' ? 'dark' : 'light';
    
    // Save selected theme to session so it persists across pages
    $_SESSION['theme'] = $theme;
    
    // Save to database for logged-in user
    if (isset($_SESSION['username'])) {
        $conn = getDbConnection();
        $stmt = $conn->prepare("UPDATE user SET theme = :theme WHERE pk_username = :username");
        $stmt->execute(['theme' => $theme, 'username' => $_SESSION['username']]);
    }
    
    // Redirect back to the same page to prevent resubmission on refresh
    header('Location: preferences.php');
    exit;
}

// Include the view which renders the preferences page (form for selecting theme)
require __DIR__ . '/../pages/preferences_view.php';
?>