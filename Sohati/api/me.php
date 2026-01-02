<?php
require_once __DIR__ . '/../config/database.php';
requireAuth();

$stmt = $conn->prepare("SELECT id, username, email, role, phone, profile_image FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

jsonResponse(['success' => true, 'data' => $user]);
?>
