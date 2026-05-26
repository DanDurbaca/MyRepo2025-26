<?php
// ------------------------------------------------------------
// START OF PHP BACKEND LOGIC
// ------------------------------------------------------------

// Start or resume the user's session
session_start();

// If the user is not logged in, send them back to the login page
if (empty($_SESSION["userNameSession"])) {
    header("Location: Log-in.php");
    exit; // Stop the script so nothing else runs
}

// ---------- DATABASE CONNECTION DETAILS ----------
$host = "localhost";                 // Server where MySQL is running
$db = "portableindoorfeedback";      // Name of your database
$user = "root";                      // MySQL username
$pass = "";                          // MySQL password (empty here)

// Create the actual connection to MySQL
$conn = mysqli_connect($host, $user, $pass, $db);

// ---------- DEFAULT VARIABLES ----------
$userRole = 'User';      // Assume normal user unless we prove otherwise
$latestMeasurements = []; // Will store last 5 measurements
$stationCount = 0;        // Will store number of stations
$collectionCount = 0;     // Will store number of collections
$friendCount = 0;         // Will store number of friends

// Only run database queries if the connection worked
if ($conn) {

    // Get the currently logged-in username from the session
    $currentUser = $_SESSION["userNameSession"];
    
    // ------------------------------------------------------------
    // 1) GET USER ROLE (User or Admin)
    // ------------------------------------------------------------

    // SQL query to find the user's role
    $role_sql = "SELECT role FROM user WHERE pk_username = ?";

    // Prepare the statement (prevents SQL injection)
    $role_stmt = mysqli_prepare($conn, $role_sql);

    // Bind the username to the ? in the query
    mysqli_stmt_bind_param($role_stmt, "s", $currentUser);

    // Execute the query
    mysqli_stmt_execute($role_stmt);

    // Get the result from MySQL
    $role_result = mysqli_stmt_get_result($role_stmt);

    // If a row exists, fetch the role
    if ($role_result && mysqli_num_rows($role_result) > 0) {
        $user_data = mysqli_fetch_assoc($role_result);
        $userRole = $user_data['role']; // Overwrite default 'User'
    }
    
    // ------------------------------------------------------------
    // 2) GET LAST 5 MEASUREMENTS FROM USER'S STATIONS
    // ------------------------------------------------------------

    $measurements_sql = "SELECT m.*, s.name as station_name 
                         FROM measurement m
                         JOIN station s ON m.fk_station_records = s.pk_serialNumber
                         WHERE s.fk_user_owns = ?
                         ORDER BY m.timestamp DESC
                         LIMIT 5";

    // Prepare statement
    $measurements_stmt = mysqli_prepare($conn, $measurements_sql);

    // Bind username
    mysqli_stmt_bind_param($measurements_stmt, "s", $currentUser);

    // Execute
    mysqli_stmt_execute($measurements_stmt);

    // Get result set
    $measurements_result = mysqli_stmt_get_result($measurements_stmt);
    
    // Loop through all rows and store them in the array
    while ($row = mysqli_fetch_assoc($measurements_result)) {
        $latestMeasurements[] = $row;
    }
    
    // ------------------------------------------------------------
    // 3) COUNT HOW MANY STATIONS THE USER OWNS
    // ------------------------------------------------------------

    $station_sql = "SELECT COUNT(*) as count FROM station WHERE fk_user_owns = ?";

    $station_stmt = mysqli_prepare($conn, $station_sql);

    mysqli_stmt_bind_param($station_stmt, "s", $currentUser);

    mysqli_stmt_execute($station_stmt);

    $station_result = mysqli_stmt_get_result($station_stmt);

    $station_data = mysqli_fetch_assoc($station_result);

    // Store the number
    $stationCount = $station_data['count'];
    
    // ------------------------------------------------------------
    // 4) COUNT HOW MANY COLLECTIONS THE USER CREATED
    // ------------------------------------------------------------

    $collection_sql = "SELECT COUNT(*) as count FROM collection WHERE fk_user_creates = ?";

    $collection_stmt = mysqli_prepare($conn, $collection_sql);

    mysqli_stmt_bind_param($collection_stmt, "s", $currentUser);

    mysqli_stmt_execute($collection_stmt);

    $collection_result = mysqli_stmt_get_result($collection_stmt);

    $collection_data = mysqli_fetch_assoc($collection_result);

    $collectionCount = $collection_data['count'];
    
    // ------------------------------------------------------------
    // 5) COUNT HOW MANY FRIENDS THE USER HAS
    // ------------------------------------------------------------

    $friend_sql = "SELECT COUNT(*) as count FROM isfriend WHERE pkfk_user_user = ?";

    $friend_stmt = mysqli_prepare($conn, $friend_sql);

    mysqli_stmt_bind_param($friend_stmt, "s", $currentUser);

    mysqli_stmt_execute($friend_stmt);

    $friend_result = mysqli_stmt_get_result($friend_stmt);

    $friend_data = mysqli_fetch_assoc($friend_result);

    $friendCount = $friend_data['count'];
}

// ------------------------------------------------------------
// HANDLE BUTTON CLICKS (FORM SUBMISSION)
// ------------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // If Log-out button was pressed
    if (isset($_POST["logOutBtn"]) && $_POST["logOutBtn"] === "Log-out") {
        session_unset(); // Clear session data
        header("location: Log-in.php");
        exit;
    }

    // If Profile button was pressed
    if (isset($_POST["userProfile"]) && $_POST["userProfile"] === "Profile") {
        header("location: userProfilePage.php");
        exit;
    }

    // If Station Management button was pressed
    if (isset($_POST["stationManagement"]) && $_POST["stationManagement"] === "Station Management") {
        header("location: stationManagementPage.php");
        exit;
    }

    // If Friends button was pressed
    if (isset($_POST["friendsBtn"]) && $_POST["friendsBtn"] === "Friends") {
        header("location: friendsPage.php");
        exit;
    }

    // If Collections button was pressed
    if (isset($_POST["collectionsBtn"]) && $_POST["collectionsBtn"] === "Collections") {
        header("location: collectionsPage.php");
        exit;
    }

    // If Create Stations button was pressed (Admin only)
    if (isset($_POST["stationCreation"]) && $_POST["stationCreation"] === "Create Stations") {
        header("location: stationCreator.php");
        exit;
    }

    // If Admin Panel button was pressed
    if (isset($_POST["adminPanel"]) && $_POST["adminPanel"] === "Admin Panel") {
        header("location: adminPanel.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PIF - Home</title>
<link rel="stylesheet" href="homepage customization.css">

<!-- Everything below here is FRONT-END (HTML + CSS) -->
<style>
    /* Dashboard Styles */
    .dashboard {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
    }
    
    .welcome-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0de3bff;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #212a44ff;
        margin-bottom: 10px;
    }
    
    .stat-label {
        color: #666;
        font-size: 1rem;
    }
    
    .latest-measurements {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-top: 20px;
    }
    
    .measurements-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    
    .measurements-table th,
    .measurements-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .measurements-table th {
        background: #f8f9fa;
        color: #212a44ff;
        font-weight: bold;
    }
    
    .measurements-table tr:hover {
        background: #f8f9fa;
    }
    
    .no-data {
        text-align: center;
        padding: 30px;
        color: #666;
        font-style: italic;
    }
    
    .dashboard-section-title {
        color: #212a44ff;
        margin-bottom: 20px;
        font-size: 1.5rem;
        border-left: 4px solid #f0de3bff;
        padding-left: 15px;
    }
    
    /* Quick Actions */
    .quick-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin: 20px 0;
        justify-content: center;
    }
    
    .action-btn {
        background: #f0de3bff;
        color: #212a44ff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .action-btn:hover {
        background: #e6d135;
    }
</style>
</head>
<body>

<!-- Everything is inside one POST form -->
<form method="POST">
    <div class="dashboard">

        <!-- Welcome Header -->
        <div class="welcome-header">
            <h1>Welcome <?php echo htmlspecialchars($_SESSION["userNameSession"]); ?>!</h1>
            <p style="color: #666; margin-top: 10px;">
                <?php echo date('l, F j, Y'); ?> • 
                Role: <strong><?php echo htmlspecialchars($userRole); ?></strong>
            </p>
        </div>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-number"><?php echo $stationCount; ?></div>
                <div class="stat-label">Stations</div>
            </div>

            <div class="stat-card">
                <div class="stat-number"><?php echo $collectionCount; ?></div>
                <div class="stat-label">Collections</div>
            </div>

            <div class="stat-card">
                <div class="stat-number"><?php echo $friendCount; ?></div>
                <div class="stat-label">Friends</div>
            </div>

            <div class="stat-card">
                <div class="stat-number"><?php echo count($latestMeasurements); ?></div>
                <div class="stat-label">Latest Measurements</div>
            </div>

        </div>
        
        <!-- Latest Measurements Section -->
        <div class="latest-measurements">
            <h2 class="dashboard-section-title">Latest Measurements</h2>
            
            <?php if (!empty($latestMeasurements)): ?>
                <table class="measurements-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Station</th>
                            <th>Temperature</th>
                            <th>Humidity</th>
                            <th>Light</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($latestMeasurements as $measurement): ?>
                            <tr>
                                <td><?php echo date('H:i', strtotime($measurement['timestamp'])); ?></td>
                                <td><?php echo htmlspecialchars($measurement['station_name']); ?></td>
                                <td><?php echo htmlspecialchars($measurement['temperature']); ?>°C</td>
                                <td><?php echo htmlspecialchars($measurement['humidity']); ?>%</td>
                                <td><?php echo htmlspecialchars($measurement['light']); ?> lx</td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No measurements found. Add stations to start collecting data!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Navigation Buttons -->
        <div style="text-align: center; margin-top: 40px;">
            <h2 class="dashboard-section-title">Quick Actions</h2>
            <div class="quick-actions">

                <input type="submit" value="Log-out" name="logOutBtn" class="action-btn">
                <input type="submit" value="Profile" name="userProfile" class="action-btn">
                
                <?php if ($userRole === 'Admin'): ?>
                    <input type="submit" value="Create Stations" name="stationCreation" class="action-btn">
                    <input type="submit" value="Admin Panel" name="adminPanel" class="action-btn">
                    <input type="submit" value="Collections" name="collectionsBtn" class="action-btn">
                <?php else: ?>
                    <input type="submit" value="Station Management" name="stationManagement" class="action-btn">
                    <input type="submit" value="Friends" name="friendsBtn" class="action-btn">
                    <input type="submit" value="Collections" name="collectionsBtn" class="action-btn">
                <?php endif; ?>

            </div>
        </div>

    </div>
</form>

</body>
</html>
