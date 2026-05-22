<?php
// email_notifier.php - Email notification utility

/**
 * Send email notification to user
 * 
 * @param string $to_email - Recipient email address
 * @param string $subject - Email subject
 * @param string $message - Email message body
 * @param string $html_content - Optional HTML content
 * @return bool - True if email was sent successfully
 */
function send_email($to_email, $subject, $message, $html_content = null) {
    // Configuration
    $from_email = "noreply@stations-webapp.local";
    $from_name = "Stations Webapp";
    
    // Validate email
    if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Headers
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    
    if ($html_content) {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body = $html_content;
    } else {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body = $message;
    }
    
    // Send email
    // NOTE: This requires a mail server to be configured on your system
    // For local development, you might want to log emails instead
    $result = mail($to_email, $subject, $body, $headers);
    
    if (!$result) {
        // Log failed email attempt
        error_log("Failed to send email to: " . $to_email . " Subject: " . $subject);
    }
    
    return $result;
}

/**
 * Send friend request notification
 */
function notify_friend_request($mysqli, $to_user, $from_user) {
    // Create database notification
    $title = "Friend request from " . htmlspecialchars($from_user);
    $message = "You have a new friend request from " . htmlspecialchars($from_user);
    
    $safe_to = $mysqli->real_escape_string($to_user);
    $safe_from = $mysqli->real_escape_string($from_user);
    
    $mysqli->query("INSERT INTO env_notification (notif_to, notif_type, notif_title, notif_message, notif_related_user) 
                   VALUES ('". $safe_to ."', 'friend_request', '". $mysqli->real_escape_string($title) ."', '". $mysqli->real_escape_string($message) ."', '". $safe_from ."')");
    
    // Get user email
    $res = $mysqli->query("SELECT usr_email FROM env_user WHERE usr_name='". $safe_to ."' LIMIT 1");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        $subject = "New Friend Request on Stations Webapp";
        $html = "<html><body>";
        $html .= "<h2>Friend Request</h2>";
        $html .= "<p>You have a new friend request from <strong>" . htmlspecialchars($from_user) . "</strong></p>";
        $html .= "<p><a href='http://localhost/WEBAP2_2026/Website/friends.php'>View Friend Requests</a></p>";
        $html .= "</body></html>";
        
        send_email($row['usr_email'], $subject, $message, $html);
    }
}

/**
 * Send collection shared notification
 */
function notify_collection_shared($mysqli, $to_user, $from_user, $collection_name) {
    // Create database notification
    $title = htmlspecialchars($from_user) . " shared a collection with you";
    $message = "Collection: " . htmlspecialchars($collection_name);
    
    $safe_to = $mysqli->real_escape_string($to_user);
    $safe_from = $mysqli->real_escape_string($from_user);
    
    $mysqli->query("INSERT INTO env_notification (notif_to, notif_type, notif_title, notif_message, notif_related_user) 
                   VALUES ('". $safe_to ."', 'collection_shared', '". $mysqli->real_escape_string($title) ."', '". $mysqli->real_escape_string($message) ."', '". $safe_from ."')");
    
    // Get user email
    $res = $mysqli->query("SELECT usr_email FROM env_user WHERE usr_name='". $safe_to ."' LIMIT 1");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        $subject = "New Shared Collection on Stations Webapp";
        $html = "<html><body>";
        $html .= "<h2>Collection Shared</h2>";
        $html .= "<p><strong>" . htmlspecialchars($from_user) . "</strong> shared the collection <strong>" . htmlspecialchars($collection_name) . "</strong> with you.</p>";
        $html .= "<p><a href='http://localhost/WEBAP2_2026/Website/collections.php'>View Collections</a></p>";
        $html .= "</body></html>";
        
        send_email($row['usr_email'], $subject, $message, $html);
    }
}

/**
 * Send error notification
 */
function notify_error($mysqli, $to_user, $error_message) {
    // Create database notification
    $safe_to = $mysqli->real_escape_string($to_user);
    
    $mysqli->query("INSERT INTO env_notification (notif_to, notif_type, notif_title, notif_message) 
                   VALUES ('". $safe_to ."', 'error', 'Error', '". $mysqli->real_escape_string($error_message) ."')");
    
    // Get user email
    $res = $mysqli->query("SELECT usr_email FROM env_user WHERE usr_name='". $safe_to ."' LIMIT 1");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        $subject = "Error Notification from Stations Webapp";
        $html = "<html><body>";
        $html .= "<h2>Error</h2>";
        $html .= "<p>" . htmlspecialchars($error_message) . "</p>";
        $html .= "</body></html>";
        
        send_email($row['usr_email'], $subject, $error_message, $html);
    }
}

/**
 * Send new message notification
 */
function notify_new_message($mysqli, $to_user, $from_user, $preview = '') {
    // Create database notification
    $title = "New message from " . htmlspecialchars($from_user);
    $message = $preview ? substr($preview, 0, 100) : "You have a new message";
    
    $safe_to = $mysqli->real_escape_string($to_user);
    $safe_from = $mysqli->real_escape_string($from_user);
    
    $mysqli->query("INSERT INTO env_notification (notif_to, notif_type, notif_title, notif_message, notif_related_user) 
                   VALUES ('". $safe_to ."', 'chat', '". $mysqli->real_escape_string($title) ."', '". $mysqli->real_escape_string($message) ."', '". $safe_from ."')");
    
    // Get user email
    $res = $mysqli->query("SELECT usr_email FROM env_user WHERE usr_name='". $safe_to ."' LIMIT 1");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        $subject = "New Message from " . htmlspecialchars($from_user);
        $html = "<html><body>";
        $html .= "<h2>New Message</h2>";
        $html .= "<p>You have a new message from <strong>" . htmlspecialchars($from_user) . "</strong></p>";
        $html .= "<p>" . htmlspecialchars($message) . "</p>";
        $html .= "<p><a href='http://localhost/WEBAP2_2026/Website/chat.php'>View Message</a></p>";
        $html .= "</body></html>";
        
        send_email($row['usr_email'], $subject, $message, $html);
    }
}
?>
