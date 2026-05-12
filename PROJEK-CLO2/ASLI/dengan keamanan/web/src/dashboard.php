<?php
session_start();
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KAMSIS (Secure)</title>
</head>
<body>
    <h1>Dashboard</h1>
    <p>Selamat datang, <?php echo sanitizeOutput($_SESSION['username']); ?>!</p>

    <h3>Status Keamanan</h3>
    <ul>
        <li>Koneksi: HTTPS (SSL/TLS)</li>
        <li>Password: bcrypt + Auto Salt</li>
        <li>Transmisi: SHA-256 Hash sebelum kirim</li>
        <li>SQL Query: Prepared Statements (PDO)</li>
        <li>Input: Validasi panjang + filter SQL</li>
        <li>Output: htmlspecialchars() (anti XSS)</li>
        <li>Brute Force: Cooldown 10/15/30 menit</li>
        <li>IP Block: fail2ban aktif</li>
    </ul>

    <a href="comment.php">Halaman Komentar</a> |
    <a href="logout.php">Logout</a>
</body>
</html>
