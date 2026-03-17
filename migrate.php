<?php
// =========================================================
// DATABASE MIGRATION SCRIPT
// Run this once to add the profile_photo column if needed
// =========================================================

require 'db.php';

try {
    // Check if profile_photo column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'profile_photo'");
    $column_exists = $stmt->rowCount() > 0;
    
    if (!$column_exists) {
        // Add profile_photo column
        $pdo->exec("ALTER TABLE students ADD COLUMN profile_photo VARCHAR(255) NULL AFTER id");
        echo "✓ Column 'profile_photo' added successfully!";
    } else {
        echo "✓ Column 'profile_photo' already exists.";
    }
    
    // Also ensure sessions_left column exists (if not already there)
    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'sessions_left'");
    $sessions_exists = $stmt->rowCount() > 0;
    
    if (!$sessions_exists) {
        $pdo->exec("ALTER TABLE students ADD COLUMN sessions_left INT DEFAULT 30 AFTER profile_photo");
        echo "<br>✓ Column 'sessions_left' added successfully!";
    } else {
        echo "<br>✓ Column 'sessions_left' already exists.";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
