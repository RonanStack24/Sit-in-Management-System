<?php
// =========================================================
// DATABASE MIGRATION - Create Sit-in Sessions Table
// Run this once to set up the database
// =========================================================

require 'db.php';

try {
    // Check if sitin_sessions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'sitin_sessions'");
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        // Create sitin_sessions table
        $pdo->exec("
            CREATE TABLE sitin_sessions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                student_id INT NOT NULL,
                entry_time DATETIME NOT NULL,
                exit_time DATETIME NULL,
                purpose VARCHAR(255),
                duration_minutes INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                INDEX idx_student (student_id),
                INDEX idx_entry_time (entry_time)
            )
        ");
        echo "✓ Table 'sitin_sessions' created successfully!<br>";
    } else {
        echo "✓ Table 'sitin_sessions' already exists.<br>";
    }

    // Check if admin table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    $admin_exists = $stmt->rowCount() > 0;
    
    if (!$admin_exists) {
        // Create admin_users table
        $pdo->exec("
            CREATE TABLE admin_users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✓ Table 'admin_users' created successfully!<br>";
        
        // Insert default admin (username: admin, password: admin123)
        $default_password = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO admin_users (username, password, email) VALUES ('admin', '$default_password', 'admin@ccs.edu')");
        echo "✓ Default admin user created (username: admin, password: admin123)<br>";
    } else {
        echo "✓ Table 'admin_users' already exists.<br>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green; margin-top: 20px;'>✓ Database Setup Complete!</h2>";
    echo "<p>You can now access the admin dashboard at: <a href='admin_dashboard.php'>/admin_dashboard.php</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
}
?>
