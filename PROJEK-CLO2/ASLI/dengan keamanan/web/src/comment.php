<?php
require_once 'config.php';
require_once 'functions.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $komentar = $_POST['komentar'] ?? '';

    $namaCheck = validateInput($nama, 50, 'name');
    if (!$namaCheck['valid']) {
        $error = "Nama: " . $namaCheck['message'];
    } else {
        $komentarCheck = validateInput($komentar, 500, 'comment');
        if (!$komentarCheck['valid']) {
            $error = "Komentar: " . $komentarCheck['message'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO comments (nama, komentar) VALUES (:nama, :komentar)");
            $stmt->execute([':nama' => $namaCheck['sanitized'], ':komentar' => $komentarCheck['sanitized']]);
            $success = "Komentar berhasil ditambahkan!";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM comments ORDER BY created_at DESC");
$stmt->execute();
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komentar - KAMSIS (Secure)</title>
</head>
<body>
    <h1>Komentar Publik</h1>
    <p><i>Tidak perlu login - Input divalidasi &amp; disanitasi</i></p>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo sanitizeOutput($error); ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?php echo sanitizeOutput($success); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nama:</label><br>
        <input type="text" name="nama" maxlength="50" required><br>
        <small>Maks 50 karakter</small><br><br>
        <label>Komentar:</label><br>
        <textarea name="komentar" rows="4" cols="40" maxlength="500" required></textarea><br>
        <small>Maks 500 karakter</small><br><br>
        <button type="submit">Kirim Komentar</button>
    </form>

    <hr>
    <h2>Daftar Komentar</h2>
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $row): ?>
            <div style="border:1px solid #ccc; padding:8px; margin:5px 0;">
                <b><?php echo sanitizeOutput($row['nama']); ?></b><br>
                <?php echo sanitizeOutput($row['komentar']); ?><br>
                <small><?php echo sanitizeOutput($row['created_at']); ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Belum ada komentar.</p>
    <?php endif; ?>

    <br>
    <a href="login.php">Login</a> | <a href="register.php">Register</a>
</body>
</html>
