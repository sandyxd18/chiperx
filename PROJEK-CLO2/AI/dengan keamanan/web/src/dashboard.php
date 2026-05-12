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
    <title>Dashboard - Secure Web KAMSIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="lock-icon">🏠</div>
                <h1>Dashboard</h1>
                <p class="subtitle">Versi Dengan Keamanan</p>
            </div>

            <div class="dashboard-content">
                <div class="welcome-box">
                    <h2>Selamat datang, <?php echo sanitizeOutput($_SESSION['username']); ?>!</h2>
                    <p>Anda berhasil login ke sistem yang aman.</p>
                </div>

                <div class="info-box info-box-secure">
                    <h3>🛡️ Status Keamanan</h3>
                    <ul>
                        <li>✅ Koneksi: <strong>HTTPS (SSL/TLS)</strong></li>
                        <li>✅ Password: <strong>bcrypt + Auto Salt</strong></li>
                        <li>✅ Transmisi: <strong>SHA-256 Hash sebelum kirim</strong></li>
                        <li>✅ SQL Query: <strong>Prepared Statements (PDO)</strong></li>
                        <li>✅ Input: <strong>Validasi panjang + filter SQL chars</strong></li>
                        <li>✅ Output: <strong>htmlspecialchars() (anti XSS)</strong></li>
                        <li>✅ Brute Force: <strong>Cooldown 10/15/30 menit</strong></li>
                        <li>✅ IP Block: <strong>fail2ban aktif</strong></li>
                    </ul>
                </div>

                <div class="nav-links">
                    <a href="comment.php" class="btn btn-secondary"><span
                            class="material-symbols-outlined">comment</span> Halaman Komentar</a>
                    <a href="logout.php" class="btn btn-danger"><span class="material-symbols-outlined">logout</span>
                        Logout</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>