<?php
session_start();
require_once __DIR__ . '/../assets/db.php';

header('Content-Type: application/json');

function respond(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

$pdo = getDb();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stationSerial = $_GET['station'] ?? null;

        if (!$stationSerial) {
            respond(400, ['error' => 'station parameter is required']);
        }

        // Ensure the station exists (no authentication enforced)
        $stationCheck = $pdo->prepare('SELECT pk_serialNumber FROM station WHERE pk_serialNumber = :sn');
        $stationCheck->execute([':sn' => $stationSerial]);
        if (!$stationCheck->fetch()) {
            respond(404, ['error' => 'Station not found']);
        }

        // Get measurements (unauthenticated access)
        $stmt = $pdo->prepare(
            'SELECT pk_measurement, temperature, humidity, pressure, light, gas, timestamp 
             FROM measurement 
             WHERE fk_station_records = :station 
             ORDER BY timestamp DESC'
        );
        $stmt->execute([':station' => $stationSerial]);
        $measurements = $stmt->fetchAll();
        respond(200, ['status' => 'ok', 'measurements' => $measurements]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, ['error' => 'Method not allowed. Use GET to retrieve or POST to insert.']);
    }

    // Extract inputs from POST
    $stationSerial = trim($_POST['station_serial'] ?? '');
    $timestampInput = $_POST['timestamp'] ?? null;
    $temperature = $_POST['temperature'] ?? null;
    $humidity = $_POST['humidity'] ?? null;
    $pressure = $_POST['pressure'] ?? null;
    $light = $_POST['light'] ?? null;
    $gas = $_POST['gas'] ?? null;

    // Validate required fields
    $errors = [];

    if (!$stationSerial) {
        $errors[] = 'station_serial is required.';
    }

    // Parse timestamp
    $timestamp = null;
    if ($timestampInput) {
        $timestamp = DateTime::createFromFormat('Y-m-d H:i:s.u', $timestampInput)
                 ?: DateTime::createFromFormat('Y-m-d H:i:s', $timestampInput)
                 ?: DateTime::createFromFormat(DateTime::ATOM, $timestampInput);
    }
    if (!$timestamp) {
        $errors[] = 'timestamp must be valid datetime.';
    }

    foreach ([
        'temperature' => $temperature,
        'humidity' => $humidity,
        'pressure' => $pressure,
        'light' => $light,
        'gas' => $gas,
    ] as $field => $value) {
        if ($value === null || $value === '') {
            $errors[] = "$field is required.";
        } elseif (!is_numeric($value)) {
            $errors[] = "$field must be numeric.";
        }
    }

    if ($errors) {
        respond(400, ['error' => 'Validation failed', 'details' => $errors]);
    }

    // Ensure the station exists (no authentication enforced)
    $stmt = $pdo->prepare('SELECT pk_serialNumber FROM station WHERE pk_serialNumber = :sn');
    $stmt->execute([':sn' => $stationSerial]);
    if (!$stmt->fetch()) {
        respond(404, ['error' => 'Station not found']);
    }

    // Insert measurement
    $stmt = $pdo->prepare(
        'INSERT INTO measurement (temperature, humidity, pressure, light, gas, timestamp, fk_station_records)
         VALUES (:temperature, :humidity, :pressure, :light, :gas, :timestamp, :station)'
    );

    $stmt->execute([
        ':temperature' => $temperature,
        ':humidity' => $humidity,
        ':pressure' => $pressure,
        ':light' => $light,
        ':gas' => $gas,
        ':timestamp' => $timestamp->format('Y-m-d H:i:s'),
        ':station' => $stationSerial,
    ]);

    respond(201, ['status' => 'ok', 'message' => 'Measurement stored', 'measurementId' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    respond(500, ['error' => 'Database error', 'detail' => $e->getMessage()]);
}

