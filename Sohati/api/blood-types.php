<?php
require_once __DIR__ . '/../config/database.php';

$result = $conn->query("SELECT * FROM blood_types ORDER BY blood_type");
$bloodTypes = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'data' => $bloodTypes]);
?>
