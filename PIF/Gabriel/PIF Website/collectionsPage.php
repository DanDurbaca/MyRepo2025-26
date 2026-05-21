<?php
session_start();

if (empty($_SESSION["userNameSession"])) {
    header("Location: Log-in.php");
    exit;
}

$host = "localhost";
$db = "portableindoorfeedback";
$user = "root";
$pass = "";
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed");
}

$currentUser = $_SESSION["userNameSession"];

/* ---------- GET FRIEND LIST ---------- */
$friends = mysqli_query(
    $conn,
    "SELECT u.pk_username 
     FROM isfriend i
     JOIN user u 
     ON u.pk_username = i.pkfk_user_friend
     WHERE i.pkfk_user_user = '$currentUser'"
);

/* ---------- GET USER COLLECTIONS ---------- */
$collections = mysqli_query(
    $conn,
    "SELECT * FROM collection WHERE fk_user_creates = '$currentUser'"
);

/* ---------- GET STATIONS USER OWNS ---------- */
$stations = mysqli_query(
    $conn,
    "SELECT * FROM station WHERE fk_user_owns = '$currentUser'"
);

/* ---------- HANDLE CREATE COLLECTION ---------- */
if (isset($_POST['create_collection'])) {
    $name = $_POST['collection_name'];
    $desc = $_POST['collection_desc'];
    $selected_stations = $_POST['stations'] ?? [];

    // Insert collection
    $stmt = $conn->prepare(
        "INSERT INTO collection (name, description, fk_user_creates) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $name, $desc, $currentUser);
    $stmt->execute();

    $collection_id = $stmt->insert_id;

    // Insert measurements from selected stations into contains
    foreach ($selected_stations as $station_sn) {
        $measurements = mysqli_query($conn, "SELECT pk_measurement FROM measurement WHERE fk_station_records = '$station_sn'");
        while ($m = mysqli_fetch_assoc($measurements)) {
            $stmt2 = $conn->prepare(
                "INSERT INTO contains (pkfk_collection, pkfk_measurement) VALUES (?, ?)"
            );
            $stmt2->bind_param("ii", $collection_id, $m['pk_measurement']);
            $stmt2->execute();
        }
    }

    header("Location: collectionsPage.php");
    exit;
}

/* ---------- HANDLE SHARE COLLECTION ---------- */
if (isset($_POST['share_collection'])) {
    $collectionId = $_POST['collection_id'];
    $friend = $_POST['friend_username'];

    $stmt = $conn->prepare(
        "INSERT INTO hasaccess (pkfk_user, pkfk_collection) VALUES (?, ?)"
    );
    $stmt->bind_param("si", $friend, $collectionId);
    $stmt->execute();

    header("Location: collectionsPage.php");
    exit;
}

/* ---------- HANDLE UNSHARE COLLECTION ---------- */
if (isset($_POST['unshare_collection'])) {
    $collectionId = $_POST['collection_id'];
    $friend = $_POST['friend_username'];

    $stmt = $conn->prepare(
        "DELETE FROM hasaccess WHERE pkfk_user = ? AND pkfk_collection = ?"
    );
    $stmt->bind_param("si", $friend, $collectionId);
    $stmt->execute();

    header("Location: collectionsPage.php");
    exit;
}

/* ---------- HANDLE DELETE COLLECTION ---------- */
if (isset($_POST['delete_collection'])) {
    $collectionId = $_POST['collection_id'];

    $stmt = $conn->prepare(
        "DELETE FROM collection WHERE pk_collection = ? AND fk_user_creates = ?"
    );
    $stmt->bind_param("is", $collectionId, $currentUser);
    $stmt->execute();

    header("Location: collectionsPage.php");
    exit;
}

/* ---------- GET COLLECTIONS SHARED WITH ME ---------- */
$shared_collections = mysqli_query(
    $conn,
    "SELECT c.pk_collection, c.name, c.description
     FROM collection c
     JOIN hasaccess h ON c.pk_collection = h.pkfk_collection
     WHERE h.pkfk_user = '$currentUser'"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Collections</title>
<link rel="stylesheet" href="collections_customization.css">
</head>
<body>
<div class="container">

<a href="homePage.php" class="btn">← Back</a>

<h1>Your Collections</h1>

<!-- CREATE NEW COLLECTION -->
<div class="card">
<h2>Create New Collection</h2>
<form method="POST">
<input type="text" name="collection_name" placeholder="Collection name" required>
<textarea name="collection_desc" placeholder="Description"></textarea>

<h3>Select Stations to Include</h3>
<?php if(mysqli_num_rows($stations) === 0): ?>
<p style="color:white;">You don't own any stations yet.</p>
<?php else: ?>
<?php
mysqli_data_seek($stations, 0);
while($st = mysqli_fetch_assoc($stations)):
?>
<label style="display:block; margin-bottom:5px;">
    <input type="checkbox" name="stations[]" value="<?php echo $st['pk_serialNumber']; ?>">
    [<?php echo $st['pk_serialNumber']; ?>] <?php echo htmlspecialchars($st['name']); ?>
</label>
<?php endwhile; ?>
<?php endif; ?>

<button name="create_collection">Create Collection</button>
</form>
</div>

<!-- EXISTING COLLECTIONS -->
<h2>Your Existing Collections</h2>

<?php
mysqli_data_seek($collections, 0);
while ($col = mysqli_fetch_assoc($collections)):
?>
<div class="card">
<strong><?php echo htmlspecialchars($col['name']); ?></strong>
<p><?php echo htmlspecialchars($col['description']); ?></p>

<hr>

<!-- LIST WHO IT'S SHARED WITH -->
<h3>Shared With</h3>
<?php
$shared_with = mysqli_query($conn, "
    SELECT u.pk_username 
    FROM hasaccess h
    JOIN user u ON h.pkfk_user = u.pk_username
    WHERE h.pkfk_collection = {$col['pk_collection']}
");
if(mysqli_num_rows($shared_with) === 0) {
    echo "<p style='color:white; font-style:italic;'>Not shared with anyone.</p>";
} else {
    echo "<ul>";
    while($sw = mysqli_fetch_assoc($shared_with)) {
        echo "<li>" . htmlspecialchars($sw['pk_username']) . "</li>";
    }
    echo "</ul>";
}
?>

<hr>
<h3>Share with a friend</h3>
<form method="POST">
<input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>">

<select name="friend_username" required>
<option value="">Select a friend</option>
<?php 
mysqli_data_seek($friends, 0);
while ($f = mysqli_fetch_assoc($friends)): 
?>
<option value="<?php echo $f['pk_username']; ?>">
<?php echo $f['pk_username']; ?>
</option>
<?php endwhile; ?>
</select>

<button name="share_collection">Share (View Only)</button>
</form>

<hr>

<h3>Unshare</h3>
<form method="POST">
<input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>">

<input type="text" name="friend_username" 
placeholder="Friend username to remove" required>

<button name="unshare_collection">Remove Access</button>
</form>

<hr>

<h3>Delete Collection</h3>
<form method="POST">
<input type="hidden" name="collection_id" value="<?php echo $col['pk_collection']; ?>">
<button name="delete_collection">Delete Collection</button>
</form>
</div>
<?php endwhile; ?>

<!-- SHARED WITH ME -->
<h2>Collections Shared With Me</h2>

<?php
if (mysqli_num_rows($shared_collections) === 0) {
    echo "<p style='color:white; font-style:italic;'>No collections shared with you.</p>";
}

while ($sc = mysqli_fetch_assoc($shared_collections)):

    // Get all measurements in this collection, grouped by station serial number
    $measurements = mysqli_query($conn, "
        SELECT m.*, s.name AS station_name, s.pk_serialNumber
        FROM contains cn
        JOIN measurement m ON cn.pkfk_measurement = m.pk_measurement
        JOIN station s ON m.fk_station_records = s.pk_serialNumber
        WHERE cn.pkfk_collection = {$sc['pk_collection']}
        ORDER BY s.pk_serialNumber, m.timestamp
    ");

    $stations_data = [];
    while ($m = mysqli_fetch_assoc($measurements)) {
        $sn = $m['pk_serialNumber'];
        if (!isset($stations_data[$sn])) {
            $stations_data[$sn] = [
                'name' => $m['station_name'],
                'measurements' => []
            ];
        }
        $stations_data[$sn]['measurements'][] = $m;
    }
?>
<div class="card">
<strong><?php echo htmlspecialchars($sc['name']); ?></strong>
<p><?php echo htmlspecialchars($sc['description']); ?></p>

<?php foreach ($stations_data as $station_sn => $station_info): ?>
    <h3>Station: [<?php echo $station_sn; ?>] <?php echo htmlspecialchars($station_info['name']); ?></h3>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Temperature (°C)</th>
                <th>Humidity (%)</th>
                <th>Pressure</th>
                <th>Light</th>
                <th>Gas</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($station_info['measurements'] as $dp): ?>
            <tr>
                <td><?php echo $dp['timestamp']; ?></td>
                <td><?php echo $dp['temperature']; ?></td>
                <td><?php echo $dp['humidity']; ?></td>
                <td><?php echo $dp['pressure']; ?></td>
                <td><?php echo $dp['light']; ?></td>
                <td><?php echo $dp['gas']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

</div>
<?php endwhile; ?>

</div>
</body>
</html>
