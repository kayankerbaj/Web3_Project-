<?php
require_once __DIR__ . '/../config/database.php';
requireAuth('admin');

$result = $conn->query("
    SELECT d.id, d.user_id, u.username, u.email, u.phone,
           d.years_of_experience, d.consultation_fee, d.bio,
           s.specialty_name
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    LEFT JOIN specialties s ON d.specialty_id = s.id
    WHERE d.status = 'pending'
    ORDER BY d.id DESC
");

$doctors = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'data' => $doctors]);
?>
