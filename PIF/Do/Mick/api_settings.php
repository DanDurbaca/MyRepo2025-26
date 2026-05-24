<?php
// api_settings.php - API endpoint for updating user settings

require_once __DIR__ . '/db.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$uid = current_user_id();
$mysqli = db_connect();
$safe_uid = $mysqli->real_escape_string($uid);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_theme') {
        $theme = $mysqli->real_escape_string($_POST['theme'] ?? 'light');
        if (in_array($theme, ['light', 'dark'])) {
            $mysqli->query("UPDATE env_user_settings SET theme='". $theme ."' WHERE usr_ref='". $safe_uid ."'");
            echo json_encode(['success' => true, 'theme' => $theme]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid theme']);
        }
    } elseif ($action === 'update_language') {
        $language = $mysqli->real_escape_string($_POST['language'] ?? 'en');
        if (in_array($language, ['en', 'de', 'fr'])) {
            $mysqli->query("UPDATE env_user_settings SET language='". $language ."' WHERE usr_ref='". $safe_uid ."'");
            echo json_encode(['success' => true, 'language' => $language]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid language']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
