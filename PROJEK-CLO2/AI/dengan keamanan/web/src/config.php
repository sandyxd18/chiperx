<?php

$db_host = getenv('DB_HOST') ?: 'db-server';
$db_name = getenv('DB_NAME') ?: 'kamsis_db';
$db_user = getenv('DB_USER') ?: 'kamsis_user';
$db_pass = getenv('DB_PASS') ?: 'K4ms1s_S3cur3_P@ss!';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
?>