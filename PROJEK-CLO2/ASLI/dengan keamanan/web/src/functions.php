<?php

function validateInput($input, $maxLength, $type = 'general') {
    if (strpos($input, "\0") !== false) {
        return ['valid' => false, 'message' => 'Input tidak valid.', 'sanitized' => ''];
    }
    if (strlen($input) === 0) {
        return ['valid' => false, 'message' => 'Input tidak boleh kosong.', 'sanitized' => ''];
    }
    if (strlen($input) > $maxLength) {
        return ['valid' => false, 'message' => "Maksimal $maxLength karakter.", 'sanitized' => ''];
    }

    if ($type === 'username') {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $input)) {
            return ['valid' => false, 'message' => 'Username hanya boleh huruf, angka, underscore.', 'sanitized' => ''];
        }
        return ['valid' => true, 'message' => '', 'sanitized' => $input];
    }

    if ($type === 'name' || $type === 'comment') {
        $patterns = ['/(union|select|drop|delete|insert|update|alter|exec|truncate|create)/i', '/(--|#|\/\*|\*\/)/', '/(or\s+\d+=\d+)/i', '/(;\s*$)/'];
        foreach ($patterns as $p) {
            if (preg_match($p, $input)) {
                return ['valid' => false, 'message' => 'Input mengandung karakter tidak diizinkan.', 'sanitized' => ''];
            }
        }
        return ['valid' => true, 'message' => '', 'sanitized' => trim($input)];
    }

    return ['valid' => true, 'message' => '', 'sanitized' => trim($input)];
}


function sanitizeOutput($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}


function checkBruteForce($pdo, $ip) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM login_attempts WHERE ip_address = :ip AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)");
    $stmt->execute([':ip' => $ip]);
    $total = $stmt->fetch()['total'];

    if ($total < 5) return ['allowed' => true, 'message' => '', 'attempts' => $total];

    $stmt = $pdo->prepare("SELECT attempted_at FROM login_attempts WHERE ip_address = :ip AND success = 0 ORDER BY attempted_at DESC LIMIT 1");
    $stmt->execute([':ip' => $ip]);
    $last = strtotime($stmt->fetch()['attempted_at']);
    $elapsed = time() - $last;

    if ($total >= 5 && $total < 10) { $cd = 600; $n = 1; }
    elseif ($total >= 10 && $total < 15) { $cd = 900; $n = 2; }
    elseif ($total >= 15 && $total < 20) { $cd = 1800; $n = 3; }
    else { return ['allowed' => false, 'message' => 'IP diblokir. Hubungi admin.', 'attempts' => $total]; }

    if ($elapsed < $cd) {
        $m = ceil(($cd - $elapsed) / 60);
        return ['allowed' => false, 'message' => "Cooldown ke-$n. Coba lagi dalam $m menit.", 'attempts' => $total];
    }
    return ['allowed' => true, 'message' => '', 'attempts' => $total];
}

function recordLoginAttempt($pdo, $ip, $username, $success) {
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, username, success) VALUES (:ip, :u, :s)");
    $stmt->execute([':ip' => $ip, ':u' => $username, ':s' => $success ? 1 : 0]);
}

function logFailedLogin($ip, $username) {
    $t = date('Y-m-d H:i:s');
    file_put_contents('/var/log/auth-login.log', "FAILED_LOGIN ip=$ip user=$username at=$t\n", FILE_APPEND | LOCK_EX);
}

function resetLoginAttempts($pdo, $ip) {
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = :ip");
    $stmt->execute([':ip' => $ip]);
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
?>
