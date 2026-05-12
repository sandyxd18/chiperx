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
    <title>Dashboard - Web KAMSIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="lock-icon">🏠</div>
                <h1>Dashboard</h1>
                <p class="subtitle">Versi Tanpa Keamanan</p>
            </div>

            <div class="dashboard-content">
                <div class="welcome-box">
                    <h2>Selamat datang, <?php echo $_SESSION['username']; ?>!</h2>
                    <p>Anda berhasil login ke sistem.</p>
                </div>

                <div class="info-box">
                    <h3>ℹ️ Informasi Keamanan</h3>
                    <ul>
                        <li>❌ Koneksi: <strong>HTTP</strong> (tidak terenkripsi)</li>
                        <li>❌ Password: <strong>MD5</strong> tanpa salt</li>
                        <li>❌ Transmisi: <strong>Plain text</strong></li>
                        <li>❌ SQL Query: <strong>String concatenation</strong></li>
                        <li>❌ Input: <strong>Tanpa validasi</strong></li>
                        <li>❌ Output: <strong>Tanpa sanitasi</strong> (rentan XSS)</li>
                        <li>❌ Brute Force: <strong>Tidak ada proteksi</strong></li>
                        <li>❌ IP Block: <strong>Tidak ada</strong></li>
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