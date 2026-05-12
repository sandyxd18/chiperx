<?php

function validateInput($input, $maxLength, $type = 'general') {
    $result = ['valid' => true, 'message' => '', 'sanitized' => ''];

    if (strpos($input, "\0") !== false) {
        return ['valid' => false, 'message' => 'Input mengandung karakter tidak valid.', 'sanitized' => ''];
    }
    if (strlen($input) === 0) {
        return ['valid' => false, 'message' => 'Input tidak boleh kosong.', 'sanitized' => ''];
    }

    if (strlen($input) > $maxLength) {
        return ['valid' => false, 'message' => "Input terlalu panjang. Maksimal $maxLength karakter.", 'sanitized' => ''];
    }

    switch ($type) {
        case 'username':
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $input)) {
                return ['valid' => false, 'message' => 'Username hanya boleh mengandung huruf, angka, dan underscore.', 'sanitized' => ''];
            }
            $result['sanitized'] = $input;
            break;

        case 'password':
            $result['sanitized'] = $input;
            break;

        case 'name':
        case 'comment':
            $sql_patterns = [
                '/(\bunion\b)/i',
                '/(\bselect\b)/i',
                '/(\bdrop\b)/i',
                '/(\bdelete\b)/i',
                '/(\binsert\b)/i',
                '/(\bupdate\b)/i',
                '/(\balter\b)/i',
                '/(\bexec\b)/i',
                '/(\bexecute\b)/i',
                '/(\btruncate\b)/i',
                '/(\bcreate\b)/i',
                '/(--|#|\/\*|\*\/)/',
                '/(\bor\b\s+\d+\s*=\s*\d+)/i',
                '/(\band\b\s+\d+\s*=\s*\d+)/i',
                '/(;\s*$)/',
                '/(\bchar\s*\()/i',
                '/(\bconcat\s*\()/i',
                '/(\bbenchmark\s*\()/i',
                '/(\bsleep\s*\()/i',
            ];

            foreach ($sql_patterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    return ['valid' => false, 'message' => 'Input mengandung karakter atau kata yang tidak diizinkan.', 'sanitized' => ''];
                }
            }

            $result['sanitized'] = trim($input);
            break;

        default:
            $result['sanitized'] = trim($input);
            break;
    }

    return $result;
}

function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function checkBruteForce($pdo, $ip) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM login_attempts
        WHERE ip_address = :ip
        AND success = 0
        AND attempted_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ");
    $stmt->execute([':ip' => $ip]);
    $totalFailed = $stmt->fetch()['total'];

    if ($totalFailed < 5) {
        return ['allowed' => true, 'message' => '', 'attempts' => $totalFailed];
    }
    $stmt = $pdo->prepare("
        SELECT attempted_at
        FROM login_attempts
        WHERE ip_address = :ip
        AND success = 0
        ORDER BY attempted_at DESC
        LIMIT 1
    ");
    $stmt->execute([':ip' => $ip]);
    $lastAttempt = $stmt->fetch()['attempted_at'];
    $lastTime = strtotime($lastAttempt);
    $now = time();
    $elapsed = $now - $lastTime;

    if ($totalFailed >= 5 && $totalFailed < 10) {
        $cooldown = 600;
        $cooldownNum = 1;
    }
    elseif ($totalFailed >= 10 && $totalFailed < 15) {
        $cooldown = 900;
        $cooldownNum = 2;
    }
    elseif ($totalFailed >= 15 && $totalFailed < 20) {
        $cooldown = 1800;
        $cooldownNum = 3;
    }
    else {
        return [
            'allowed' => false,
            'message' => 'Akun Anda telah diblokir karena terlalu banyak percobaan login gagal. Hubungi administrator.',
            'attempts' => $totalFailed
        ];
    }

    if ($elapsed < $cooldown) {
        $remaining = $cooldown - $elapsed;
        $mins = ceil($remaining / 60);
        return [
            'allowed' => false,
            'message' => "Terlalu banyak percobaan login gagal (cooldown ke-$cooldownNum). Coba lagi dalam $mins menit. (Total gagal: $totalFailed)",
            'attempts' => $totalFailed
        ];
    }

    return ['allowed' => true, 'message' => '', 'attempts' => $totalFailed];
}

function recordLoginAttempt($pdo, $ip, $username, $success) {
    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (ip_address, username, success)
        VALUES (:ip, :username, :success)
    ");
    $stmt->execute([
        ':ip' => $ip,
        ':username' => $username,
        ':success' => $success ? 1 : 0,
    ]);
}


function logFailedLogin($ip, $username) {
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "FAILED_LOGIN ip=$ip user=$username at=$timestamp\n";
    file_put_contents('/var/log/auth-login.log', $logLine, FILE_APPEND | LOCK_EX);
}

function resetLoginAttempts($pdo, $ip) {
    $stmt = $pdo->prepare("
        DELETE FROM login_attempts
        WHERE ip_address = :ip
    ");
    $stmt->execute([':ip' => $ip]);
}


function getClientIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}


function hashUsername($username) {
    return hash('sha256', $username);
}