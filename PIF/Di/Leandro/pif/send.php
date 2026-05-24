<?php
require_once 'config.php';

// Initialize variables
$success_message = '';
$error_message = '';
$stations = [];
$is_api_request = false;

// Get all stations for dropdown
try {
    $stmt = $pdo->query("SELECT pk_serialNumber, name FROM station ORDER BY name");
    $stations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "❌ Error fetching stations: " . $e->getMessage();
}

// Helper function to send JSON response
function sendResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit();
}

// Check if this is an API request from Raspberry Pi
$is_api_request = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
$is_api_request = $is_api_request || (isset($_POST['api_request']) && $_POST['api_request'] === 'true');

// Handle API request from Raspberry Pi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_api_request) {
    // Get data from POST request
    $station_serial = isset($_POST['station_serial']) ? $_POST['station_serial'] : null;
    $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : null;
    $temperature = isset($_POST['temperature']) ? $_POST['temperature'] : null;
    $humidity = isset($_POST['humidity']) ? $_POST['humidity'] : null;
    $pressure = isset($_POST['pressure']) ? $_POST['pressure'] : null;
    $light = isset($_POST['light']) ? $_POST['light'] : null;
    $gas = isset($_POST['gas']) ? $_POST['gas'] : null;

    // Validate required fields
    if (!$station_serial || !$timestamp) {
        sendResponse(false, "Missing required fields: station_serial and timestamp are required");
    }

    // Check if station exists using PDO
    try {
        $check_stmt = $pdo->prepare("SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?");
        $check_stmt->execute([$station_serial]);

        if ($check_stmt->rowCount() == 0) {
            sendResponse(false, "Station with serial number '$station_serial' does not exist");
        }

        // Prepare and execute insert statement using PDO
        $stmt = $pdo->prepare(
            "INSERT INTO measurement (temperature, humidity, pressure, light, gas, timestamp, fk_station_records)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        try {
            $stmt->execute([$temperature, $humidity, $pressure, $light, $gas, $timestamp, $station_serial]);
            sendResponse(true, "Measurement recorded successfully", [
                "id" => $pdo->lastInsertId(),
                "station" => $station_serial,
                "timestamp" => $timestamp
            ]);
        } catch (PDOException $e) {
            sendResponse(false, "Database error: " . $e->getMessage());
        }
    } catch (PDOException $e) {
        sendResponse(false, "Database error: " . $e->getMessage());
    }
}

// Handle web form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_api_request) {
    $temp = isset($_POST['Temperature']) ? $_POST['Temperature'] : null;
    $hum = isset($_POST['Humidity']) ? $_POST['Humidity'] : null;
    $psi = isset($_POST['Pressure']) ? $_POST['Pressure'] : null;
    $lux = isset($_POST['Light']) ? $_POST['Light'] : null;
    $gas = isset($_POST['Gas']) ? $_POST['Gas'] : null;
    $time = isset($_POST['Timestamp']) ? $_POST['Timestamp'] : null;
    $station_serial = isset($_POST['station_serial']) ? $_POST['station_serial'] : null;

    // Validate required fields
    if (!$station_serial) {
        $error_message = "❌ Please select a station";
    } else {
        // Check if station exists
        try {
            $check_stmt = $pdo->prepare("SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?");
            $check_stmt->execute([$station_serial]);

            if ($check_stmt->rowCount() == 0) {
                $error_message = "❌ Station with serial number '$station_serial' does not exist";
            } else {
                // Prepare and execute insert statement
                $stmt = $pdo->prepare(
                    "INSERT INTO measurement (temperature, humidity, pressure, light, gas, timestamp, fk_station_records)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                try {
                    $stmt->execute([$temp, $hum, $psi, $lux, $gas, $time, $station_serial]);
                    $success_message = "✅ New record created successfully";
                } catch (PDOException $e) {
                    $error_message = "❌ Error: " . $e->getMessage();
                }
            }
        } catch (PDOException $e) {
            $error_message = "❌ Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Monitor - Data Entry</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 30px 20px;
        }

        .api-info {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .api-info h3 {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .api-info p {
            color: #555;
            font-size: 12px;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .api-info code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            color: #d63384;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-left: 5px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        select {
            cursor: pointer;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Environment Monitor</h1>
            <p>Indoor Air Quality & Environmental Data Logger</p>
        </div>

        <div class="content">
            <!-- API Info Section -->
            <div class="api-info">
                <h3>🤖 Raspberry Pi Integration</h3>
                <p><strong>API Endpoint:</strong> POST to this same URL with form data/</p>
                <p><strong>Required Parameters:</strong> <code>station_serial</code>, <code>timestamp</code></p>
                <p><strong>Example curl command:</strong></p>
                <code style="display: block; background: white; padding: 10px; margin-top: 5px; border-radius: 3px; font-size: 11px;">
                    curl -X POST http://your-server/send.php \<br/>
                    &nbsp;&nbsp;-d "station_serial=SN-123" \<br/>
                    &nbsp;&nbsp;-d "timestamp=2024-01-15 14:30:00" \<br/>
                    &nbsp;&nbsp;-d "temperature=22.5" \<br/>
                    &nbsp;&nbsp;-d "humidity=45.2" \<br/>
                    &nbsp;&nbsp;-d "pressure=1013.25" \<br/>
                    &nbsp;&nbsp;-d "light=150.5" \<br/>
                    &nbsp;&nbsp;-d "gas=412.3"
                </code>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Manual Data Entry Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="station">
                        Select Station <span class="badge">Required</span>
                    </label>
                    <select name="station_serial" id="station" required>
                        <option value="">Choose a station</option>
                        <?php while($row = array_shift($stations)): ?>
                            <option value="<?php echo htmlspecialchars($row['pk_serialNumber']); ?>">
                                <?php echo htmlspecialchars($row['name']); ?> (<?php echo htmlspecialchars($row['pk_serialNumber']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="temp">🌡️ Temperature (°C)</label>
                    <input type="number" id="temp" name="Temperature" step="any" required placeholder="e.g., 22.5">
                </div>

                <div class="form-group">
                    <label for="hum">💧 Humidity (%)</label>
                    <input type="number" id="hum" name="Humidity" step="any" required placeholder="e.g., 45.2">
                </div>

                <div class="form-group">
                    <label for="psi">🔽 Pressure (hPa)</label>
                    <input type="number" id="psi" name="Pressure" step="any" required placeholder="e.g., 1013.25">
                </div>

                <div class="form-group">
                    <label for="lux">💡 Light (Lux)</label>
                    <input type="number" id="lux" name="Light" step="any" required placeholder="e.g., 150.5">
                </div>

                <div class="form-group">
                    <label for="gas">🌫️ Gas (ppm)</label>
                    <input type="number" id="gas" name="Gas" step="any" required placeholder="e.g., 412.3">
                </div>

                <div class="form-group">
                    <label for="timestamp">📅 Timestamp</label>
                    <input type="datetime-local" id="timestamp" name="Timestamp" required>
                </div>

                <button type="submit">Submit Measurement</button>
            </form>
        </div>
    </div>

    <script>
        // Set default timestamp to current date/time
        document.getElementById('timestamp').valueAsDate = new Date();
    </script>
</body>
</html>
