<?php











session_start();
require_once 'config.php';
require_once 'functions.php';

$error = '';
$cooldown_msg = '';
$client_ip = getClientIP();

$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);


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
    }
    
    elseif (strlen($password) !== 64 || !ctype_xdigit($password)) {
        $error = 'Data login tidak valid.';
    }
    else {
        
        $usernameHash = hashUsername($usernameCheck['sanitized']);

        
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
        $stmt->execute([':username' => $usernameHash]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password_hash'])) {
            
            session_regenerate_id(true); 
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $usernameCheck['sanitized']; 

            
            recordLoginAttempt($pdo, $client_ip, $usernameHash, true);
            resetLoginAttempts($pdo, $client_ip);

            header("Location: dashboard.php");
            exit();
        } else {
            
            $error = "Username atau password salah!";
            recordLoginAttempt($pdo, $client_ip, $usernameHash, false);
            logFailedLogin($client_ip, $usernameHash);

            
            $bruteCheck = checkBruteForce($pdo, $client_ip);
            if (!$bruteCheck['allowed']) {
                $cooldown_msg = $bruteCheck['message'];
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
    <title>Login - Secure Web KAMSIS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="lock-icon">🔒</div>
                <h1>Secure Login</h1>
                <p class="subtitle">Versi Dengan Keamanan | HTTPS + Hashing + Salt</p>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success">
                    ✅ <?php echo sanitizeOutput($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if ($cooldown_msg): ?>
                <div class="alert alert-warning">
                    ⏳ <?php echo sanitizeOutput($cooldown_msg); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    ⚠️ <?php echo sanitizeOutput($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" <?php echo $cooldown_msg ? 'class="form-disabled"' : ''; ?>>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           placeholder="Masukkan username"
                           maxlength="50"
                           pattern="[a-zA-Z0-9_]+"
                           title="Hanya huruf, angka, dan underscore"
                           required
                           <?php echo $cooldown_msg ? 'disabled' : ''; ?>>
                    <small class="input-hint">Maks. 50 karakter, hanya huruf/angka/underscore</small>
                </div>
                <div class="form-group">
                    <label for="password_input">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password_input"
                               placeholder="Masukkan password"
                               maxlength="128"
                               required
                               <?php echo $cooldown_msg ? 'disabled' : ''; ?>>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_input', this)" <?php echo $cooldown_msg ? 'disabled' : ''; ?>><span class="material-symbols-outlined">visibility</span></button>
                    </div>
                    
                    <input type="hidden" id="password" name="password">
                    <small class="input-hint">Maks. 128 karakter | Di-hash SHA-256 sebelum dikirim</small>
                </div>
                <button type="submit" class="btn btn-primary" <?php echo $cooldown_msg ? 'disabled' : ''; ?>>
                    <span class="material-symbols-outlined">login</span> Login
                </button>
            </form>

            <div class="security-badges">
                <span class="badge badge-green">🔒 HTTPS</span>
                <span class="badge badge-green">🧂 Salt</span>
                <span class="badge badge-green">🔑 SHA-256</span>
                <span class="badge badge-green">🛡️ Anti SQLi</span>
            </div>

            <div class="card-footer">
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                <p><a href="comment.php"><span class="material-symbols-outlined">comment</span> Lihat Komentar (Tanpa Login)</a></p>
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

    
    
    
    
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const passwordInput = document.getElementById('password_input');
        const passwordHidden = document.getElementById('password');
        const password = passwordInput.value;

        if (!password) return;

        
        const encoder = new TextEncoder();
        const data = encoder.encode(password);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

        
        passwordHidden.value = hashHex;
        
        passwordInput.value = '';
        passwordInput.removeAttribute('name');

        
        this.submit();
    });
    </script>
</body>
</html>
