<?php
error_reporting(0);
header('Content-Type: application/json');
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'patient';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    if ($role === 'patient') {
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.notes,
                a.duration_minutes,
                u.username as doctor_name,
                u.email as doctor_email,
                u.id as doctor_user_id,
                s.specialty_name,
                ast.status_name,
                ast.status_color
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            LEFT JOIN specialties s ON d.specialty_id = s.id
            JOIN appointment_statuses ast ON a.status_id = ast.id
            WHERE p.user_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute([$user_id]);
        
    } else if ($role === 'doctor') {
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.notes,
                a.duration_minutes,
                u.username as patient_name,
                u.email as patient_email,
                u.id as patient_user_id,
                ast.status_name,
                ast.status_color
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            JOIN patients p ON a.patient_id = p.id
            JOIN users u ON p.user_id = u.id
            JOIN appointment_statuses ast ON a.status_id = ast.id
            WHERE d.user_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute([$user_id]);
        
    } else {
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.notes,
                a.duration_minutes,
                u1.username as patient_name,
                u2.username as doctor_name,
                s.specialty_name,
                ast.status_name,
                ast.status_color
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u1 ON p.user_id = u1.id
            JOIN users u2 ON d.user_id = u2.id
            LEFT JOIN specialties s ON d.specialty_id = s.id
            JOIN appointment_statuses ast ON a.status_id = ast.id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute();
    }
    
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $appointments
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
