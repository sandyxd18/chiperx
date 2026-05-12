<?php
require_once 'config.php';

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $komentar = $_POST['komentar'];

    $query = "INSERT INTO comments (nama, komentar) VALUES ('$nama', '$komentar')";

    if ($conn->query($query)) {
        $success = "Komentar berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan komentar: " . $conn->error;
    }
}

$comments = $conn->query("SELECT * FROM comments ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komentar - Web KAMSIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="lock-icon">💬</div>
                <h1>Komentar Publik</h1>
                <p class="subtitle">Versi Tanpa Keamanan - Tidak perlu login</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" id="nama" name="nama" placeholder="Masukkan nama Anda" required>
                </div>
                <div class="form-group">
                    <label for="komentar">Komentar</label>
                    <textarea id="komentar" name="komentar" rows="4" placeholder="Tulis komentar Anda..."
                        required></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><span class="material-symbols-outlined">comment</span>
                    Kirim Komentar</button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h2><span class="material-symbols-outlined">list_alt</span> Daftar Komentar</h2>
            </div>
            <div class="comments-list">
                <?php if ($comments && $comments->num_rows > 0): ?>
                    <?php while ($row = $comments->fetch_assoc()): ?>
                        <div class="comment-item">
                            <div class="comment-author"><?php echo $row['nama']; ?></div>
                            <div class="comment-text"><?php echo $row['komentar']; ?></div>
                            <div class="comment-date"><?php echo $row['created_at']; ?></div>
                        </div>
                    <?php endwhile; ?>
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