<?php

session_start();
require_once 'config.php';

$error = '';
$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username' AND password = MD5('$password')";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Web KAMSIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="lock-icon">🔓</div>
                <h1>Login</h1>
                <p class="subtitle">Versi Tanpa Keamanan</p>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)"><span
                                class="material-symbols-outlined">visibility</span></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="material-symbols-outlined">login</span> Login
                </button>
            </form>

            <div class="card-footer">
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                <p><a href="comment.php"><span class="material-symbols-outlined">comment</span> Lihat Komentar (Tanpa
                        Login)</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<span class="material-symbols-outlined">visibility_off</span>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<span class="material-symbols-outlined">visibility</span>';
            }
        }
    </script>
</body>

</html>