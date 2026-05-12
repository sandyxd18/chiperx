<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $usernameCheck = validateInput($username, 50, 'username');
    if (!$usernameCheck['valid']) {
        $error = $usernameCheck['message'];
    } elseif (strlen($password) !== 64 || !ctype_xdigit($password)) {
        $error = 'Data registrasi tidak valid.';
    } elseif ($password !== $confirm) {
        $error = 'Password tidak cocok!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $usernameCheck['sanitized']]);
        if ($stmt->fetch()) {
            $error = "Username sudah digunakan!";
        } else {
            $bcryptHash = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
            $stmt->execute([':username' => $usernameCheck['sanitized'], ':password_hash' => $bcryptHash]);
            $success = "Registrasi berhasil! Silakan login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KAMSIS (Secure)</title>
</head>
<body>
    <h1>Register</h1>
    <p><i>Versi Dengan Keamanan - bcrypt + Salt</i></p>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo sanitizeOutput($error); ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?php echo sanitizeOutput($success); ?></p>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <label>Username:</label><br>
        <input type="text" name="username" maxlength="50" pattern="[a-zA-Z0-9_]+" required><br>
        <small>Maks 50 karakter, hanya huruf/angka/underscore</small><br><br>

        <label>Password:</label><br>
        <input type="password" id="password_input" maxlength="128" required><br>
        <input type="hidden" id="password" name="password">
        <small>Di-hash SHA-256 sebelum dikirim</small><br><br>

        <label>Konfirmasi Password:</label><br>
        <input type="password" id="confirm_input" maxlength="128" required><br>
        <input type="hidden" id="confirm_password" name="confirm_password"><br><br>

        <button type="submit">Daftar</button>
    </form>

    <br>
    <p>Sudah punya akun? <a href="login.php">Login</a></p>

    <script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const pi = document.getElementById('password_input');
        const ci = document.getElementById('confirm_input');
        const ph = document.getElementById('password');
        const ch = document.getElementById('confirm_password');
        if (!pi.value || !ci.value) return;
        if (pi.value !== ci.value) { alert('Password tidak cocok!'); return; }
        const enc = new TextEncoder();
        const pb = await crypto.subtle.digest('SHA-256', enc.encode(pi.value));
        const cb = await crypto.subtle.digest('SHA-256', enc.encode(ci.value));
        ph.value = Array.from(new Uint8Array(pb)).map(b => b.toString(16).padStart(2,'0')).join('');
        ch.value = Array.from(new Uint8Array(cb)).map(b => b.toString(16).padStart(2,'0')).join('');
        pi.value = ''; ci.value = '';
        pi.removeAttribute('name'); ci.removeAttribute('name');
        this.submit();
    });
    </script>
</body>
</html>
