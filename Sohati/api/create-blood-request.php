<?php
require_once __DIR__ . '/../config/database.php';
requireAuth('patient');

$input = json_decode(file_get_contents('php://input'), true);
$neededBloodTypeId = intval($input['needed_blood_type_id'] ?? 0);
$quantity = intval($input['quantity_ml'] ?? 500);
$urgency = $input['urgency'] ?? 'medium';
$notes = $input['notes'] ?? '';

if (!$neededBloodTypeId) {
    jsonResponse(['error' => 'Blood type required'], 400);
}

// Get patient
$stmt = $conn->prepare("SELECT id, blood_type_id FROM patients WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    jsonResponse(['error' => 'Patient not found'], 404);
}

$stmt = $conn->prepare("INSERT INTO blood_donations (donor_id, blood_type_id, needed_blood_type_id, quantity_ml, urgency, notes) VALUES (?,?,?,?,?,?)");
$bloodTypeId = $patient['blood_type_id'] ?: 1;
$stmt->bind_param('iiiiss', $patient['id'], $bloodTypeId, $neededBloodTypeId, $quantity, $urgency, $notes);
$stmt->execute();

jsonResponse(['success' => true, 'request_id' => $conn->insert_id]);
?>
