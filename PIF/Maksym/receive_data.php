<?php
// receive_data.php for PIF — Pi station data ingest API endpoint.
// Accepts POST with: station_serial, timestamp, temperature, humidity, pressure, light, gas
// Always returns JSON.

// Connection to DB
$host = 'database';
$db   = 'portableindoorfeedback';
$user = 'pif_user';
$pass = 'pif_password';
$conn = mysqli_connect($host, $user, $pass, $db);
if ($conn) {
    mysqli_set_charset($conn, 'utf8mb4');
}

header('Content-Type: application/json');

if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit();
}

$station_serial = isset($_POST['station_serial']) ? trim($_POST['station_serial']) : '';
$timestamp      = isset($_POST['timestamp'])      ? trim($_POST['timestamp'])      : '';
$temperature    = isset($_POST['temperature'])    ? $_POST['temperature']          : null;
$humidity       = isset($_POST['humidity'])       ? $_POST['humidity']             : null;
$pressure       = isset($_POST['pressure'])       ? $_POST['pressure']             : null;
$light          = isset($_POST['light'])          ? $_POST['light']                : null;
$gas            = isset($_POST['gas'])            ? $_POST['gas']                  : null;

$errors = [];
if ($station_serial === '') $errors[] = 'station_serial required';
if ($timestamp === '')      $errors[] = 'timestamp required';
if (!is_numeric($temperature)) $errors[] = 'temperature must be numeric';
if (!is_numeric($humidity))    $errors[] = 'humidity must be numeric';
if (!is_numeric($pressure))    $errors[] = 'pressure must be numeric';
if (!is_numeric($light))       $errors[] = 'light must be numeric';
if (!is_numeric($gas))         $errors[] = 'gas must be numeric';

// Normalize timestamp to MySQL DATETIME (accepts ISO formats with/without T)
$tsFormatted = '';
if ($timestamp !== '') {
    $t = strtotime(str_replace('T', ' ', $timestamp));
    if ($t === false) {
        $errors[] = 'timestamp format invalid';
    } else {
        $tsFormatted = date('Y-m-d H:i:s', $t);
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit();
}

// Verify station exists
$stmt = $conn->prepare("SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?");
$stmt->bind_param("s", $station_serial);
$stmt->execute();
$result = $stmt->get_result();
$stationExists = $result->num_rows > 0;
$stmt->close();

if (!$stationExists) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Unknown station serial']);
    exit();
}

// Insert measurement
$stmt = $conn->prepare("
    INSERT INTO measurement
        (temperature, humidity, pressure, light, gas, timestamp, fk_station_records)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$tempF = (float)$temperature;
$humF  = (float)$humidity;
$prsF  = (float)$pressure;
$lgtF  = (float)$light;
$gasF  = (float)$gas;
$stmt->bind_param("dddddss", $tempF, $humF, $prsF, $lgtF, $gasF, $tsFormatted, $station_serial);

if ($stmt->execute()) {
    $newID = $conn->insert_id;
    $stmt->close();
    echo json_encode(['status' => 'success', 'id' => $newID]);
    exit();
} else {
    $stmt->close();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error during insert']);
    exit();
}
?>

