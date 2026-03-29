<?php
$host = 'localhost';
$dbname = 'ndrc_nestle';
$username = 'root';
$password = '';

/* 
// InfinityFree Production Settings (Use these when hosting online)
$host = "sqlXXX.infinityfree.com"; 
$dbname = "if0_41494385_ndrc_nestle"; 
$username = "if0_41494385"; 
$password = "ddsDCZhT5fPdc"; 
*/

// Define Base URL for absolute links
// Use '/' for production or '/Nestle_NDRC-/' for local XAMPP
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
