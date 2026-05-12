<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $komentar = $_POST['komentar'];

    $query = "INSERT INTO comments (nama, komentar) VALUES ('$nama', '$komentar')";
    if ($conn->query($query)) {
        $success = "Komentar berhasil ditambahkan!";
    } else {
        $error = "Gagal: " . $conn->error;
    }
}

$comments = $conn->query("SELECT * FROM comments ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komentar - KAMSIS</title>
</head>
<body>
    <h1>Komentar Publik</h1>
    <p><i>Tidak perlu login</i></p>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nama:</label><br>
        <input type="text" name="nama" required><br><br>
        <label>Komentar:</label><br>
        <textarea name="komentar" rows="4" cols="40" required></textarea><br><br>
        <button type="submit">Kirim Komentar</button>
    </form>

    <hr>
    <h2>Daftar Komentar</h2>
    <?php if ($comments && $comments->num_rows > 0): ?>
        <?php while ($row = $comments->fetch_assoc()): ?>
            <div style="border:1px solid #ccc; padding:8px; margin:5px 0;">
                <b><?php echo $row['nama']; ?></b><br>
                <?php echo $row['komentar']; ?><br>
                <small><?php echo $row['created_at']; ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Belum ada komentar.</p>
    <?php endif; ?>

    <br>
    <a href="login.php">Login</a> | <a href="register.php">Register</a>
</body>
</html>
