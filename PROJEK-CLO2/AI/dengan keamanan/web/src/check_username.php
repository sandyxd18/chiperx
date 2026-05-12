<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';

    if (empty($username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username kosong']);
        exit;
    }

    $usernameCheck = validateInput($username, 50, 'username');
    if (!$usernameCheck['valid']) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid']);
        exit;
    }

    $usernameHash = hashUsername($usernameCheck['sanitized']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $usernameHash]);

    if ($stmt->fetch()) {
        echo json_encode(['status' => 'taken']);
    } else {
        echo json_encode(['status' => 'available']);
    }
}
?>