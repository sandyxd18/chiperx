<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$error = '';
$cooldown_msg = '';
$client_ip = getClientIP();

$bruteCheck = checkBruteForce($pdo, $client_ip);
if (!$bruteCheck['allowed']) {
    $cooldown_msg = $bruteCheck['message'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bruteCheck['allowed']) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $usernameCheck = validateInput($username, 50, 'username');
    if (!$usernameCheck['valid']) {
        $error = $usernameCheck['message'];
    } elseif (strlen($password) !== 64 || !ctype_xdigit($password)) {
        $error = 'Data login tidak valid.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
        $stmt->execute([':username' => $usernameCheck['sanitized']]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            recordLoginAttempt($pdo, $client_ip, $user['username'], true);
            resetLoginAttempts($pdo, $client_ip);
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Username atau password salah!";
            recordLoginAttempt($pdo, $client_ip, $username, false);
            logFailedLogin($client_ip, $username);
            $bruteCheck = checkBruteForce($pdo, $client_ip);
            if (!$bruteCheck['allowed']) $cooldown_msg = $bruteCheck['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KAMSIS (Secure)</title>
</head>
<body>
    <h1>Login</h1>
    <p><i>Versi Dengan Keamanan - HTTPS + Hashing + Salt</i></p>

    <?php if ($cooldown_msg): ?>
        <p style="color:orange;"><?php echo sanitizeOutput($cooldown_msg); ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo sanitizeOutput($error); ?></p>
    <?php endif; ?>

    <form method="POST" id="loginForm">
        <label>Username:</label><br>
        <input type="text" name="username" maxlength="50" pattern="[a-zA-Z0-9_]+" required <?php echo $cooldown_msg ? 'disabled' : ''; ?>><br>
        <small>Maks 50 karakter, hanya huruf/angka/underscore</small><br><br>

        <label>Password:</label><br>
        <input type="password" id="password_input" maxlength="128" required <?php echo $cooldown_msg ? 'disabled' : ''; ?>><br>
        <input type="hidden" id="password" name="password">
        <small>Di-hash SHA-256 sebelum dikirim</small><br><br>

        <button type="submit" <?php echo $cooldown_msg ? 'disabled' : ''; ?>>Login</button>
    </form>

    <br>
    <p>Belum punya akun? <a href="register.php">Daftar</a></p>
    <p><a href="comment.php">Lihat Komentar (Tanpa Login)</a></p>

    <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const pi = document.getElementById('password_input');
        const ph = document.getElementById('password');
        if (!pi.value) return;
        const data = new TextEncoder().encode(pi.value);
        const buf = await crypto.subtle.digest('SHA-256', data);
        ph.value = Array.from(new Uint8Array(buf)).map(b => b.toString(16).padStart(2,'0')).join('');
        pi.value = '';
        pi.removeAttribute('name');
        this.submit();
    });
    </script>
</body>
</html>
