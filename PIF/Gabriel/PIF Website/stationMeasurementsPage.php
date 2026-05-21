<?php
// Start a session to track user login state across pages
session_start();

// Check if user is logged in - if not, redirect to login page
if (empty($_SESSION["userNameSession"])) {
    header("Location: Log-in.php");
    exit; // Stop script execution after redirect
}

// Database connection configuration
$host = "localhost";     // MySQL server address (usually localhost for local development)
$db = "portableindoorfeedback";  // Database name
$user = "root";          // Database username (default for XAMPP/WAMP)
$pass = "";              // Database password (empty for local development)
$conn = mysqli_connect($host, $user, $pass, $db);

// Check if connection was successful
if (!$conn) {
    die("Database connection failed"); // Stop script if connection fails
}

// Get current logged-in user from session
$currentUser = $_SESSION["userNameSession"];
// Get station serial number from URL parameter (e.g., ?station=SN-123)
$stationSerial = $_GET['station'] ?? ''; // ?? '' means use empty string if not set

// If no station was selected, show error message
if (empty($stationSerial)) {
    echo "No station selected";
    exit;
}

/* ---------- STATION OWNERSHIP CHECK ---------- */
// Check if current user owns this station
$check_ownership = "SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ? AND fk_user_owns = ?";
// Prepare statement to prevent SQL injection
$check_stmt = mysqli_prepare($conn, $check_ownership);
// Bind parameters: s=string, first ? = $stationSerial, second ? = $currentUser
mysqli_stmt_bind_param($check_stmt, "ss", $stationSerial, $currentUser);
mysqli_stmt_execute($check_stmt);
$ownership_result = mysqli_stmt_get_result($check_stmt);

/* ---------- ADMIN CHECK ---------- */
// Check if current user is an administrator
$check_admin = "SELECT role FROM user WHERE pk_username = ?";
$admin_stmt = mysqli_prepare($conn, $check_admin);
mysqli_stmt_bind_param($admin_stmt, "s", $currentUser);
mysqli_stmt_execute($admin_stmt);
$admin_result = mysqli_stmt_get_result($admin_stmt);
$admin_data = mysqli_fetch_assoc($admin_result); // Get result as associative array
$isAdmin = ($admin_data['role'] === 'Admin'); // Check if role is exactly 'Admin'

/* ---------- ACCESS CONTROL ---------- */
// Deny access if: user doesn't own station AND is not an admin
if (mysqli_num_rows($ownership_result) === 0 && !$isAdmin) {
    echo "Access denied. You don't own this station.";
    exit;
}

/* ---------- DATE FILTERING (NO LIMITS) ---------- */
// Get date filters from URL parameters (e.g., ?start_date=2024-01-01&end_date=2024-01-31)
$startDate = $_GET['start_date'] ?? ''; // Start date filter
$endDate = $_GET['end_date'] ?? '';     // End date filter
$dateError = ''; // Variable to store date validation errors

// Validate: end date cannot be before start date
if (!empty($startDate) && !empty($endDate) && $endDate < $startDate) {
    $dateError = 'End date must be after or equal to start date.';
    $endDate = ''; // Clear invalid end date
}

/* ---------- BUILD SQL QUERY WITH FILTERS ---------- */
$whereClause = "fk_station_records = ?"; // Base condition: station serial number
$params = [$stationSerial];              // Array of parameters for prepared statement
$paramTypes = "s";                       // Parameter types: s=string

// Add start date filter if provided and no errors
if (!empty($startDate) && empty($dateError)) {
    $whereClause .= " AND timestamp >= ?";      // Add AND condition for start date
    $params[] = $startDate . " 00:00:00";       // Add parameter with time set to beginning of day
    $paramTypes .= "s";                         // Add another string type
}

// Add end date filter if provided and no errors
if (!empty($endDate) && empty($dateError)) {
    $whereClause .= " AND timestamp <= ?";      // Add AND condition for end date
    $params[] = $endDate . " 23:59:59";         // Add parameter with time set to end of day
    $paramTypes .= "s";                         // Add another string type
}

/* ---------- EXECUTE MEASUREMENTS QUERY ---------- */
$measurements_sql = "SELECT * FROM measurement WHERE $whereClause ORDER BY timestamp DESC";
$measurements_stmt = mysqli_prepare($conn, $measurements_sql);

// Dynamically bind parameters based on how many filters were added
if (count($params) > 1) {
    // If we have filters, bind all parameters dynamically
    mysqli_stmt_bind_param($measurements_stmt, $paramTypes, ...$params); // ... spreads array
} else {
    // If no filters, just bind station serial
    mysqli_stmt_bind_param($measurements_stmt, "s", $stationSerial);
}

// Execute query and get results
mysqli_stmt_execute($measurements_stmt);
$measurements_result = mysqli_stmt_get_result($measurements_stmt);

/* ---------- GET STATION INFO FOR DISPLAY ---------- */
$station_sql = "SELECT name, description FROM station WHERE pk_serialNumber = ?";
$station_stmt = mysqli_prepare($conn, $station_sql);
mysqli_stmt_bind_param($station_stmt, "s", $stationSerial);
mysqli_stmt_execute($station_stmt);
$station_result = mysqli_stmt_get_result($station_stmt);
$station_data = mysqli_fetch_assoc($station_result); // Get station name and description
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Station Measurements - PIF System</title>
<link rel="stylesheet" href="station_measurements.css">

<style>
/* Filter form styling */
.filter-form {
    background: #f8f9fa; /* Light gray background */
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    border: 1px solid #dee2e6; /* Light border */
}

.filter-row {
    display: flex;           /* Flexbox for horizontal layout */
    gap: 15px;               /* Space between columns */
    margin-bottom: 15px;
    align-items: flex-end;   /* Align items at bottom */
}

.filter-group { 
    flex: 1; /* Each group takes equal width */
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #495057; /* Dark gray */
}

.filter-group input[type="date"] {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da; /* Bootstrap-like border */
    border-radius: 4px;
    font-size: 14px;
}

/* Filter buttons */
.filter-btn {
    padding: 8px 20px;
    background: #007bff; /* Bootstrap primary blue */
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.reset-btn {
    padding: 8px 20px;
    background: #6c757d; /* Bootstrap secondary gray */
    color: white;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block; /* Make link behave like button */
}
</style>
</head>

<body>
<div class="container">

<!-- Navigation: Back to stations list -->
<a href="stationManagementPage.php" class="back-btn">manage stations</a>
<a href="stationCreator.php" class="back-btn">← Back to Stations</a>

<h1>Station Measurements</h1>

<!-- Station information display -->
<div class="station-info">
<h2><?php echo htmlspecialchars($station_data['name'] ?? 'Unnamed Station'); ?></h2>
<p><strong>Serial Number:</strong> <?php echo htmlspecialchars($stationSerial); ?></p>
<?php if (!empty($station_data['description'])): ?>
<p><strong>Description:</strong> <?php echo htmlspecialchars($station_data['description']); ?></p>
<?php endif; ?>
</div>

<!-- Date filtering form -->
<div class="filter-form">
<h3>Filter Measurements by Date</h3>

<?php if (!empty($dateError)): ?>
<!-- Display date validation error if any -->
<div class="date-error">
<strong>Error:</strong> <?php echo htmlspecialchars($dateError); ?>
</div>
<?php endif; ?>

<form method="GET" action="">
<!-- Hidden field to preserve station serial when submitting form -->
<input type="hidden" name="station" value="<?php echo htmlspecialchars($stationSerial); ?>">

<div class="filter-row">
<div class="filter-group">
<label>Start Date:</label>
<!-- Date input for start filter -->
<input type="date" name="start_date"
value="<?php echo htmlspecialchars($startDate); ?>">
</div>

<div class="filter-group">
<label>End Date:</label>
<!-- Date input for end filter -->
<input type="date" name="end_date"
value="<?php echo htmlspecialchars($endDate); ?>">
</div>

<div class="filter-actions">
<button type="submit" class="filter-btn">Apply Filter</button>
<!-- Reset link: returns to page without date filters -->
<a href="?station=<?php echo htmlspecialchars($stationSerial); ?>" class="reset-btn">Reset</a>
</div>
</div>

<?php if (!empty($startDate) || !empty($endDate)): ?>
<!-- Show active filters summary -->
<div class="filter-info">
<strong>Active Filters:</strong>
<?php if (!empty($startDate)): ?> From <?php echo htmlspecialchars($startDate); ?><?php endif; ?>
<?php if (!empty($endDate)): ?>
<?php if (!empty($startDate)): ?> to <?php else: ?> Until <?php endif; ?>
<?php echo htmlspecialchars($endDate); ?>
<?php endif; ?>
</div>
<?php endif; ?>

</form>
</div>

<!-- Measurements display section -->
<div class="measurements-section">
<h2>Measurement Data</h2>

<?php if (mysqli_num_rows($measurements_result) > 0): ?>
<!-- If measurements exist, display them -->
<?php $count = 0; ?>
<?php while ($row = mysqli_fetch_assoc($measurements_result)): ?>
<?php $count++; ?>

<div class="measurement-card">
<div class="measurement-header">
<span>Measurement #<?php echo $count; ?> (ID: <?php echo $row['pk_measurement']; ?>)</span>
<span><?php echo $row['timestamp']; ?></span>
</div>

<div class="measurement-data">
<!-- Display all sensor values -->
<p>Temperature: <?php echo $row['temperature']; ?> °C</p>
<p>Humidity: <?php echo $row['humidity']; ?> %</p>
<p>Pressure: <?php echo $row['pressure']; ?> hPa</p>
<p>Light: <?php echo $row['light']; ?> lx</p>
<p>Gas: <?php echo $row['gas']; ?> ppm</p>
</div>
</div>

<?php endwhile; ?>

<!-- Summary of displayed measurements -->
<div class="measurement-summary">
<p><strong>Total measurements shown:</strong> <?php echo $count; ?></p>
</div>

<?php else: ?>
<!-- No measurements found message -->
<div class="no-data">
No measurements found for this station in that range.
</div>
<?php endif; ?>

</div>
</div>
</body>
</html>