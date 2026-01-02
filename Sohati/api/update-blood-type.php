<?php
require_once __DIR__ . '/../config/database.php';
requireAuth('patient');

$input = json_decode(file_get_contents('php://input'), true);
$bloodTypeId = intval($input['blood_type_id'] ?? 0);

if (!$bloodTypeId) {
    jsonResponse(['error' => 'Blood type required'], 400);
}

$stmt = $conn->prepare("UPDATE patients SET blood_type_id = ? WHERE user_id = ?");
$stmt->bind_param('ii', $bloodTypeId, $_SESSION['user_id']);
$stmt->execute();

jsonResponse(['success' => true]);
?>
