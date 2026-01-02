<?php
session_start();
session_unset();
session_destroy();
header('Content-Type: application/json');
//converts a PHP array into a JSON string.
echo json_encode(['success' => true]);
?>
