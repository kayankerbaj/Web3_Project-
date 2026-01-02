<?php
require_once __DIR__ . '/../config/database.php';
requireAuth('patient');
//It lets  PHP code read JSON data from the request and use it like a normal array.
$input = json_decode(file_get_contents('php://input'), true);
$appointmentId = intval($input['appointment_id'] ?? 0);

if (!$appointmentId) {
    jsonResponse(['error' => 'Appointment ID required'], 400);
}

// Verify ownership
$stmt = $conn->prepare("SELECT a.id FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.id = ? AND p.user_id = ?");
$stmt->bind_param('ii', $appointmentId, $_SESSION['user_id']);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    jsonResponse(['error' => 'Appointment not found'], 404);
}

$stmt = $conn->prepare("UPDATE appointments SET status_id = 4 WHERE id = ?");
$stmt->bind_param('i', $appointmentId);
$stmt->execute();

jsonResponse(['success' => true]);
?>
