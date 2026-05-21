<?php
// Database configuration - UPDATE THESE FOR YOUR SCHOOL VM
$db_host = 'localhost';           // Change if MySQL is remote
$db_name = 'portableindoorfeedback';
$db_user = 'cerle025';            // Your MySQL username
$db_pass = 'leandro06$$';         // Your MySQL password

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>
