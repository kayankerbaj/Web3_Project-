<?php
require_once __DIR__ . '/../config/database.php';

$result = $conn->query("
    SELECT bt.id, bt.blood_type, 
           COUNT(bd.id) as request_count,
           SUM(bd.quantity_ml) as total_quantity
    FROM blood_donations bd
    JOIN blood_types bt ON bd.needed_blood_type_id = bt.id
    WHERE bd.status = 'requested'
    GROUP BY bt.id, bt.blood_type
    ORDER BY request_count DESC
");

$requests = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'data' => $requests]);
?>
