<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    jsonResponse(['error' => 'Email and password required'], 400);
}

$stmt = $conn->prepare("SELECT id, username, email, password_hash, role FROM users WHERE email = ? AND is_active = 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonResponse(['error' => 'Invalid credentials'], 401);
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password_hash'])) {
    jsonResponse(['error' => 'Invalid credentials'], 401);
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

jsonResponse(['success' => true, 'role' => $user['role'], 'username' => $user['username']]);
?>
