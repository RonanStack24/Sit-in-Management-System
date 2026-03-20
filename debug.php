<?php
session_start();
require 'db.php';

echo "<h1>🔧 Login Debug</h1>";

// Test database connection
echo "<h2>1. Database Connection</h2>";
try {
    $test = $pdo->query('SELECT 1');
    echo "<p style='color: green;'>✅ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
}

// Check admin_users table
echo "<h2>2. Admin Users Table</h2>";
$result = $pdo->query('SELECT * FROM admin_users');
$admins = $result->fetchAll();

if (empty($admins)) {
    echo "<p style='color: red;'>❌ NO ADMIN USERS!</p>";
} else {
    foreach ($admins as $admin) {
        echo "<strong>Username:</strong> " . htmlspecialchars($admin['username']) . "<br>";
        echo "<strong>Password Hash:</strong> " . htmlspecialchars($admin['password']) . "<br>";
        echo "<strong>Hash Length:</strong> " . strlen($admin['password']) . " (should be 60)<br>";
        echo "<hr>";
    }
}

// Test password verification
echo "<h2>3. Test Login with 'admin' / 'admin123'</h2>";
$test_username = 'admin';
$test_password = 'admin123';

// Check admin_users
$stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1');
$stmt->execute([$test_username]);
$found = $stmt->fetch();

if ($found) {
    echo "<p style='color: green;'>✅ Found user 'admin' in admin_users</p>";
    echo "<strong>Hash:</strong> " . htmlspecialchars($found['password']) . "<br>";
    
    if (password_verify($test_password, $found['password'])) {
        echo "<p style='color: green;'>✅ Password 'admin123' MATCHES!</p>";
    } else {
        echo "<p style='color: red;'>❌ Password does NOT match</p>";
    }
} else {
    echo "<p style='color: red;'>❌ User 'admin' NOT found in admin_users</p>";
}

// Check students table
echo "<h2>4. Check Students Table</h2>";
$stmt = $pdo->prepare('SELECT id, id_number FROM students WHERE id_number = ? LIMIT 1');
$stmt->execute([$test_username]);
$student = $stmt->fetch();

if ($student) {
    echo "<p>Found student with ID: " . htmlspecialchars($student['id_number']) . "</p>";
} else {
    echo "<p>No student found with ID 'admin'</p>";
}

echo "<h2>5. Database Config Check</h2>";
echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
echo "<p><strong>User:</strong> " . DB_USER . "</p>";
echo "<p><strong>Password:</strong> " . (empty(DB_PASS) ? "(empty)" : "***") . "</p>";
?>

