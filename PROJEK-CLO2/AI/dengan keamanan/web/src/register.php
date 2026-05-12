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
    }
    
    elseif (strlen($password) !== 64 || !ctype_xdigit($password)) {
        $error = 'Data registrasi tidak valid.';
    }
    
    elseif ($password !== $confirm) {
        $error = 'Password tidak cocok!';
    }
    else {
        
        $usernameHash = hashUsername($usernameCheck['sanitized']);

        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $usernameHash]);

        if ($stmt->fetch()) {
            $error = "Username sudah digunakan!";
        } else {
            
            $bcryptHash = hashPassword($password);

            
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
            $stmt->execute([
                ':username' => $usernameHash,
                ':password_hash' => $bcryptHash,
            ]);

            $_SESSION['success_msg'] = "Registrasi berhasil! Silakan login.";
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Secure Web KAMSIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="lock-icon">📝</div>
                <h1>Secure Register</h1>
                <p class="subtitle">Versi Dengan Keamanan | bcrypt + Salt</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?php echo sanitizeOutput($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo sanitizeOutput($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username"
                               placeholder="Masukkan username"
                               maxlength="50"
                               pattern="[a-zA-Z0-9_]+"
                               title="Hanya huruf, angka, dan underscore"
                               required>
                        <span class="username-status" id="username-status"></span>
                    </div>
                    <small class="input-hint">Maks. 50 karakter, hanya huruf/angka/underscore</small>
                </div>
                <div class="form-group">
                    <label for="password_input">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password_input"
                               placeholder="Masukkan password"
                               minlength="8" maxlength="12"
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,12}"
                               title="Harus 8-12 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol (!@#$%^&*)"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_input', this)"><span class="material-symbols-outlined">visibility</span></button>
                    </div>
                    <input type="hidden" id="password" name="password">
                    <ul class="password-requirements" id="password-requirements">
                        <li id="req-length" class="invalid">❌ 8-12 karakter</li>
                        <li id="req-upper" class="invalid">❌ Huruf besar</li>
                        <li id="req-lower" class="invalid">❌ Huruf kecil</li>
                        <li id="req-number" class="invalid">❌ Angka</li>
                        <li id="req-symbol" class="invalid">❌ Simbol (!@#$%^&*)</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="confirm_input">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm_input"
                               placeholder="Ulangi password"
                               minlength="8" maxlength="12"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_input', this)"><span class="material-symbols-outlined">visibility</span></button>
                    </div>
                    <input type="hidden" id="confirm_password" name="confirm_password">
                    <small class="input-hint" id="match-message"></small>
                </div>
                <button type="submit" class="btn btn-primary"><span class="material-symbols-outlined">person_add</span> Daftar</button>
            </form>

            <div class="security-badges">
                <span class="badge badge-green">🧂 Auto Salt</span>
                <span class="badge badge-green">🔑 bcrypt</span>
                <span class="badge badge-green">🛡️ Validated</span>
            </div>

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
    document.getElementById('username').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const username = this.value;
        const statusSpan = document.getElementById('username-status');
        
        if (username.length === 0) {
            statusSpan.className = 'username-status';
            statusSpan.textContent = '';
            return;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            statusSpan.className = 'username-status taken';
            statusSpan.textContent = '❌';
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

    
    const passInput = document.getElementById('password_input');
    const confInput = document.getElementById('confirm_input');
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

    
    passInput.addEventListener('input', function() {
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

    
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const passwordInput = document.getElementById('password_input');
        const confirmInput = document.getElementById('confirm_input');
        const passwordHidden = document.getElementById('password');
        const confirmHidden = document.getElementById('confirm_password');

        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (!password || !confirm) return;

        if (password !== confirm) {
            let errorDiv = document.getElementById('js-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.id = 'js-error';
                errorDiv.className = 'alert alert-danger';
                const form = document.getElementById('registerForm');
                form.parentNode.insertBefore(errorDiv, form);
            }
            errorDiv.innerHTML = '⚠️ Password tidak cocok!';
            errorDiv.style.display = 'block';
            window.scrollTo(0, 0);
            return;
        } else {
            const errorDiv = document.getElementById('js-error');
            if (errorDiv) errorDiv.style.display = 'none';
        }

        
        const encoder = new TextEncoder();

        const passData = encoder.encode(password);
        const passBuffer = await crypto.subtle.digest('SHA-256', passData);
        const passHash = Array.from(new Uint8Array(passBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');

        const confData = encoder.encode(confirm);
        const confBuffer = await crypto.subtle.digest('SHA-256', confData);
        const confHash = Array.from(new Uint8Array(confBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');

        passwordHidden.value = passHash;
        confirmHidden.value = confHash;

        passwordInput.value = '';
        confirmInput.value = '';
        passwordInput.removeAttribute('name');
        confirmInput.removeAttribute('name');

        this.submit();
    });
    </script>
</body>
</html>
