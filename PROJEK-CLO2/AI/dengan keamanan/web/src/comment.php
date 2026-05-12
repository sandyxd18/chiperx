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
            $stmt->execute([
                ':nama' => $namaCheck['sanitized'],
                ':komentar' => $komentarCheck['sanitized'],
            ]);
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
    <title>Komentar - Secure Web KAMSIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="lock-icon">💬</div>
                <h1>Komentar Publik</h1>
                <p class="subtitle">Versi Dengan Keamanan | Tidak perlu login</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?php echo sanitizeOutput($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo sanitizeOutput($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" id="nama" name="nama" placeholder="Masukkan nama Anda" maxlength="50" required>
                    <small class="input-hint">Maks. 50 karakter | Hindari simbol SQL</small>
                </div>
                <div class="form-group">
                    <label for="komentar">Komentar</label>
                    <textarea id="komentar" name="komentar" rows="4" placeholder="Tulis komentar Anda..."
                        maxlength="500" required></textarea>
                    <small class="input-hint">Maks. 500 karakter | Input divalidasi & disanitasi</small>
                </div>
                <button type="submit" class="btn btn-primary"><span class="material-symbols-outlined">comment</span>
                    Kirim Komentar</button>
            </form>

            <div class="security-badges">
                <span class="badge badge-green">🛡️ Anti XSS</span>
                <span class="badge badge-green">🛡️ Anti SQLi</span>
                <span class="badge badge-green">📏 Length Limit</span>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h2><span class="material-symbols-outlined">list_alt</span> Daftar Komentar</h2>
            </div>
            <div class="comments-list">
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $row): ?>
                        <div class="comment-item">
                            <div class="comment-author"><?php echo sanitizeOutput($row['nama']); ?></div>
                            <div class="comment-text"><?php echo sanitizeOutput($row['komentar']); ?></div>
                            <div class="comment-date"><?php echo sanitizeOutput($row['created_at']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-comments">Belum ada komentar. Jadilah yang pertama!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-footer" style="text-align: center; margin-top: 20px;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php"><span class="material-symbols-outlined">home</span> Dashboard</a> |
            <?php endif; ?>
            <a href="login.php"><span class="material-symbols-outlined">login</span> Login</a> | <a
                href="register.php"><span class="material-symbols-outlined">person_add</span> Register</a>
        </div>
    </div>
</body>

</html>