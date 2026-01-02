<?php
require_once __DIR__ . '/../config/database.php';
requireAuth();

$otherUserId = intval($_GET['user_id'] ?? 0);

if (!$otherUserId) {
    jsonResponse(['error' => 'User ID required'], 400);
}

$stmt = $conn->prepare("
    SELECT m.id, m.sender_id, m.receiver_id, m.message_text, m.created_at,
           sender.username as sender_name
    FROM messages m
    JOIN users sender ON m.sender_id = sender.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
    LIMIT 100
");
$userId = $_SESSION['user_id'];
$stmt->bind_param('iiii', $userId, $otherUserId, $otherUserId, $userId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Mark as read
$stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
$stmt->bind_param('ii', $userId, $otherUserId);
$stmt->execute();

jsonResponse(['success' => true, 'data' => $messages]);
?>
