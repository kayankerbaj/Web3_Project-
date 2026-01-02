<?php
require_once __DIR__ . '/../config/database.php';
requireAuth('patient');

$input = json_decode(file_get_contents('php://input'), true);
$doctorId = intval($input['doctor_id'] ?? 0);
$date = $input['date'] ?? '';
$time = $input['time'] ?? '';
$duration = intval($input['duration'] ?? 60);
$notes = $input['notes'] ?? '';

if (!$doctorId || !$date || !$time) {
    jsonResponse(['error' => 'Doctor, date and time required'], 400);
}

// Get patient ID
$stmt = $conn->prepare("SELECT id FROM patients WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    jsonResponse(['error' => 'Patient not found'], 404);
}

// Check for conflicts
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments 
    WHERE doctor_id = ? AND appointment_date = ? 
    AND status_id IN (1,2)
    AND (
        (? BETWEEN appointment_time AND ADDTIME(appointment_time, SEC_TO_TIME(duration_minutes * 60)))
        OR (ADDTIME(?, SEC_TO_TIME(? * 60)) BETWEEN appointment_time AND ADDTIME(appointment_time, SEC_TO_TIME(duration_minutes * 60)))
        OR (appointment_time BETWEEN ? AND ADDTIME(?, SEC_TO_TIME(? * 60)))
    )");
$stmt->bind_param('isssiisi', $doctorId, $date, $time, $time, $duration, $time, $time, $duration);
$stmt->execute();
$conflict = $stmt->get_result()->fetch_assoc();

if ($conflict['count'] > 0) {
    jsonResponse(['error' => 'Time slot already booked'], 409);
}

try {
    $conn->begin_transaction();
    
    // Get doctor fee
    $stmt = $conn->prepare("SELECT consultation_fee FROM doctors WHERE id = ?");
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();
    
    // Get commission percentage
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'commission_percentage'");
    $stmt->execute();
    $commissionPct = floatval($stmt->get_result()->fetch_assoc()['setting_value']);
    
    // Calculate commission
    $commission = ($doctor['consultation_fee'] * $commissionPct) / 100;
    
    // Book appointment
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, duration_minutes, notes, commission) VALUES (?,?,?,?,?,?,?)");
    $patientId = $patient['id'];
    $stmt->bind_param('iississ', $patientId, $doctorId, $date, $time, $duration, $notes, $commission);
    $stmt->execute();
    $appointmentId = $conn->insert_id;
    
    // Log commission
    $stmt = $conn->prepare("INSERT INTO commission_log (appointment_id, commission_amount, commission_percentage) VALUES (?,?,?)");
    $stmt->bind_param('idd', $appointmentId, $commission, $commissionPct);
    $stmt->execute();
    
    $conn->commit();
    jsonResponse(['success' => true, 'appointment_id' => $appointmentId]);
    
} catch (Exception $e) {
    $conn->rollback();
    jsonResponse(['error' => 'Booking failed'], 500);
}
?>
