<?php

$db_host = getenv('DB_HOST') ?: 'db-server';
$db_name = getenv('DB_NAME') ?: 'kamsis_db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: 'root123';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>