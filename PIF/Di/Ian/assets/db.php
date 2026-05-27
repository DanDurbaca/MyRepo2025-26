<?php
// Simple PDO connector for the local MySQL instance.
// Update $dbUser and $dbPass if your credentials differ.

function getDb(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbHost = '127.0.0.1';
    $dbName = 'moria904';
    $dbUser = 'root';
    $dbPass = '';

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}
