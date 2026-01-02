<?php
require_once __DIR__ . '/../config/database.php';
requireAuth();

$userId = $_SESSION['user_id'];

$result = $conn->query("
    SELECT DISTINCT
        CASE WHEN m.sender_id = $userId THEN m.receiver_id ELSE m.sender_id END as other_user_id,
        u.username, u.role,
        (SELECT message_text FROM messages m2 
         WHERE (m2.sender_id = $userId AND m2.receiver_id = other_user_id) 
            OR (m2.sender_id = other_user_id AND m2.receiver_id = $userId)
         ORDER BY m2.created_at DESC LIMIT 1) as last_message,
        (SELECT COUNT(*) FROM messages m3 
         WHERE m3.sender_id = other_user_id AND m3.receiver_id = $userId AND m3.is_read = 0) as unread_count
    FROM messages m
    JOIN users u ON u.id = CASE WHEN m.sender_id = $userId THEN m.receiver_id ELSE m.sender_id END
    WHERE m.sender_id = $userId OR m.receiver_id = $userId
    ORDER BY (SELECT created_at FROM messages m2 
              WHERE (m2.sender_id = $userId AND m2.receiver_id = other_user_id) 
                 OR (m2.sender_id = other_user_id AND m2.receiver_id = $userId)
              ORDER BY m2.created_at DESC LIMIT 1) DESC
");

$conversations = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'data' => $conversations]);
?>
