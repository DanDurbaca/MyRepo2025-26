<?php
// Short: Quick DB connection/test utility for local troubleshooting.
// test_db.php - Simple script to test database connection
require_once 'config.php';

// Attempt a trivial SELECT to validate DB connectivity and credentials
try {
    // Try to query the database
    $stmt = $pdo->query("SELECT 1");
    echo "Database connection successful!";
} catch (PDOException $e) {
    // Log full error and show generic message
    error_log('Test DB connection failed: ' . $e->getMessage());
    echo "Database connection failed. See server logs for details.";
}
?>