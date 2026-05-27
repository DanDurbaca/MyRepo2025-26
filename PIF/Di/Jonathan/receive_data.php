<?php
// receive_data.php - Endpoint for station telemetry (accepts POST/GET and stores measurements)
require_once 'config.php';

// Normalize and collect incoming parameters from POST or GET for DB insertion
$logFile = '/tmp/station_data.log';
$data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'station_serial' => $_POST['station_serial'] ?? $_GET['station_serial'] ?? null,
    'temperature' => $_POST['temperature'] ?? $_GET['temperature'] ?? null,
    'humidity' => $_POST['humidity'] ?? $_GET['humidity'] ?? null,
    'pressure' => $_POST['pressure'] ?? $_GET['pressure'] ?? null,
    'light' => $_POST['light'] ?? $_GET['light'] ?? null,
    'gas' => $_POST['gas'] ?? $_GET['gas'] ?? null
];

// Append JSON-line entry to debug log for auditing incoming telemetry
file_put_contents($logFile, json_encode($data) . "\n", FILE_APPEND);

// Insert measurement using a PDO prepared statement (prevents SQL injection)
try {
    global $host, $dbname, $username, $password;
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO measurement (fk_station_records, timestamp, temperature, humidity, pressure, light, gas)
            VALUES (:station_serial, :timestamp, :temperature, :humidity, :pressure, :light, :gas)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':station_serial' => $data['station_serial'],
        ':timestamp' => $data['timestamp'],
        ':temperature' => $data['temperature'],
        ':humidity' => $data['humidity'],
        ':pressure' => $data['pressure'],
        ':light' => $data['light'],
        ':gas' => $data['gas']
    ]);

    echo "Data inserted successfully into database\n";
    
} catch (PDOException $e) {
    file_put_contents($logFile, "DB Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
