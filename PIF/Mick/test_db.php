<?php
// test_db.php - lightweight DB connection tester
require_once __DIR__ . '/db.php';
try {
    $m = db_connect();
    echo "Connected OK to MySQL: " . htmlspecialchars($m->host_info);
} catch (Throwable $e) {
    http_response_code(500);
    echo "Connection failed: " . htmlspecialchars($e->getMessage());
}

?>
