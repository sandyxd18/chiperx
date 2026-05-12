<?php
session_start();
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
    <title>Dashboard - KAMSIS</title>
</head>
<body>
    <h1>Dashboard</h1>
    <p>Selamat datang, <?php echo $_SESSION['username']; ?>!</p>

    <h3>Informasi Keamanan</h3>
    <ul>
        <li>Koneksi: HTTP (tidak terenkripsi)</li>
        <li>Password: MD5 tanpa salt</li>
        <li>SQL Query: String concatenation</li>
        <li>Output: Tanpa sanitasi (rentan XSS)</li>
        <li>Brute Force: Tidak ada proteksi</li>
    </ul>

    <a href="comment.php">Halaman Komentar</a> |
    <a href="logout.php">Logout</a>
</body>
</html>
