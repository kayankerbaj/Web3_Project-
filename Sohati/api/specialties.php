<?php
require_once __DIR__ . '/../config/database.php';

$result = $conn->query("SELECT id, specialty_name, description FROM specialties ORDER BY specialty_name");
$specialties = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'data' => $specialties]);
?>
