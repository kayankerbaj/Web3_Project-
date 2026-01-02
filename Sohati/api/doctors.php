<?php
require_once __DIR__ . '/../config/database.php';

$specialtyId = isset($_GET['specialty_id']) ? intval($_GET['specialty_id']) : 0;

$sql = "SELECT d.id, d.user_id, u.username, u.email, u.phone, u.profile_image,
               d.years_of_experience, d.consultation_fee, d.bio, d.rating,
               s.specialty_name, s.id as specialty_id
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        LEFT JOIN specialties s ON d.specialty_id = s.id
        WHERE d.status = 'approved' AND u.is_active = 1";

if ($specialtyId > 0) {
    $sql .= " AND d.specialty_id = " . $specialtyId;
}

$sql .= " ORDER BY d.rating DESC, d.years_of_experience DESC LIMIT 50";

$result = $conn->query($sql);
$doctors = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'data' => $doctors]);
?>
