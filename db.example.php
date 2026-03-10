<?php
// =========================================================
// DATABASE CONNECTION — EXAMPLE FILE
//
// 1. Copy this file and rename it to db.php
// 2. Fill in your own database credentials below
// 3. Never commit db.php — it is listed in .gitignore
// =========================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'sit_in_db');      // your database name
define('DB_USER',    'root');           // your MySQL username
define('DB_PASS',    '');               // your MySQL password
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('DB Connection Error: ' . $e->getMessage());
    die('Database connection failed. Please check your db.php configuration.');
}
