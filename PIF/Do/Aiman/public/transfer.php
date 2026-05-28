<?php

$host = "localhost";
$dbname = "pif_db";
$username = "pif_user";
$password = "AyaAi172";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Only POST requests are allowed.");
}

$station_serial = $_POST["station_serial"] ?? "";
$timestamp = $_POST["timestamp"] ?? "";
$temperature = $_POST["temperature"] ?? null;
$humidity = $_POST["humidity"] ?? null;
$pressure = $_POST["pressure"] ?? null;
$light = $_POST["light"] ?? null;
$gas = $_POST["gas"] ?? null;

if ($station_serial === "" || $timestamp === "") {
    die("Missing required data.");
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT station_id
        FROM stations
        WHERE serial_number = ?
    ");
    $stmt->execute([$station_serial]);

    $station = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$station) {
        die("Station not found.");
    }

    $station_id = $station["station_id"];

    $stmt = $pdo->prepare("
        INSERT INTO measurements
        (station_id, measured_at, temperature, humidity, pressure, light, gas)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $station_id,
        $timestamp,
        $temperature,
        $humidity,
        $pressure,
        (int)$light,
        (int)$gas,
    ]);

    echo "Data stored successfully.";
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

