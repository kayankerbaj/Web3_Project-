<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'medical_platform');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    if ($conn->connect_error) {
        throw new Exception("Connection failed");
    }
} catch (Exception $e) {
    die(json_encode(['error' => 'Database error']));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireAuth($role = null) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    if ($role && isset($_SESSION['role']) && $_SESSION['role'] !== $role) {
        jsonResponse(['error' => 'Forbidden'], 403);
    }
}

function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
