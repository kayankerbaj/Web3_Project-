<?php
require_once 'config/database.php';

$email = 'admin@carewave.com';
$password = 'admin123';

echo "<h2>Testing Admin Login</h2>";

// Get user from database
$stmt = $conn->prepare("SELECT id, username, email, password_hash, role FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color: red;'>❌ User not found in database!</p>";
    exit;
}

$user = $result->fetch_assoc();

echo "<p style='color: green;'>✅ User found:</p>";
echo "<ul>";
echo "<li>ID: {$user['id']}</li>";
echo "<li>Username: {$user['username']}</li>";
echo "<li>Email: {$user['email']}</li>";
echo "<li>Role: {$user['role']}</li>";
echo "<li>Hash: {$user['password_hash']}</li>";
echo "</ul>";

// Test password verification
if (password_verify($password, $user['password_hash'])) {
    echo "<p style='color: green; font-weight: bold;'>✅ Password verification: SUCCESS</p>";
    echo "<p>Login should work!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Password verification: FAILED</p>";
    echo "<p>Need to reset password hash!</p>";
}
?>
