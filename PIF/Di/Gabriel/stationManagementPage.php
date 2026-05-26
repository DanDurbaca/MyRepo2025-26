<?php
// Start session to track user authentication
session_start();

// Check if user is logged in
if (empty($_SESSION["userNameSession"])) {
    // If not logged in, display login required page
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

// Database connection setup
$host = "localhost";
$db = "portableindoorfeedback";
$user = "root";
$pass = "";
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    echo '<p style="color:red; text-align:center; margin-top:50px;">Database connection failed</p>';
    exit;
}

// Get current user from session
$currentUser = $_SESSION["userNameSession"];
$error = "";    // Variable for error messages
$success = "";  // Variable for success messages

/* ---------- ADMIN ROLE CHECK ---------- */

// SQL query to get the role of the currently logged-in user
// The ? is a placeholder to safely insert the username later
$role_sql = "SELECT role FROM user WHERE pk_username = ?";

// Prepare the SQL statement to prevent SQL injection
$role_stmt = mysqli_prepare($conn, $role_sql);

// Bind the current username to the placeholder (?)
// "s" means the value is a string
mysqli_stmt_bind_param($role_stmt, "s", $currentUser);

// Execute the prepared SQL statement
mysqli_stmt_execute($role_stmt);

// Get the result set returned by the query
$role_result = mysqli_stmt_get_result($role_stmt);

// Fetch the row as an associative array (e.g. ['role' => 'Admin'])
$user_data = mysqli_fetch_assoc($role_result);

// Check if the user's role is exactly "Admin"
// This will be true for admins, false for normal users
$isAdmin = ($user_data['role'] === 'Admin');


/* ---------- HANDLE FORM SUBMISSIONS ---------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check which form button was clicked
    $backToHome = $_POST["backToHome"] ?? '';
    $addStation = $_POST["addStation"] ?? '';
    $editStation = $_POST["editStation"] ?? '';
    $deleteStation = $_POST["deleteStation"] ?? '';
    $reassignStation = $_POST["reassignStation"] ?? ''; // NEW: Reassign station feature
    
    /* ---------- BACK TO HOMEPAGE ---------- */
    if (!empty($backToHome)) {
        header("Location: stationCreator.php");
        exit;
    }
    
    /* ---------- ADD/REGISTER NEW STATION ---------- */
    if (!empty($addStation)) {
        $serialNumber = trim($_POST["serialNumber"]);
        $stationName = trim($_POST["stationName"]);
        $description = trim($_POST["description"]);
        
        // Validate required fields
        if (empty($serialNumber) || empty($stationName)) {
            $error = "Error! Serial number and station name are required.";
        } else {
            // Check if station already exists
            $check_sql = "SELECT pk_serialNumber, fk_user_owns FROM station WHERE pk_serialNumber = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $serialNumber);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Station exists, check if already owned
                $station_data = mysqli_fetch_assoc($check_result);
                
                if ($station_data['fk_user_owns'] !== null) {
                    $error = "Error! This station is already registered to another user.";
                } else {
                    // Claim unassigned station (update ownership)
                    $update_sql = "UPDATE station SET name = ?, description = ?, fk_user_owns = ? WHERE pk_serialNumber = ?";
                    $update_stmt = mysqli_prepare($conn, $update_sql);
                    mysqli_stmt_bind_param($update_stmt, "ssss", $stationName, $description, $currentUser, $serialNumber);
                    
                    if (mysqli_stmt_execute($update_stmt)) {
                        $success = "Station claimed successfully!";
                    } else {
                        $error = "Error! Could not claim station: " . mysqli_error($conn);
                    }
                }
            } else {
                // Station doesn't exist, create new one
                $insert_sql = "INSERT INTO station (pk_serialNumber, name, description, fk_user_owns) VALUES (?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, "ssss", $serialNumber, $stationName, $description, $currentUser);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    $success = "New station created successfully!";
                } else {
                    $error = "Error! Could not create station: " . mysqli_error($conn);
                }
            }
        }
    }
    
    /* ---------- EDIT STATION ---------- */
    if (!empty($editStation)) {
        $serialNumber = $_POST["editSerialNumber"];
        $stationName = trim($_POST["editStationName"]);
        $description = trim($_POST["editDescription"]);
        
        if (empty($stationName)) {
            $error = "Error! Station name is required.";
        } else {
            // Check permissions: admin can edit any, users only their own
            if ($isAdmin) {
                // Admin can edit any station
                $verify_sql = "SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?";
                $verify_stmt = mysqli_prepare($conn, $verify_sql);
                mysqli_stmt_bind_param($verify_stmt, "s", $serialNumber);
            } else {
                // Regular user can only edit their own stations
                $verify_sql = "SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ? AND fk_user_owns = ?";
                $verify_stmt = mysqli_prepare($conn, $verify_sql);
                mysqli_stmt_bind_param($verify_stmt, "ss", $serialNumber, $currentUser);
            }
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            
            if (mysqli_num_rows($verify_result) > 0) {
                // User has permission, update station
                $update_sql = "UPDATE station SET name = ?, description = ? WHERE pk_serialNumber = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "sss", $stationName, $description, $serialNumber);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $success = "Station updated successfully!";
                } else {
                    $error = "Error! Could not update station: " . mysqli_error($conn);
                }
            } else {
                $error = "Error! You don't own this station or it doesn't exist.";
            }
        }
    }
    
    /* ---------- DELETE STATION ---------- */
    if (!empty($deleteStation)) {
        $serialNumber = $_POST["deleteSerialNumber"];
        $confirm = $_POST["confirmDelete"] ?? '';
        
        // Require confirmation typing "DELETE" for safety
        if ($confirm !== "DELETE") {
            $error = "Error! Please type DELETE to confirm deletion.";
        } else {
            // Check permissions similar to edit
            if ($isAdmin) {
                $verify_sql = "SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?";
                $verify_stmt = mysqli_prepare($conn, $verify_sql);
                mysqli_stmt_bind_param($verify_stmt, "s", $serialNumber);
            } else {
                $verify_sql = "SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ? AND fk_user_owns = ?";
                $verify_stmt = mysqli_prepare($conn, $verify_sql);
                mysqli_stmt_bind_param($verify_stmt, "ss", $serialNumber, $currentUser);
            }
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            
            if (mysqli_num_rows($verify_result) > 0) {
                // Delete station (cascade will delete measurements too)
                $delete_sql = "DELETE FROM station WHERE pk_serialNumber = ?";
                $delete_stmt = mysqli_prepare($conn, $delete_sql);
                mysqli_stmt_bind_param($delete_stmt, "s", $serialNumber);
                
                if (mysqli_stmt_execute($delete_stmt)) {
                    $success = "Station deleted successfully!";
                } else {
                    $error = "Error! Could not delete station: " . mysqli_error($conn);
                }
            } else {
                $error = "Error! You don't own this station or it doesn't exist.";
            }
        }
    }
    
    /* ---------- REASSIGN STATION (ADMIN ONLY) ---------- */
    if (!empty($reassignStation) && $isAdmin) {
        $serialNumber = $_POST["reassignSerialNumber"];
        $newOwner = trim($_POST["newOwner"]);
        
        if (empty($newOwner)) {
            $error = "Error! Please enter a username for the new owner.";
        } else {
            // Check if station exists
            $check_sql = "SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $serialNumber);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) === 0) {
                $error = "Error! Station does not exist.";
            } else {
                // Check if new owner exists
                $owner_sql = "SELECT pk_username FROM user WHERE pk_username = ?";
                $owner_stmt = mysqli_prepare($conn, $owner_sql);
                mysqli_stmt_bind_param($owner_stmt, "s", $newOwner);
                mysqli_stmt_execute($owner_stmt);
                $owner_result = mysqli_stmt_get_result($owner_stmt);
                
                if (mysqli_num_rows($owner_result) === 0) {
                    $error = "Error! User '$newOwner' does not exist.";
                } else {
                    // Transfer station ownership
                    $reassign_sql = "UPDATE station SET fk_user_owns = ? WHERE pk_serialNumber = ?";
                    $reassign_stmt = mysqli_prepare($conn, $reassign_sql);
                    mysqli_stmt_bind_param($reassign_stmt, "ss", $newOwner, $serialNumber);
                    
                    if (mysqli_stmt_execute($reassign_stmt)) {
                        $success = "Station reassigned to '$newOwner' successfully!";
                    } else {
                        $error = "Error! Could not reassign station: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
}

/* ---------- SORTING SYSTEM ---------- */
// Get sort parameter from URL or use default
$sort = $_GET['sort'] ?? 'name_asc';

// Determine ORDER BY clause based on sort parameter
$orderBy = "name ASC"; // Default: sort by name ascending

switch ($sort) {
    case 'name_desc':
        $orderBy = "name DESC"; // Name Z → A
        break;
    case 'serial_asc':
        $orderBy = "pk_serialNumber ASC"; // Serial number ascending
        break;
    case 'serial_desc':
        $orderBy = "pk_serialNumber DESC"; // Serial number descending
        break;
    default:
        $orderBy = "name ASC";
}

/* ---------- FETCH STATIONS LIST ---------- */
// Different queries for admin vs regular users
if ($isAdmin) {
    // Admin can see all stations with owner information
    $stations_sql = "SELECT s.*, u.pk_username as owner_name 
                     FROM station s 
                     LEFT JOIN user u ON s.fk_user_owns = u.pk_username 
                     ORDER BY $orderBy";
    $stations_stmt = mysqli_prepare($conn, $stations_sql);
} else {
    // Regular user sees only their stations
    $stations_sql = "SELECT * FROM station 
                     WHERE fk_user_owns = ? 
                     ORDER BY $orderBy";
    $stations_stmt = mysqli_prepare($conn, $stations_sql);
    mysqli_stmt_bind_param($stations_stmt, "s", $currentUser);
}

// Execute stations query
mysqli_stmt_execute($stations_stmt);
$stations_result = mysqli_stmt_get_result($stations_stmt);

/* ---------- FETCH ALL USERS FOR REASSIGNMENT (ADMIN ONLY) ---------- */
$all_users = [];
if ($isAdmin) {
    // Get all usernames for reassignment dropdown
    $users_sql = "SELECT pk_username FROM user ORDER BY pk_username";
    $users_result = mysqli_query($conn, $users_sql);
    while ($user_row = mysqli_fetch_assoc($users_result)) {
        $all_users[] = $user_row['pk_username']; // Add to array
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIF - Station Management</title>
    <link rel="stylesheet" href="station_customization.css">
    <style>
        /* Additional styles for reassignment feature */
        .btn-reassign {
            background: #6f42c1; /* Purple color for reassign button */
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 2px;
        }
        
        .btn-reassign:hover {
            background: #5a32a3; /* Darker purple on hover */
        }
        
        .admin-badge {
            display: inline-block;
            background: #dc3545; /* Red badge for admin */
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
            font-weight: bold;
        }
        
        .station-owner-info {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        
        .station-owner-info strong {
            color: #333;
        }
        
        .admin-only-feature {
            border-left: 4px solid #6f42c1; /* Purple left border */
            padding-left: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back to Homepage Form -->
        <form method="POST" action="" class="back-form">
            <input type="submit" class="back-btn" value="← Back" name="backToHome">
        </form>
        
        <!-- Page Header with Admin Badge if applicable -->
        <h1>Station Management <?php if ($isAdmin): ?><span class="admin-badge">ADMIN</span><?php endif; ?></h1>
        
        <!-- Error/Success Messages Display -->
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Add New Station Section -->
        <div class="section">
            <h2>Add/Register New Station</h2>
            <form method="POST" action="">
                <h3>Serial Number:</h3>
                <input type="text" name="serialNumber" placeholder="Enter station serial number" required>
                
                <h3>Station Name:</h3>
                <input type="text" name="stationName" placeholder="Enter station name" required>
                
                <h3>Description (Optional):</h3>
                <textarea name="description" placeholder="Enter station description" rows="3"></textarea>
                
                <input type="submit" value="Add/Register Station" name="addStation">
            </form>
        </div>
        
        <!-- Stations List Section -->
        <div class="section">
            <?php if ($isAdmin): ?>
                <!-- Admin sees "All Stations" -->
                <h2>All Stations (<?php echo mysqli_num_rows($stations_result); ?>)</h2>
            <?php else: ?>
                <!-- Regular user sees "My Stations" -->
                <h2>My Stations (<?php echo mysqli_num_rows($stations_result); ?>)</h2>
            <?php endif; ?>

            <!-- Sorting Form -->
            <form method="GET" action="" class="sort-form">
                <label>Sort by:</label>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="name_asc" <?php if ($sort == 'name_asc')
                            echo 'selected'; ?>>Name A → Z</option>
                        <option value="name_desc" <?php if ($sort == 'name_desc')
                            echo 'selected'; ?>>Name Z → A</option>
                        <option value="serial_asc" <?php if ($sort == 'serial_asc')
                            echo 'selected'; ?>>Serial ↑</option>
                        <option value="serial_desc" <?php if ($sort == 'serial_desc')
                            echo 'selected'; ?>>Serial ↓</option>
                    </select>
            </form>
            
            <?php if (mysqli_num_rows($stations_result) > 0): ?>
                <!-- Display stations list -->
                <div class="stations-list">
                    <?php 
                    // Reset pointer to beginning of result set
                    mysqli_data_seek($stations_result, 0);
                    while ($station = mysqli_fetch_assoc($stations_result)): 
                        // Check if current user owns this station
                        $isOwnedByCurrentUser = (!$isAdmin && $station['fk_user_owns'] === $currentUser) || 
                                                ($isAdmin && isset($station['owner_name']) && $station['owner_name'] === $currentUser);
                    ?>
                        <div class="station-card">
                            <!-- Station Header with Name and ID -->
                            <div class="station-header">
                                <h3><?php echo htmlspecialchars($station['name']); ?></h3>
                                <span class="station-id">ID: <?php echo htmlspecialchars($station['pk_serialNumber']); ?></span>
                            </div>
                            
                            <!-- Station Description (if exists) -->
                            <?php if (!empty($station['description'])): ?>
                                <p class="station-desc"><?php echo htmlspecialchars($station['description']); ?></p>
                            <?php endif; ?>
                            
                            <!-- Owner Information (Admin only) -->
                            <?php if ($isAdmin && isset($station['owner_name'])): ?>
                                <div class="station-owner-info">
                                    <strong>Owner:</strong> 
                                    <?php echo !empty($station['owner_name']) ? htmlspecialchars($station['owner_name']) : '<em>Unassigned</em>'; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div class="station-actions">
                                <!-- View Measurements Button -->
                                <a href="stationMeasurementsPage.php?station=<?php echo $station['pk_serialNumber']; ?>" class="btn-view">
                                    Station Measurements
                                </a>
                            
                                <!-- Edit Button (if user has permission) -->
                                <?php if ($isAdmin || $isOwnedByCurrentUser): ?>
                                    <button onclick="openEditModal(
                                        '<?php echo $station['pk_serialNumber']; ?>',
                                        '<?php echo htmlspecialchars($station['name'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($station['description'] ?? '', ENT_QUOTES); ?>'
                                    )" class="btn-edit">Edit</button>
                                
                                    <!-- Delete Button (if user has permission) -->
                                    <button onclick="openDeleteModal(
                                        '<?php echo $station['pk_serialNumber']; ?>',
                                        '<?php echo htmlspecialchars($station['name'], ENT_QUOTES); ?>'
                                    )" class="btn-delete">Delete</button>
                                <?php endif; ?>
                            
                                <!-- Share Button (only for owned stations) -->
                                <?php if ($isOwnedByCurrentUser): ?>
                                    <button onclick="openShareModal('<?php echo $station['pk_serialNumber']; ?>')" class="btn-share">
                                        Share
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Reassign Button (Admin only) -->
                                <?php if ($isAdmin): ?>
                                    <button onclick="openReassignModal(
                                        '<?php echo $station['pk_serialNumber']; ?>',
                                        '<?php echo htmlspecialchars($station['name'], ENT_QUOTES); ?>',
                                        '<?php echo isset($station['owner_name']) ? htmlspecialchars($station['owner_name'], ENT_QUOTES) : ''; ?>'
                                    )" class="btn-reassign">
                                        Reassign
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <!-- No stations message -->
                <div class="no-data">
                    <p>You don't have any stations yet. Add your first station above!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Station Modal (hidden by default) -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Station</h2>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="editSerialNumber" id="editSerialNumber">
                
                <h3>Station Name:</h3>
                <input type="text" name="editStationName" id="editStationName" required>
                
                <h3>Description (Optional):</h3>
                <textarea name="editDescription" id="editDescription" rows="3"></textarea>
                
                <input type="submit" value="Save Changes" name="editStation">
            </form>
        </div>
    </div>
    
    <!-- Delete Station Modal (hidden by default) -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>Delete Station</h2>
            <p id="deleteMessage">Are you sure you want to delete this station?</p>
            <form method="POST" action="" id="deleteForm">
                <input type="hidden" name="deleteSerialNumber" id="deleteSerialNumber">
                
                <h3>Type "DELETE" to confirm:</h3>
                <input type="text" name="confirmDelete" placeholder="Type DELETE here" required>
                
                <input type="submit" value="Delete Station" name="deleteStation" class="btn-delete">
            </form>
        </div>
    </div>
    
    <!-- Reassign Station Modal (Admin only, hidden by default) -->
    <div id="reassignModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReassignModal()">&times;</span>
            <h2>Reassign Station</h2>
            <p id="reassignMessage">Assign this station to a different user.</p>
            <form method="POST" action="" id="reassignForm">
                <input type="hidden" name="reassignSerialNumber" id="reassignSerialNumber">
                
                <h3>Current Owner:</h3>
                <p id="currentOwnerDisplay" style="padding: 8px; background: #f8f9fa; border-radius: 4px;"></p>
                
                <h3>New Owner Username:</h3>
                <?php if ($isAdmin && !empty($all_users)): ?>
                    <!-- Dropdown for admin to select user -->
                    <select name="newOwner" id="newOwner" required style="width: 100%; padding: 8px; margin-bottom: 15px;">
                        <option value="">-- Select a user --</option>
                        <?php foreach ($all_users as $username): ?>
                            <option value="<?php echo htmlspecialchars($username); ?>">
                                <?php echo htmlspecialchars($username); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <!-- Text input for username (backup) -->
                    <input type="text" name="newOwner" id="newOwner" placeholder="Enter username" required style="width: 100%; padding: 8px; margin-bottom: 15px;">
                <?php endif; ?>
                
                <!-- Admin warning message -->
                <div class="admin-only-feature">
                    <p><strong>Admin Only:</strong> This will transfer station ownership to the selected user.</p>
                </div>
                
                <input type="submit" value="Reassign Station" name="reassignStation" class="btn-reassign" style="width: 100%; padding: 10px;">
            </form>
        </div>
    </div>
    
    <!-- JavaScript for Modal Functions -->
    <script>
        // Edit Modal Functions
        function openEditModal(serial, name, description) {
            document.getElementById('editSerialNumber').value = serial;
            document.getElementById('editStationName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Delete Modal Functions
        function openDeleteModal(serial, name) {
            document.getElementById('deleteSerialNumber').value = serial;
            document.getElementById('deleteMessage').innerHTML = `Are you sure you want to delete station "<strong>${name}</strong>"? This will also delete all measurement data for this station.`;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Reassign Modal Functions
        function openReassignModal(serial, name, currentOwner) {
            document.getElementById('reassignSerialNumber').value = serial;
            document.getElementById('currentOwnerDisplay').innerHTML = currentOwner ? 
                `<strong>${currentOwner}</strong>` : 
                `<em>Unassigned</em>`;
            document.getElementById('reassignMessage').innerHTML = `Reassign station "<strong>${name}</strong>" to a different user.`;
            document.getElementById('reassignModal').style.display = 'block';
        }
        
        function closeReassignModal() {
            document.getElementById('reassignModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            const reassignModal = document.getElementById('reassignModal');
            
            if (event.target == editModal) {
                closeEditModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
            if (event.target == reassignModal) {
                closeReassignModal();
            }
        }
    </script>
</body>
</html>