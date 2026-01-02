<?php
require_once __DIR__ . '/../config/database.php';
requireAuth('admin');

$stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM patients");
$stats['patients'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status='approved'");
$stats['doctors'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status='pending'");
$stats['pending_doctors'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE appointment_date >= CURDATE()");
$stats['upcoming_appointments'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT SUM(commission) as total FROM appointments WHERE status_id = 3");
$stats['total_revenue'] = floatval($result->fetch_assoc()['total'] ?? 0);

jsonResponse(['success' => true, 'data' => $stats]);
?>
