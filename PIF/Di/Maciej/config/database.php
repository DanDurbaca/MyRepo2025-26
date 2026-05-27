<?php
// database.php
// Base URL of the application for root-relative paths in links, scripts, and CSS (in this case the directory path is not specified since it has been already done it the apache config file)
define('BASE_URL', '');

// Database credentials
define('DB_HOST', 'localhost');     // Database server host
define('DB_USER', 'root');          // Database username
define('DB_PASS', '');              // Database password
define('DB_NAME', 'portableindoorfeedback'); // Database name

// Function to create and return a PDO database connection
function getDbConnection()
{
    try {
        // Create PDO connection using MySQL driver
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );

        // Set error mode to exception to catch database errors
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Return results as associative arrays by default
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $conn; // Return the connection object
    } catch (PDOException $e) {
        // Stop execution and display error message if connection fails
        die("Connection failed: " . $e->getMessage());
    }
}

// Start PHP session if not already started, to manage user login states
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
