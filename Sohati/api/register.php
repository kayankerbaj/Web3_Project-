<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$role = $input['role'] ?? 'patient';
$gender = $input['gender'] ?? 'other';

if (!$username || !$email || !$password) {
    jsonResponse(['error' => 'All fields required'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['error' => 'Invalid email'], 400);
}

if (strlen($password) < 6) {
    jsonResponse(['error' => 'Password must be 6+ characters'], 400);
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    jsonResponse(['error' => 'Email already exists'], 409);
}

try {
    $conn->begin_transaction();
    
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, gender) VALUES (?,?,?,?,?)");
    $stmt->bind_param('sssss', $username, $email, $hash, $role, $gender);
    $stmt->execute();
    $userId = $conn->insert_id;
    
    if ($role === 'patient') {
        $stmt = $conn->prepare("INSERT INTO patients (user_id) VALUES (?)");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    } elseif ($role === 'doctor') {
        $specialtyId = intval($input['specialty_id'] ?? 0) ?: null;
        $experience = intval($input['years_of_experience'] ?? 0);
        $fee = floatval($input['consultation_fee'] ?? 50);
        
        $stmt = $conn->prepare("INSERT INTO doctors (user_id, specialty_id, years_of_experience, consultation_fee) VALUES (?,?,?,?)");
        $stmt->bind_param('iiid', $userId, $specialtyId, $experience, $fee);
        $stmt->execute();
    }
    
    $conn->commit();
    jsonResponse(['success' => true, 'user_id' => $userId]);
} catch (Exception $e) {
    $conn->rollback();
    jsonResponse(['error' => 'Registration failed'], 500);
}
?>
