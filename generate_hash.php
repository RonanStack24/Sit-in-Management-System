<?php
// Generate bcrypt hash for "admin123"
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<h2>🔐 Generate Admin Password Hash</h2>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Hash:</strong></p>";
echo "<code style='background: #f0f0f0; padding: 10px; display: block; word-break: break-all;'>" . htmlspecialchars($hash) . "</code>";
echo "<p style='color: green; margin-top: 20px;'><strong>✅ Use this in your SQL:</strong></p>";
echo "<pre style='background: #f0f0f0; padding: 10px;'>UPDATE admin_users SET password = '" . $hash . "' WHERE username = 'admin';</pre>";
?>
