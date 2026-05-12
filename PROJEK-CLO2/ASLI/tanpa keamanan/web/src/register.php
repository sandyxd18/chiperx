<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Password tidak cocok!";
    } else {
        $check = $conn->query("SELECT * FROM users WHERE username = '$username'");
        if ($check && $check->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $md5_password = md5($password);
            $query = "INSERT INTO users (username, password) VALUES ('$username', '$md5_password')";
            if ($conn->query($query)) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Gagal mendaftar: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KAMSIS</title>
</head>
<body>
    <h1>Register</h1>
    <p><i>Versi Tanpa Keamanan</i></p>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <label>Konfirmasi Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>
        <button type="submit">Daftar</button>
    </form>

    <br>
    <p>Sudah punya akun? <a href="login.php">Login</a></p>
</body>
</html>
