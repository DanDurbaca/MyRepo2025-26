<?php
// api/submit.php
// Accepts JSON or form POST from a station and inserts a measurement into `measurement` table.
// Required fields: serial, secret, temperature, humidity, pressure, light, gas
// Optional: timestamp (ISO or MySQL DATETIME)

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../inc/log.php';

// DB-backed rate limiter (per-IP) using `rate_limits` table
// If Redis is available in your environment, consider replacing this with a Redis atomic counter.
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$limit = 60; // max requests per window
$window = 60; // seconds

// Try Redis-backed limiter first if available/configured
$useRedis = false;
$redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
$redisPort = getenv('REDIS_PORT') ?: 6379;
if (class_exists('Redis')) {
    try {
        $redis = new Redis();
        $redis->connect($redisHost, intval($redisPort), 1);
        $useRedis = true;
    } catch (Exception $e) {
        error_log('Redis connect failed: ' . $e->getMessage());
        $useRedis = false;
    }
}

if ($useRedis) {
    try {
        $key = 'pif_rl_' . hash('sha256', $ip);
        $count = $redis->incr($key);
        if ($count === 1) {
            $redis->expire($key, $window);
        }
        if ($count > $limit) {
            http_response_code(429);
            echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
            exit;
        }
    } catch (Exception $e) {
        error_log('Redis rate limiter error: ' . $e->getMessage());
        // fall through to DB-based limiter below
    }
} 
// If Redis not used or failed, fall back to DB-backed limiter
{
    $now = time();
    $window_start = intval(floor($now / $window) * $window);
    $key = hash('sha256', $ip);
    try {
        // Attempt atomic upsert: increment count if same window, otherwise reset to 1 for new window
        $sql = "INSERT INTO rate_limits (`k`, window_start, cnt) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE cnt = IF(window_start = VALUES(window_start), cnt + 1, 1), window_start = VALUES(window_start)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$key, $window_start]);
        // Now fetch current count for this key
        $chk = $pdo->prepare('SELECT cnt, window_start FROM rate_limits WHERE `k` = ? LIMIT 1');
        $chk->execute([$key]);
        $row = $chk->fetch();
        if ($row && intval($row['window_start']) === $window_start && intval($row['cnt']) > $limit) {
            http_response_code(429);
            echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
            exit;
        }
    } catch (PDOException $e) {
        // If rate-limiter table is missing or DB error, fall back to permissive behavior but log the issue
        error_log('Rate limiter DB error: ' . $e->getMessage());
    }
}

$input = null;
// Get JSON body if present
$raw = file_get_contents('php://input');
if ($raw) {
    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $input = $json;
    }
}
// Fallback to $_POST
if (!$input) {
    $input = $_POST;
}

// Optional API token enforcement: if `$api_shared_secret` is set in config.php,
// require the token be provided via header `X-API-KEY` or form/json field `api_key`.
if (!empty($api_shared_secret)) {
    $provided = '';
    if (!empty($_SERVER['HTTP_X_API_KEY'])) {
        $provided = $_SERVER['HTTP_X_API_KEY'];
    } elseif (!empty($input['api_key'])) {
        $provided = $input['api_key'];
    } elseif (!empty($input['secret'])) {
        // legacy: allow `secret` field for stations if configured
        $provided = $input['secret'];
    }
    if (!is_string($provided) || !hash_equals((string)$api_shared_secret, (string)$provided)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Invalid API token']);
        exit;
    }
}

$required = ['station_serial', 'temperature', 'humidity', 'pressure', 'light', 'gas'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => "Missing field: $field"]);
        exit;
    }
}

$serial = trim($input['station_serial']);
$secret = isset($input['secret']) ? trim($input['secret']) : '';
$temperature = (float)$input['temperature'];
$humidity = (float)$input['humidity'];
$pressure = (float)$input['pressure'];
$light = (float)$input['light'];
$gas = (float)$input['gas'];
// Normalize and validate timestamp: accept ISO 8601 or MySQL DATETIME; normalize to UTC 'Y-m-d H:i:s'
$timestamp_raw = isset($input['timestamp']) && $input['timestamp'] ? trim($input['timestamp']) : null;
if ($timestamp_raw) {
    try {
        // Try to parse with DateTimeImmutable which understands ISO 8601 with timezone
        $dt = new DateTimeImmutable($timestamp_raw);
        $dt = $dt->setTimezone(new DateTimeZone('UTC'));
        $timestamp = $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid timestamp format']);
        exit;
    }
} else {
    // default to server time (UTC)
    $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
}

try {
    // Fetch station by serial to verify it exists
    $stmt = $pdo->prepare('SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ? LIMIT 1');
    $stmt->execute([$serial]);
    $station = $stmt->fetch();
    if (!$station) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Invalid station serial']);
        exit;
    }

    // Skip authentication as per user requirement

    // Server-side validation: sensor value ranges
    $errors = [];
    if ($temperature < -50 || $temperature > 60) $errors[] = 'temperature out of range';
    if ($humidity < 0 || $humidity > 100) $errors[] = 'humidity out of range';
    if ($pressure < 300 || $pressure > 1100) $errors[] = 'pressure out of range';
    if ($light < 0 || $light > 100000) $errors[] = 'light out of range';
    if ($gas < 0 || $gas > 10000) $errors[] = 'gas out of range';
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Validation failed', 'details' => $errors]);
        exit;
    }

    // Insert measurement
    $ins = $pdo->prepare('INSERT INTO measurement (temperature, humidity, pressure, light, gas, timestamp, fk_station_records) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $ins->execute([$temperature, $humidity, $pressure, $light, $gas, $timestamp, $serial]);

    $insertId = $pdo->lastInsertId();
    echo json_encode(['ok' => true, 'insert_id' => $insertId]);
    // structured log
    app_log('info', 'measurement_insert', ['serial' => $serial, 'insert_id' => $insertId, 'temperature' => $temperature, 'humidity' => $humidity]);
} catch (PDOException $e) {
    error_log('API submit error: ' . $e->getMessage());
    app_log('error', 'measurement_insert_failed', ['error' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}

?>