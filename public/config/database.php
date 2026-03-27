<?php
$host = 'localhost';
$dbname = 'ndrc_nestle';
$username = 'root';
$password = '';

// Define Base URL for absolute links
// If your project is in htdocs/Nestle_NDRC-, then use '/Nestle_NDRC-/'
define('BASE_URL', '/Nestle_NDRC-/');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
