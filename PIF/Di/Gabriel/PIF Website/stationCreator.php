<?php
session_start();

// Check if user is logged in
if (empty($_SESSION["userNameSession"])) {
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
    exit;
}

// Database connection
$host = "localhost";
$db = "portableindoorfeedback";
$user = "root";
$pass = "";
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("<p style='color:red'>Database connection failed</p>");
}

$currentUser = $_SESSION["userNameSession"];

// Check if user is admin
$role_sql = "SELECT role FROM user WHERE pk_username = ?";
$role_stmt = mysqli_prepare($conn, $role_sql);
mysqli_stmt_bind_param($role_stmt, "s", $currentUser);
mysqli_stmt_execute($role_stmt);
$role_result = mysqli_stmt_get_result($role_stmt);
$user_data = mysqli_fetch_assoc($role_result);

if ($user_data['role'] !== 'Admin') {
    header("Location: HomePage.php");
    exit;
}

$error = "";
$success = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $backToHome = $_POST["backToHome"] ?? '';
    $createStation = $_POST["createStation"] ?? '';
    
    // Back to homepage
    if (!empty($backToHome)) {
        header("Location: HomePage.php");
        exit;
    }
    
    // Create new station
    if (!empty($createStation)) {
        $serialNumber = trim($_POST["serialNumber"]);
        $stationName = trim($_POST["stationName"]);
        $description = trim($_POST["description"]);
        
        if (empty($serialNumber) || empty($stationName)) {
            $error = "Error! Serial number and station name are required.";
        } else {
            // Check if station exists
            $check_sql = "SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $serialNumber);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error = "Error! Station with this serial number already exists.";
            } else {
                // Create new unassigned station
                $insert_sql = "INSERT INTO station (pk_serialNumber, name, description) VALUES (?, ?, ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, "sss", $serialNumber, $stationName, $description);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    $success = "New station created successfully!";
                } else {
                    $error = "Error! Could not create station: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Get all stations
$stations_sql = "SELECT s.pk_serialNumber, s.name, s.description, u.pk_username as owner 
                FROM station s 
                LEFT JOIN user u ON s.fk_user_owns = u.pk_username 
                ORDER BY s.name";
$stations_result = mysqli_query($conn, $stations_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIF - Create Stations</title>
    <link rel="stylesheet" href="station creator customization.css">
    <style>
        .station-measurements {
            margin: 10px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .measurement-count {
            color: #28a745;
            font-weight: bold;
        }
        
        .measurement-count-empty {
            color: #6c757d;
            font-style: italic;
        }
        
        .view-data-link {
            color: #007bff;
            text-decoration: none;
            margin-left: 10px;
            font-weight: bold;
        }
        
        .view-data-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back button form -->
        <form method="POST" action="">
            <input type="submit" class="back-btn" value="← Back to Home" name="backToHome">
        </form>
        
        <h1>Create Stations</h1>
        
<a href="stationManagementPage.php" class="back-btn">manage stations</a>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Create Station Section -->
        <div class="section">
            <h2>Create New Station</h2>
            <form method="POST" action="">
                <h3>Serial Number:</h3>
                <input type="text" name="serialNumber" placeholder="Enter station serial number" required>
                
                <h3>Station Name:</h3>
                <input type="text" name="stationName" placeholder="Enter station name" required>
                
                <h3>Description (Optional):</h3>
                <textarea name="description" placeholder="Enter station description" rows="3"></textarea>
                
                <input type="submit" value="Create Station" name="createStation">
            </form>
        </div>
        
        <!-- View All Stations Section -->
        <div class="section">
            <h2>All Stations</h2>
            
            <?php
            // Get all stations
            $stations_sql = "SELECT s.pk_serialNumber, s.name, s.description, u.pk_username as owner 
                            FROM station s 
                            LEFT JOIN user u ON s.fk_user_owns = u.pk_username 
                            ORDER BY s.name";
            $stations_result = mysqli_query($conn, $stations_sql);
            ?>
            
            <div class="station-list">
                <?php if (mysqli_num_rows($stations_result) > 0): ?>
                    <?php while ($station = mysqli_fetch_assoc($stations_result)): 
                        $isAssigned = !empty($station['owner']);
                        
                        // Count measurements for this station
                        $count_sql = "SELECT COUNT(*) as measurement_count FROM measurement WHERE fk_station_records = ?";
                        $count_stmt = mysqli_prepare($conn, $count_sql);
                        mysqli_stmt_bind_param($count_stmt, "s", $station['pk_serialNumber']);
                        mysqli_stmt_execute($count_stmt);
                        $count_result = mysqli_stmt_get_result($count_stmt);
                        $measurement_data = mysqli_fetch_assoc($count_result);
                        $measurement_count = $measurement_data['measurement_count'];
                    ?>
                        <div class="station-item <?php echo $isAssigned ? 'assigned' : 'unassigned'; ?>">
                            <div class="station-header">
                                <h4><?php echo htmlspecialchars($station['name']); ?></h4>
                                <span class="station-id">ID: <?php echo htmlspecialchars($station['pk_serialNumber']); ?></span>
                            </div>
                            
                            <?php if (!empty($station['description'])): ?>
                                <p><?php echo htmlspecialchars($station['description']); ?></p>
                            <?php endif; ?>
                            
                            <!-- MEASUREMENT COUNT DISPLAY -->
                            <div class="station-measurements">
                                <?php if ($measurement_count > 0): ?>
                                    <span class="measurement-count"> <?php echo $measurement_count; ?> measurement(s)</span>
                                    <a href="stationMeasurementsPage.php?station=<?php echo $station['pk_serialNumber']; ?>" class="view-data-link">View Data</a>
                                <?php else: ?>
                                    <span class="measurement-count-empty"> No measurements yet</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="station-owner">
                                <?php if ($isAssigned): ?>
                                    <span class="owner-label">Owner: </span>
                                    <span class="owner-name"><?php echo htmlspecialchars($station['owner']); ?></span>
                                <?php else: ?>
                                    <span class="unassigned-label">Unassigned - Users can claim this station</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No stations created yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>