<?php
require_once 'config/database.php';

echo "<h2>Creating Admin Account</h2>";

// Delete existing admin
$conn->query("DELETE FROM users WHERE email = 'admin@sohati.com'");

// Create new admin
$username = 'Admin';
$email = 'admin@sohati.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);
$role = 'admin';
$gender = 'male';

$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, gender) VALUES (?,?,?,?,?)");
$stmt->bind_param('sssss', $username, $email, $hash, $role, $gender);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✅ Admin account created successfully!</p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> admin@sohati.com</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "<li><strong>Hash:</strong> $hash</li>";
    echo "</ul>";
    echo "<p><a href='test_login.php'>Test Login Now</a></p>";
} else {
    echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
}
?>
