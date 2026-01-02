<?php
require_once __DIR__ . '/../config/database.php';
requireAuth('admin');

$input = json_decode(file_get_contents('php://input'), true);
$doctorId = intval($input['doctor_id'] ?? 0);
$action = $input['action'] ?? '';

if (!$doctorId || !in_array($action, ['approved', 'rejected'])) {
    jsonResponse(['error' => 'Invalid request'], 400);
}

$stmt = $conn->prepare("UPDATE doctors SET status = ?, job_applied = 1 WHERE id = ?");
$stmt->bind_param('si', $action, $doctorId);
$stmt->execute();

jsonResponse(['success' => true]);
?>
