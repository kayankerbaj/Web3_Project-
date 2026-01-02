<?php
require_once __DIR__ . '/../config/database.php';
requireAuth();

$input = json_decode(file_get_contents('php://input'), true);
$receiverId = intval($input['receiver_id'] ?? 0);
$message = trim($input['message'] ?? '');

if (!$receiverId || !$message) {
    jsonResponse(['error' => 'Receiver and message required'], 400);
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?,?,?)");
$stmt->bind_param('iis', $_SESSION['user_id'], $receiverId, $message);
$stmt->execute();

jsonResponse(['success' => true, 'message_id' => $conn->insert_id]);
?>
