<?php
// Start or resume the session so we can access login data
session_start();

// -------------------------------------------------------------
// Check if user is logged in
// -------------------------------------------------------------

// If the session variable that stores the username is empty...
if (empty($_SESSION["userNameSession"])) {

    // ...output an entire HTML page telling the user to log in
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PIF - Login Required</title>
        <style>
            body { font-family: Arial; background: #212a44ff; color: white; text-align: center; padding: 50px; }
            .box { background: white; color: #333; padding: 30px; border-radius: 10px; max-width: 500px; margin: 0 auto; }
            a { color: #f0de3bff; text-decoration: none; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>Login Required</h1>
            <p>You need to be logged in to view this page.</p>
            <p><a href="Log-in.php">Click here to login</a></p>
        </div>
    </body>
    </html>';

    // Stop the script so nothing below runs
    exit;
}

// -------------------------------------------------------------
// Database connection
// -------------------------------------------------------------

// Database server address
$host = "localhost";

// Name of your database
$db = "portableindoorfeedback";

// MySQL username
$user = "root";

// MySQL password (empty)
$pass = "";

// Connect to MySQL
$conn = mysqli_connect($host, $user, $pass, $db);

// If connection failed...
if (!$conn) {

    // Show error message
    echo '<p style="color:red; text-align:center; margin-top:50px;">Database connection failed</p>';

    // Stop script
    exit;
}

// Store logged-in username in a simpler variable
$currentUser = $_SESSION["userNameSession"];

// Variable to store error messages
$error = "";

// Variable to store success messages
$success = "";

// Will later store search results
$searchResults = null;

// -------------------------------------------------------------
// Check if friend_requests table exists, create if not
// -------------------------------------------------------------

// SQL that checks if table exists
$check_table = "SHOW TABLES LIKE 'friend_requests'";

// Run the query
$table_result = mysqli_query($conn, $check_table);

// If zero rows returned → table does NOT exist
if (mysqli_num_rows($table_result) == 0) {

    // SQL to create the table
    $create_table = "CREATE TABLE friend_requests (
        pk_request_id INT AUTO_INCREMENT PRIMARY KEY,
        fk_user_from VARCHAR(50) NOT NULL,
        fk_user_to VARCHAR(50) NOT NULL,
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
        FOREIGN KEY (fk_user_from) REFERENCES user(pk_username) ON DELETE CASCADE,
        FOREIGN KEY (fk_user_to) REFERENCES user(pk_username) ON DELETE CASCADE,
        UNIQUE KEY unique_request (fk_user_from, fk_user_to)
    )";

    // Actually run the CREATE TABLE command
    mysqli_query($conn, $create_table);
}

// -------------------------------------------------------------
// Handle form submissions
// -------------------------------------------------------------

// Only run this block if the page was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Read which button was pressed (or empty if not pressed)
    $backToHome = $_POST["backToHome"] ?? '';
    $searchFriend = $_POST["searchFriend"] ?? '';
    $sendRequest = $_POST["sendRequest"] ?? '';
    $acceptRequest = $_POST["acceptRequest"] ?? '';
    $declineRequest = $_POST["declineRequest"] ?? '';
    $removeFriend = $_POST["removeFriend"] ?? '';
    
    // ---------------- Back to homepage ----------------
    // If back button was pressed...
    if (!empty($backToHome)) {

        // Redirect to homepage
        header("Location: HomePage.php");

        // Stop script
        exit;
    }
    
    // ---------------- Search for friend ----------------
    if (!empty($searchFriend)) {

        // Get what user typed and remove spaces
        $searchUsername = trim($_POST["searchUsername"]);
        
        if (!empty($searchUsername)) {

            // Add % for partial matching
            $searchTerm = "%" . $searchUsername . "%";

            // SQL to search users
            $search_sql = "SELECT pk_username, firstName, lastName, email 
                           FROM user 
                           WHERE (pk_username LIKE ? OR firstName LIKE ? OR lastName LIKE ?) 
                           AND pk_username != ? 
                           LIMIT 10";

            // Prepare safe statement
            $search_stmt = mysqli_prepare($conn, $search_sql);

            // Bind variables to the ?
            mysqli_stmt_bind_param($search_stmt, "ssss", $searchTerm, $searchTerm, $searchTerm, $currentUser);

            // Execute query
            mysqli_stmt_execute($search_stmt);

            // Store results
            $searchResults = mysqli_stmt_get_result($search_stmt);
        }
    }
    
    // ---------------- Send friend request ----------------
    if (!empty($sendRequest)) {

        // Get target username
        $targetUsername = trim($_POST["targetUsername"]);
        
        if (empty($targetUsername)) {
            $error = "Error! Please enter a username.";
        } elseif ($targetUsername === $currentUser) {
            $error = "Error! You cannot send a friend request to yourself.";
        } else {

            // Check if user exists
            $check_sql = "SELECT pk_username FROM user WHERE pk_username = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $targetUsername);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) == 0) {
                $error = "Error! User '$targetUsername' does not exist.";
            } else {

                // Check if already friends
                $friend_check = "SELECT * FROM isfriend WHERE 
                    (pkfk_user_user = ? AND pkfk_user_friend = ?) OR 
                    (pkfk_user_user = ? AND pkfk_user_friend = ?)";
                $friend_stmt = mysqli_prepare($conn, $friend_check);
                mysqli_stmt_bind_param($friend_stmt, "ssss", $currentUser, $targetUsername, $targetUsername, $currentUser);
                mysqli_stmt_execute($friend_stmt);
                $friend_result = mysqli_stmt_get_result($friend_stmt);
                
                if (mysqli_num_rows($friend_result) > 0) {
                    $error = "Error! You are already friends with '$targetUsername'.";
                } else {

                    // Check if request already sent
                    $request_check = "SELECT * FROM friend_requests WHERE 
                        fk_user_from = ? AND fk_user_to = ? AND status = 'pending'";
                    $request_stmt = mysqli_prepare($conn, $request_check);
                    mysqli_stmt_bind_param($request_stmt, "ss", $currentUser, $targetUsername);
                    mysqli_stmt_execute($request_stmt);
                    $request_result = mysqli_stmt_get_result($request_stmt);
                    
                    if (mysqli_num_rows($request_result) > 0) {
                        $error = "Error! You have already sent a friend request to '$targetUsername'.";
                    } else {

                        // Check if they already sent YOU a request
                        $their_request = "SELECT * FROM friend_requests WHERE 
                            fk_user_from = ? AND fk_user_to = ? AND status = 'pending'";
                        $their_stmt = mysqli_prepare($conn, $their_request);
                        mysqli_stmt_bind_param($their_stmt, "ss", $targetUsername, $currentUser);
                        mysqli_stmt_execute($their_stmt);
                        $their_result = mysqli_stmt_get_result($their_stmt);
                        
                        if (mysqli_num_rows($their_result) > 0) {
                            $error = "Error! '$targetUsername' has already sent you a friend request. Check your requests!";
                        } else {

                            // Insert new friend request
                            $insert_sql = "INSERT INTO friend_requests (fk_user_from, fk_user_to, status) VALUES (?, ?, 'pending')";
                            $insert_stmt = mysqli_prepare($conn, $insert_sql);
                            mysqli_stmt_bind_param($insert_stmt, "ss", $currentUser, $targetUsername);
                            
                            if (mysqli_stmt_execute($insert_stmt)) {
                                $success = "Friend request sent to '$targetUsername'!";
                            } else {
                                $error = "Error! Could not send friend request: " . mysqli_error($conn);
                            }
                        }
                    }
                }
            }
        }
    }
    
    // ---------------- Accept friend request ----------------
    if (!empty($acceptRequest)) {

        $requestFrom = $_POST["requestFrom"];
        
        // Mark request as accepted
        $update_sql = "UPDATE friend_requests SET status = 'accepted' 
                       WHERE fk_user_from = ? AND fk_user_to = ? AND status = 'pending'";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ss", $requestFrom, $currentUser);
        
        if (mysqli_stmt_execute($update_stmt)) {

            // Add friendship (both directions)
            $add_sql1 = "INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (?, ?)";
            $add_stmt1 = mysqli_prepare($conn, $add_sql1);
            mysqli_stmt_bind_param($add_stmt1, "ss", $currentUser, $requestFrom);
            
            $add_sql2 = "INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (?, ?)";
            $add_stmt2 = mysqli_prepare($conn, $add_sql2);
            mysqli_stmt_bind_param($add_stmt2, "ss", $requestFrom, $currentUser);
            
            if (mysqli_stmt_execute($add_stmt1) && mysqli_stmt_execute($add_stmt2)) {
                $success = "Friend request accepted! You are now friends with '$requestFrom'.";
            } else {
                $error = "Error! Could not add friend: " . mysqli_error($conn);
            }
        } else {
            $error = "Error! Could not accept request: " . mysqli_error($conn);
        }
    }
    
    // ---------------- Decline friend request ----------------
    if (!empty($declineRequest)) {

        $requestFrom = $_POST["requestFrom"];
        
        $decline_sql = "UPDATE friend_requests SET status = 'declined' 
                        WHERE fk_user_from = ? AND fk_user_to = ? AND status = 'pending'";
        $decline_stmt = mysqli_prepare($conn, $decline_sql);
        mysqli_stmt_bind_param($decline_stmt, "ss", $requestFrom, $currentUser);
        
        if (mysqli_stmt_execute($decline_stmt)) {
            $success = "Friend request from '$requestFrom' declined.";
        } else {
            $error = "Error! Could not decline request: " . mysqli_error($conn);
        }
    }
    
    // ---------------- Remove friend ----------------
    if (!empty($removeFriend)) {

        $friendUsername = $_POST["friendUsername"];
        
        // Delete both friendship rows
        $remove_sql = "DELETE FROM isfriend WHERE 
            (pkfk_user_user = ? AND pkfk_user_friend = ?) OR 
            (pkfk_user_user = ? AND pkfk_user_friend = ?)";
        $remove_stmt = mysqli_prepare($conn, $remove_sql);
        mysqli_stmt_bind_param($remove_stmt, "ssss", $currentUser, $friendUsername, $friendUsername, $currentUser);
        
        if (mysqli_stmt_execute($remove_stmt)) {
            $success = "Friend '$friendUsername' removed successfully!";
        } else {
            $error = "Error! Could not remove friend: " . mysqli_error($conn);
        }
    }
}

// -------------------------------------------------------------
// Get current friends
// -------------------------------------------------------------

$friends_sql = "SELECT u.pk_username, u.firstName, u.lastName, u.email 
                FROM user u 
                JOIN isfriend f ON (u.pk_username = f.pkfk_user_friend OR u.pk_username = f.pkfk_user_user)
                WHERE (f.pkfk_user_user = ? OR f.pkfk_user_friend = ?) 
                AND u.pk_username != ?
                GROUP BY u.pk_username
                ORDER BY u.firstName, u.lastName";

$friends_stmt = mysqli_prepare($conn, $friends_sql);
mysqli_stmt_bind_param($friends_stmt, "sss", $currentUser, $currentUser, $currentUser);
mysqli_stmt_execute($friends_stmt);
$friends_result = mysqli_stmt_get_result($friends_stmt);

// -------------------------------------------------------------
// Get pending friend requests sent TO current user
// -------------------------------------------------------------

$requests_sql = "SELECT r.fk_user_from, r.request_date, 
                        u.firstName, u.lastName, u.pk_username
                 FROM friend_requests r
                 JOIN user u ON r.fk_user_from = u.pk_username
                 WHERE r.fk_user_to = ? 
                 AND r.status = 'pending'
                 ORDER BY r.request_date DESC";

$requests_stmt = mysqli_prepare($conn, $requests_sql);
mysqli_stmt_bind_param($requests_stmt, "s", $currentUser);
mysqli_stmt_execute($requests_stmt);
$requests_result = mysqli_stmt_get_result($requests_stmt);

// -------------------------------------------------------------
// Get pending requests sent BY current user (for info)
// -------------------------------------------------------------

$sent_requests_sql = "SELECT COUNT(*) as sent_count 
                      FROM friend_requests 
                      WHERE fk_user_from = ? 
                      AND status = 'pending'";

$sent_stmt = mysqli_prepare($conn, $sent_requests_sql);
mysqli_stmt_bind_param($sent_stmt, "s", $currentUser);
mysqli_stmt_execute($sent_stmt);
$sent_result = mysqli_stmt_get_result($sent_stmt);
$sent_data = mysqli_fetch_assoc($sent_result);
$sent_count = $sent_data['sent_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIF - Friends Management</title>
    <link rel="stylesheet" href="friends_customization.css">
</head>
<body>
    <div class="container">
        <!-- Back button -->
        <form method="POST" action="" class="back-form">
            <input type="submit" class="back-btn" value="← Back to Home" name="backToHome">
        </form>
        
        <h1>Friends Management</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Search for Friends -->
        <div class="section search-section">
            <h2>Search for Friends</h2>
            <?php if ($sent_count > 0): ?>
                <div class="info-note">
                    <p>You have <?php echo $sent_count; ?> pending friend request(s) sent.</p>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="search-box">
                    <input type="text" name="searchUsername" placeholder="Enter username, first name, or last name" 
                           value="<?php echo isset($_POST['searchUsername']) ? htmlspecialchars($_POST['searchUsername']) : ''; ?>">
                    <input type="submit" value="Search" name="searchFriend">
                </div>
            </form>
            
            <?php if ($searchResults && mysqli_num_rows($searchResults) > 0): ?>
                <div class="search-results">
                    <h3>Search Results:</h3>
                    <?php while ($user = mysqli_fetch_assoc($searchResults)): 
                        // Check if already friends
                        $is_friend_sql = "SELECT * FROM isfriend WHERE 
                            (pkfk_user_user = ? AND pkfk_user_friend = ?) OR 
                            (pkfk_user_user = ? AND pkfk_user_friend = ?)";
                        $is_friend_stmt = mysqli_prepare($conn, $is_friend_sql);
                        mysqli_stmt_bind_param($is_friend_stmt, "ssss", $currentUser, $user['pk_username'], $user['pk_username'], $currentUser);
                        mysqli_stmt_execute($is_friend_stmt);
                        $is_friend_result = mysqli_stmt_get_result($is_friend_stmt);
                        $is_friend = mysqli_num_rows($is_friend_result) > 0;
                        
                        // Check if request already sent
                        $request_sent_sql = "SELECT * FROM friend_requests WHERE 
                            fk_user_from = ? AND fk_user_to = ? AND status = 'pending'";
                        $request_sent_stmt = mysqli_prepare($conn, $request_sent_sql);
                        mysqli_stmt_bind_param($request_sent_stmt, "ss", $currentUser, $user['pk_username']);
                        mysqli_stmt_execute($request_sent_stmt);
                        $request_sent_result = mysqli_stmt_get_result($request_sent_stmt);
                        $request_sent = mysqli_num_rows($request_sent_result) > 0;
                    ?>
                        <div class="user-card">
                            <div class="user-info">
                                <strong><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></strong>
                                <span>@<?php echo htmlspecialchars($user['pk_username']); ?></span>
                                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="user-actions">
                                <?php if ($is_friend): ?>
                                    <span class="already-friends">Already Friends</span>
                                <?php elseif ($request_sent): ?>
                                    <span class="request-sent">Request Sent</span>
                                <?php else: ?>
                                    <form method="POST" action="" class="inline-form">
                                        <input type="hidden" name="targetUsername" value="<?php echo $user['pk_username']; ?>">
                                        <input type="submit" value="Send Request" name="sendRequest" class="btn-send">
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php elseif (isset($_POST['searchFriend'])): ?>
                <div class="no-results">
                    <p>No users found matching your search.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Two-column layout -->
        <div class="two-column">
            <!-- Left Column: Current Friends -->
            <div class="column">
                <div class="section">
                    <h2>Current Friends (<?php echo mysqli_num_rows($friends_result); ?>)</h2>
                    
                    <?php if (mysqli_num_rows($friends_result) > 0): ?>
                        <div class="friends-list">
                            <?php 
                            mysqli_data_seek($friends_result, 0);
                            while ($friend = mysqli_fetch_assoc($friends_result)): 
                            ?>
                                <div class="friend-card">
                                    <div class="friend-info">
                                        <div class="friend-name">
                                            <strong><?php echo htmlspecialchars($friend['firstName'] . ' ' . $friend['lastName']); ?></strong>
                                            <span>@<?php echo htmlspecialchars($friend['pk_username']); ?></span>
                                        </div>
                                        <div class="friend-email"><?php echo htmlspecialchars($friend['email']); ?></div>
                                    </div>
                                    <div class="friend-actions">
                                        <form method="POST" action="" class="inline-form" 
                                              onsubmit="return confirm('Remove <?php echo htmlspecialchars($friend['firstName']); ?> from friends?');">
                                            <input type="hidden" name="friendUsername" value="<?php echo $friend['pk_username']; ?>">
                                            <input type="submit" value="Remove" name="removeFriend" class="btn-remove">
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>You don't have any friends yet. Search for friends above!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column: Friend Requests -->
            <div class="column">
                <div class="section">
                    <h2>Friend Requests (<?php echo mysqli_num_rows($requests_result); ?>)</h2>
                    
                    <?php if (mysqli_num_rows($requests_result) > 0): ?>
                        <div class="requests-list">
                            <?php 
                            mysqli_data_seek($requests_result, 0);
                            while ($request = mysqli_fetch_assoc($requests_result)): 
                                $requestDate = date('Y-m-d H:i', strtotime($request['request_date']));
                            ?>
                                <div class="request-card">
                                    <div class="request-info">
                                        <div class="request-name">
                                            <strong><?php echo htmlspecialchars($request['firstName'] . ' ' . $request['lastName']); ?></strong>
                                            <span>@<?php echo htmlspecialchars($request['pk_username']); ?></span>
                                        </div>
                                        <div class="request-meta">
                                            <span class="request-date">Sent: <?php echo $requestDate; ?></span>
                                            <div class="request-status">Wants to be friends with you</div>
                                        </div>
                                    </div>
                                    <div class="request-actions">
                                        <form method="POST" action="" class="inline-form">
                                            <input type="hidden" name="requestFrom" value="<?php echo $request['fk_user_from']; ?>">
                                            <input type="submit" value="Accept" name="acceptRequest" class="btn-accept">
                                            <input type="submit" value="Decline" name="declineRequest" class="btn-decline">
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>No pending friend requests.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
