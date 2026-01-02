<?php
/*this script retrieves the most recent 100 appointments with patient, doctor, 
specialty, and status info, and returns them as JSON for the admin.*/
require_once __DIR__ . '/../config/database.php';
requireAuth('admin');

$result = $conn->query("
    SELECT a.id, a.appointment_date, a.appointment_time, a.commission,
           p_user.username as patient_name,
           d_user.username as doctor_name,
           s.specialty_name,
           ast.status_name, ast.status_color
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users p_user ON p.user_id = p_user.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users d_user ON d.user_id = d_user.id
    LEFT JOIN specialties s ON d.specialty_id = s.id
    JOIN appointment_statuses ast ON a.status_id = ast.id
    ORDER BY a.created_at DESC
    LIMIT 100
");

$appointments = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'data' => $appointments]);
?>
