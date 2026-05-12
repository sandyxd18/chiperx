<?php

session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        $check = $conn->query("SELECT * FROM users WHERE username = '$username'");

        if ($check && $check->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $md5_password = md5($password);

            $query = "INSERT INTO users (username, password) VALUES ('$username', '$md5_password')";

            if ($conn->query($query)) {
                $_SESSION['success_msg'] = "Registrasi berhasil! Silakan login.";
                header("Location: login.php");
                exit();
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
    <title>Register - Web KAMSIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="lock-icon">📝</div>
                <h1>Register</h1>
                <p class="subtitle">Versi Tanpa Keamanan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                        <span class="username-status" id="username-status"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Masukkan password"
                            minlength="8" maxlength="12" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)"><span
                                class="material-symbols-outlined">visibility</span></button>
                    </div>
                    <ul class="password-requirements" id="password-requirements">
                        <li id="req-length" class="invalid">❌ 8-12 karakter</li>
                        <li id="req-upper" class="invalid">❌ Huruf besar</li>
                        <li id="req-lower" class="invalid">❌ Huruf kecil</li>
                        <li id="req-number" class="invalid">❌ Angka</li>
                        <li id="req-symbol" class="invalid">❌ Simbol (!@#$%^&*)</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Ulangi password" minlength="8" maxlength="12" required>
                        <button type="button" class="toggle-password"
                            onclick="togglePassword('confirm_password', this)"><span
                                class="material-symbols-outlined">visibility</span></button>
                    </div>
                    <small class="input-hint" id="match-message"></small>
                </div>
                <button type="submit" class="btn btn-primary"><span class="material-symbols-outlined">person_add</span>
                    Daftar</button>
            </form>

            <div class="card-footer">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
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

        let debounceTimer;
        document.getElementById('username').addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const username = this.value;
            const statusSpan = document.getElementById('username-status');

            if (username.length === 0) {
                statusSpan.className = 'username-status';
                statusSpan.textContent = '';
                return;
            }

            statusSpan.className = 'username-status';
            statusSpan.textContent = '⏳';

            debounceTimer = setTimeout(async () => {
                try {
                    const response = await fetch('check_username.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ username })
                    });
                    const result = await response.json();

                    if (result.status === 'available') {
                        statusSpan.className = 'username-status available';
                        statusSpan.textContent = '✅';
                    } else if (result.status === 'taken') {
                        statusSpan.className = 'username-status taken';
                        statusSpan.textContent = '❌';
                    }
                } catch (err) {
                    statusSpan.textContent = '';
                }
            }, 500);
        });

        const passInput = document.getElementById('password');
        const confInput = document.getElementById('confirm_password');
        const matchMsg = document.getElementById('match-message');

        function checkMatch() {
            if (confInput.value === '') {
                matchMsg.textContent = '';
                return;
            }
            if (passInput.value === confInput.value) {
                matchMsg.textContent = '✅ Password cocok';
                matchMsg.style.color = 'var(--success)';
            } else {
                matchMsg.textContent = '❌ Password tidak cocok';
                matchMsg.style.color = 'var(--danger)';
            }
        }

        passInput.addEventListener('input', checkMatch);
        confInput.addEventListener('input', checkMatch);

        passInput.addEventListener('input', function () {
            const val = this.value;

            if (val.length >= 8 && val.length <= 12) {
                document.getElementById('req-length').className = 'valid';
                document.getElementById('req-length').textContent = '✅ 8-12 karakter';
            } else {
                document.getElementById('req-length').className = 'invalid';
                document.getElementById('req-length').textContent = '❌ 8-12 karakter';
            }

            if (/[A-Z]/.test(val)) {
                document.getElementById('req-upper').className = 'valid';
                document.getElementById('req-upper').textContent = '✅ Huruf besar';
            } else {
                document.getElementById('req-upper').className = 'invalid';
                document.getElementById('req-upper').textContent = '❌ Huruf besar';
            }

            if (/[a-z]/.test(val)) {
                document.getElementById('req-lower').className = 'valid';
                document.getElementById('req-lower').textContent = '✅ Huruf kecil';
            } else {
                document.getElementById('req-lower').className = 'invalid';
                document.getElementById('req-lower').textContent = '❌ Huruf kecil';
            }

            if (/[0-9]/.test(val)) {
                document.getElementById('req-number').className = 'valid';
                document.getElementById('req-number').textContent = '✅ Angka';
            } else {
                document.getElementById('req-number').className = 'invalid';
                document.getElementById('req-number').textContent = '❌ Angka';
            }

            if (/[!@#$%^&*]/.test(val)) {
                document.getElementById('req-symbol').className = 'valid';
                document.getElementById('req-symbol').textContent = '✅ Simbol (!@#$%^&*)';
            } else {
                document.getElementById('req-symbol').className = 'invalid';
                document.getElementById('req-symbol').textContent = '❌ Simbol (!@#$%^&*)';
            }
        });
    </script>
</body>

</html>