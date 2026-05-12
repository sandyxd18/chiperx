<?php

require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';

    if (empty($username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username kosong']);
        exit;
    }

    $result = $conn->query("SELECT id FROM users WHERE username = '$username'");

    if ($result && $result->num_rows > 0) {
        echo json_encode(['status' => 'taken']);
    } else {
        echo json_encode(['status' => 'available']);
    }
}
?>