<?php
// Start session for auth and preferences
session_start();
if (!isset($_SESSION["UserLoggedIn"])) {
    $_SESSION["UserLoggedIn"] = false;
}

// Default language selection
if (!isset($_SESSION["language"])) {
    $_SESSION["language"] = "EN"; //english language by default
}

if (isset($_GET["language"])) {
    $_SESSION["language"] = $_GET["language"];
}

// Default role if not set in session
if (!isset($_SESSION["role"])) {
    $_SESSION["role"] = "User"; // safe default (not Admin)
}

// https://www.w3schools.com/php/php_sessions.asp? (PHP Session Functions)

// https://www.w3schools.com/php/php_mysql_prepared_statements.asp? (PHP MySQL Prepared Statements)

// Database connection settings
$host = "localhost";
$username = "root";
$psw = "";
$dbName = "Project_Database";

// Connect to the database
$connection = mysqli_connect($host, $username, $psw,  $dbName);

// Load language strings into an in-memory map
$arrayOfStrings = [];

$sqlSelect = $connection->prepare("SELECT * FROM LanguageSwitch");
$sqlSelect->execute();
$result = $sqlSelect->get_result();
while ($row = $result->fetch_assoc()) {
    $row['NameCalled'];
    if (count($row) == 3) {
        if ($_SESSION["language"] == "EN")
            $arrayOfStrings[$row['NameCalled']] = $row['EnglishVersion'];
        else
            $arrayOfStrings[$row['NameCalled']] = $row['FrenchVersion'];
    }
}




// Render the common header and navigation bar
function NavigationBar1($buttonTOHighlight)
{
    global $arrayOfStrings;
?>
    <div id="wrapper">
        <header>
            <h1>Portable Indoor Feedback</h1>
            <nav>
                <ul><?php
                    // Check if the user is logged in and is an user (usertype == 0)
                    if ($_SESSION["UserLoggedIn"]) { ?>
                    <li><a href="Menu.php" <?php if ($buttonTOHighlight == "Menu") {
                                                print("class='active'");
                                            } ?>><?php print $arrayOfStrings["Menu"] ?></a></li>
                    <li><a href="Collection.php" <?php if ($buttonTOHighlight == "Collection") {
                                                    print("class='active'");
                                            } ?>><?php print $arrayOfStrings["Collection"] ?></a></li>
                    <li><a href="MyStation.php" <?php if ($buttonTOHighlight == "MyStation") {
                                                    print("class='active'");
                                            } ?>><?php print $arrayOfStrings["Station"] ?></a></li>
                    <li><a href="FriendPage.php" <?php if ($buttonTOHighlight == "FriendPage") {
                                                    print("class='active'");
                                            } ?>><?php print $arrayOfStrings["FriendPage"] ?></a></li>
                    <li><a href="measurements.php" <?php if ($buttonTOHighlight == "Measurements") {
                                                    print("class='active'");
                                            } ?>><?php print $arrayOfStrings["Measurements"] ?></a></li> <?php } ?>
                </ul>
                <ul>
                    <li>
                        <?php
                        // Check if the user is logged in
                        if (isset($_SESSION["UserLoggedIn"]) && $_SESSION["UserLoggedIn"]) {
                            // Display the username
                            echo ($arrayOfStrings["Welcomeall"] . " " . $_SESSION["User"]); //create the session
                        } else {
                            // Display "User Unknown" if not logged in
                            echo $arrayOfStrings["UserUnknown"];
                        }
                        ?>
                    </li>

                    <li><a href="index.php" <?php if ($buttonTOHighlight == "Login") {
                                                print("class='active'");
                                            } ?>> <?php if ($_SESSION["UserLoggedIn"]) print $arrayOfStrings["Logout"];
                                                    else print $arrayOfStrings["Login"]; ?> </a></li>

                    <?php
                    // Check if the user is logged in and is an admin (usertype == 1)
                    if (isset($_SESSION["UserLoggedIn"]) && $_SESSION["UserLoggedIn"] && isset($_SESSION["role"]) && $_SESSION["role"] === 'Admin') { ?>
                        <li><a href="Admin.php" <?php if ($buttonTOHighlight == "admin") {
                                                    print("class='active'");
                                                } ?>><?php print $arrayOfStrings["Admin"] ?></a></li>
                    <?php } ?>

                    <?php if (!$_SESSION["UserLoggedIn"]) {
                    ?>
                        <li><a href="register.php" <?php if ($buttonTOHighlight == "Register") {
                                                        print("class='active'");
                                                    } ?>><?php print $arrayOfStrings["Register"] ?> </a></li>
                    <?php
                    }
                    ?>
                    <?php
                    if ($_SESSION["language"] == "EN") {
                    ?>
                        <li><a href="index.php?language=FR">En Français </a></li>
                    <?php
                    } else {
                    ?>
                        <li><a href="index.php?language=EN">In English </a></li>
                    <?php
                    }
                    ?>
                </ul>
            </nav>
            <img src="banner.jpg" alt="Green banner." />
        </header>
    <?php
}
 

// Check if a username already exists
function userAlreadyExists($checkUser)
{
    global $connection;
    // Prepare query to check if a username exists
    $sqlSelect = $connection->prepare("SELECT * FROM `user` WHERE pk_username = ?");
    $sqlSelect->bind_param("s", $checkUser);
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    if ($result->num_rows == 0) {
        return false;
    } else {
        return true;
    }
}


// Verify a user's password
function checkUsersPassword($givenUser, $givenPassword)
{
    global $connection;
    // Prepare query to fetch a user by username
    $sqlSelect = $connection->prepare("SELECT * FROM `user` WHERE pk_username = ?");
    $sqlSelect->bind_param("s", $givenUser);
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    if ($result->num_rows == 0) {
        return false;
    } else {
        $row = $result->fetch_assoc();
        /*if ($row["password"] == $givenPassword) {
                    return true;
                }*/
        if (password_verify($givenPassword, $row["password"])) {
            return true;
        }
    }
    return false;
}




// Enforce login for protected pages
function requireLogin()
{
    global $arrayOfStrings;
    if (!isset($_SESSION["UserLoggedIn"]) || !$_SESSION["UserLoggedIn"]) {
        echo "<h1>" . $arrayOfStrings["UserOnly"] . "</h1>";
        exit;
    }
}

// Enforce admin-only access
function requireAdmin()
{
    global $arrayOfStrings;
    if (!isset($_SESSION["UserLoggedIn"]) || !$_SESSION["UserLoggedIn"] || $_SESSION["role"] !== "Admin") {
        echo "<h1>" . $arrayOfStrings["AdminOnly"] . "</h1>";
        exit;
    }
}

// Return current username from session
function getCurrentUser()
{
    return $_SESSION['User'] ?? '';
}

// Return stations owned by a user (includes description for MyStation)
function getUserStations($username)
{
    global $connection;
    // Prepare query to get stations owned by a user
    $getStations = $connection->prepare("SELECT pk_serialNumber, name, description FROM station WHERE fk_user_owns = ? ORDER BY pk_serialNumber ASC");
    $getStations->bind_param("s", $username);
    $getStations->execute();
    return $getStations->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Return the latest measurement for each station owned by a user
function getLatestMeasurementPerStation($me)
{
    global $connection;

    $query = "
        SELECT m.temperature, m.humidity, m.pressure, m.light, m.gas, m.timestamp, m.fk_station_records
        FROM measurement m
        INNER JOIN station s ON s.pk_serialNumber = m.fk_station_records
        INNER JOIN (
            SELECT fk_station_records, MAX(timestamp) AS max_ts
            FROM measurement
            GROUP BY fk_station_records
        ) latest ON latest.fk_station_records = m.fk_station_records AND latest.max_ts = m.timestamp
        WHERE s.fk_user_owns = ?
        ORDER BY m.fk_station_records ASC
    ";

    // Prepare query to fetch latest measurement per station
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $me);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Return measurements for a user/admin with optional filters
function getMeasurements($me, $isAdmin, $stationSerial = '', $range = '', $limit = 100)
{
    global $connection;

    // If not admin, validate station belongs to user (otherwise ignore station filter)
    if (!$isAdmin && $stationSerial !== '') {
        // Prepare query to validate station ownership
        $chk = $connection->prepare("SELECT 1 FROM station WHERE pk_serialNumber = ? AND fk_user_owns = ?");
        $chk->bind_param("ss", $stationSerial, $me);
        $chk->execute();
        if (!$chk->get_result()->fetch_assoc()) {
            $stationSerial = '';
        }
    }

    // Compute date range
    $startDateTime = '';
    $endDateTime = '';

    if ($range === 'today') {
        $startDateTime = date('Y-m-d 00:00:00');
        $endDateTime = date('Y-m-d 23:59:59');
    } else if ($range === '24h') {
        $startDateTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $endDateTime = date('Y-m-d H:i:s');
    } else if ($range === '7d') {
        $startDateTime = date('Y-m-d H:i:s', strtotime('-7 days'));
        $endDateTime = date('Y-m-d H:i:s');
    }

    $query = "
        SELECT m.pk_measurement, m.temperature, m.humidity, m.pressure, m.light, m.gas, m.timestamp, m.fk_station_records
        FROM measurement m
        INNER JOIN station s ON s.pk_serialNumber = m.fk_station_records
    ";

    $where = [];
    $types = "";
    $params = [];

    if (!$isAdmin) {
        $where[] = "s.fk_user_owns = ?";
        $types .= "s";
        $params[] = $me;
    }

    if ($stationSerial !== '') {
        $where[] = "m.fk_station_records = ?";
        $types .= "s";
        $params[] = $stationSerial;
    }

    if ($startDateTime !== '') {
        $where[] = "m.timestamp >= ?";
        $types .= "s";
        $params[] = $startDateTime;
    }

    if ($endDateTime !== '') {
        $where[] = "m.timestamp <= ?";
        $types .= "s";
        $params[] = $endDateTime;
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY m.timestamp DESC LIMIT ?";
    $types .= "i";
    $params[] = (int)$limit;

    // Prepare query for filtered measurements
    $stmt = $connection->prepare($query);
    if (!$stmt) { die("Prepare failed: " . $connection->error); }


    // Bind params in a PHP-version-safe way
    $bindArgs = [];
    $bindArgs[] = $types;

    // bind_param requires references
    for ($i = 0; $i < count($params); $i++) {
        $bindArgs[] = &$params[$i];
    }

    call_user_func_array([$stmt, 'bind_param'], $bindArgs);

    $stmt->execute();
    if (!$stmt->execute()) { die("Execute failed: " . $stmt->error); }
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}




// Return collections shared with a user
function getSharedCollections($username)
{
    global $connection;
    // Prepare query to load collections shared with a user
    $getShared = $connection->prepare("SELECT c.pk_collection, c.name, c.description, c.fk_user_creates FROM collection c INNER JOIN hasaccess h ON c.pk_collection = h.pkfk_collection WHERE h.pkfk_user = ? ORDER BY c.pk_collection DESC");
    $getShared->bind_param("s", $username);
    $getShared->execute();
    return $getShared->get_result()->fetch_all(MYSQLI_ASSOC);
}


// A) Check if station is owned by user
function isStationOwnedByUser($stationSerial, $username)
{
    global $connection;
    // Prepare query to check station ownership
    $stmt = $connection->prepare("SELECT 1 FROM station WHERE pk_serialNumber = ? AND fk_user_owns = ?");
    $stmt->bind_param("ss", $stationSerial, $username);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// B) Create collection
function createCollection($name, $desc, $createdBy)
{
    global $connection;
    // Prepare insert to create a collection
    $stmt = $connection->prepare("INSERT INTO collection (name, description, fk_user_creates) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $desc, $createdBy);
    if ($stmt->execute()) {
        return $connection->insert_id;
    }
    return 0;
}

// C) Get measurement IDs for station in date range
function getMeasurementIdsForStationInRange($stationSerial, $startDate, $endDate)
{
    global $connection;
    // Prepare query to find measurements in a date range for a station
    $stmt = $connection->prepare("SELECT pk_measurement FROM measurement WHERE fk_station_records = ? AND timestamp >= ? AND timestamp <= ? ORDER BY pk_measurement ASC");
    $stmt->bind_param("sss", $stationSerial, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// D) Add measurements to collection (avoid duplicates)
function addMeasurementsToCollection($collectionId, $measurementIds)
{
    global $connection;
    $count = 0;
    foreach ($measurementIds as $row) {
        $pk_measurement = $row['pk_measurement'];
        // Check if already exists
        // Prepare query to avoid duplicate measurement in collection
        $chk = $connection->prepare("SELECT 1 FROM contains WHERE pkfk_collection = ? AND pkfk_measurement = ?");
        $chk->bind_param("ii", $collectionId, $pk_measurement);
        $chk->execute();
        if ($chk->get_result()->num_rows === 0) {
            // Prepare insert to add measurement into collection
            $ins = $connection->prepare("INSERT INTO contains (pkfk_collection, pkfk_measurement) VALUES (?, ?)");
            $ins->bind_param("ii", $collectionId, $pk_measurement);
            if ($ins->execute()) {
                $count++;
            }
        }
    }
    return $count;
}

// E) Delete collection and related data
function deleteCollectionById($collectionId)
{
    global $connection;
    $c_id = (int)$collectionId;
    
    // Prepare delete to remove measurements from collection
    $delContains = $connection->prepare("DELETE FROM contains WHERE pkfk_collection = ?");
    $delContains->bind_param("i", $c_id);
    $delContains->execute();
    
    // Prepare delete to remove sharing access for collection
    $delAccess = $connection->prepare("DELETE FROM hasaccess WHERE pkfk_collection = ?");
    $delAccess->bind_param("i", $c_id);
    $delAccess->execute();
    
    // Prepare delete to remove the collection record
    $delCollection = $connection->prepare("DELETE FROM collection WHERE pk_collection = ?");
    $delCollection->bind_param("i", $c_id);
    return $delCollection->execute();
}

// F) Update collection name/desc
function updateCollection($collectionId, $newName, $newDesc)
{
    global $connection;
    $c_id = (int)$collectionId;
    // Prepare update to rename or describe collection
    $stmt = $connection->prepare("UPDATE collection SET name = ?, description = ? WHERE pk_collection = ?");
    $stmt->bind_param("ssi", $newName, $newDesc, $c_id);
    return $stmt->execute();
}

// G) Check if collection belongs to user
function collectionBelongsToUser($collectionId, $username)
{
    global $connection;
    $c_id = (int)$collectionId;
    // Prepare query to verify collection ownership
    $stmt = $connection->prepare("SELECT 1 FROM collection WHERE pk_collection = ? AND fk_user_creates = ?");
    $stmt->bind_param("is", $c_id, $username);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// H) Get collections created by user
function getCollectionsCreatedByUser($username)
{
    global $connection;
    // Prepare query to load collections created by a user
    $stmt = $connection->prepare("SELECT pk_collection, name, description FROM collection WHERE fk_user_creates = ? ORDER BY pk_collection DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// I) Get all collections (admin)
function getAllCollections()
{
    global $connection;
    // Prepare query to load all collections for admin
    $stmt = $connection->prepare("SELECT pk_collection, name, description, fk_user_creates FROM collection ORDER BY pk_collection DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// J) Get all stations (admin)
function getAllStations()
{
    global $connection;
    // Prepare query to load all stations for admin
    $stmt = $connection->prepare("SELECT pk_serialNumber, name FROM station ORDER BY pk_serialNumber ASC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// K) Get users a collection is shared with
function getUsersSharedWithCollection($collectionId)
{
    global $connection;
    $c_id = (int)$collectionId;
    // Prepare query to load users shared with a collection
    $stmt = $connection->prepare("SELECT pkfk_user FROM hasaccess WHERE pkfk_collection = ? ORDER BY pkfk_user ASC");
    $stmt->bind_param("i", $c_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// L) Get mutual friends for user
function getMutualFriends($username)
{
    global $connection;
    // Prepare query to load mutual friends for a user
    $stmt = $connection->prepare("SELECT DISTINCT u.pk_username, u.firstName, u.lastName FROM `user` u INNER JOIN isfriend f ON u.pk_username = f.pkfk_user_friend WHERE f.pkfk_user_user = ? AND EXISTS (SELECT 1 FROM isfriend r WHERE r.pkfk_user_user = u.pk_username AND r.pkfk_user_friend = ?) ORDER BY u.pk_username ASC");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Render a measurements table or a localized "no measurements" message
function renderMeasurementsTable($measurements)
{
    global $arrayOfStrings;

    if (empty($measurements)) {
        // Use the collection-specific key if present, otherwise a generic fallback
        $noMsg = $arrayOfStrings['NoMeasurementsInCollection'] ?? 'No measurements found.';
        echo '<p>' . htmlspecialchars($noMsg) . '</p>';
        return;
    }

    // https://www.w3schools.com/html/html_tables.asp? (HTML Tables)

    $showStation = isset($measurements[0]['fk_station_records']);

    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr>';
    if ($showStation) {
        echo '<th>' . htmlspecialchars($arrayOfStrings['Station'] ?? 'Station') . '</th>';
    }
    echo '<th>' . htmlspecialchars($arrayOfStrings['Timestamp'] ?? 'Timestamp') . '</th>';
    echo '<th>' . htmlspecialchars($arrayOfStrings['Temperature'] ?? 'Temperature') . '</th>';
    echo '<th>' . htmlspecialchars($arrayOfStrings['Humidity'] ?? 'Humidity') . '</th>';
    echo '<th>' . htmlspecialchars($arrayOfStrings['Pressure'] ?? 'Pressure') . '</th>';
    echo '<th>' . htmlspecialchars($arrayOfStrings['Light'] ?? 'Light') . '</th>';
    echo '<th>' . htmlspecialchars($arrayOfStrings['Gas'] ?? 'Gas') . '</th>';
    echo '</tr>';

    foreach ($measurements as $m) {
        echo '<tr>';
        if ($showStation) {
            echo '<td>' . htmlspecialchars($m['fk_station_records']) . '</td>';
        }
        echo '<td>' . htmlspecialchars($m['timestamp']) . '</td>';
        echo '<td>' . htmlspecialchars($m['temperature']) . ' °C</td>';
        echo '<td>' . htmlspecialchars($m['humidity']) . ' % </td>';
        echo '<td>' . htmlspecialchars($m['pressure']) . ' hPa</td>';
        echo '<td>' . htmlspecialchars($m['light']) . ' lux</td>';
        echo '<td>' . htmlspecialchars($m['gas']) . ' ppm</td>';
        echo '</tr>';
    }

    echo '</table>';
}

    ?>

