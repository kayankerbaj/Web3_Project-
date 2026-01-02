<?php
require_once __DIR__ . '/../config/database.php';

$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$date = $_GET['date'] ?? '';

if (!$doctorId || !$date) {
    jsonResponse(['error' => 'Doctor ID and date required'], 400);
}

// Get day of week
$dayName = date('l', strtotime($date));

// Get doctor schedule
$stmt = $conn->prepare("SELECT start_time, end_time FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1");
$stmt->bind_param('is', $doctorId, $dayName);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

if (!$schedule) {
    jsonResponse(['success' => true, 'available' => false, 'message' => 'Doctor not available on this day']);
}

// Get booked slots
$stmt = $conn->prepare("SELECT appointment_time, duration_minutes FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status_id IN (1,2)");
$stmt->bind_param('is', $doctorId, $date);
$stmt->execute();
$bookedSlots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'available' => true, 'schedule' => $schedule, 'booked_slots' => $bookedSlots]);
?>
