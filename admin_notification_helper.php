<?php
/**
 * Admin Notification Helper Functions
 * Used to generate notifications for admin when system events occur
 */

/**
 * Create a notification for a specific admin
 */
function createAdminNotification($pdo, $admin_id, $type, $title, $message, $related_id = null) {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO admin_notifications (admin_id, type, title, message, related_id, is_read)
            VALUES (?, ?, ?, ?, ?, FALSE)
        ');
        return $stmt->execute([$admin_id, $type, $title, $message, $related_id]);
    } catch (PDOException $e) {
        error_log('Failed to create admin notification: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for all admins
 */
function notifyAllAdmins($pdo, $type, $title, $message, $related_id = null) {
    try {
        // Get all admins
        $stmt = $pdo->query('SELECT id FROM admin_users');
        $admins = $stmt->fetchAll();
        
        $success = true;
        foreach ($admins as $admin) {
            $created = createAdminNotification($pdo, $admin['id'], $type, $title, $message, $related_id);
            if (!$created) {
                $success = false;
            }
        }
        return $success;
    } catch (PDOException $e) {
        error_log('Failed to notify all admins: ' . $e->getMessage());
        return false;
    }
}

/**
 * Notify admins about new reservation
 */
function notifyNewReservation($pdo, $reservation_id, $student_name, $lab_name, $reservation_date, $start_time) {
    try {
        $date = new DateTime($reservation_date . ' ' . $start_time);
        $formatted_time = $date->format('M d, Y h:i A');
        
        $title = '📅 New Reservation';
        $message = "{$student_name} has requested a reservation for {$lab_name} on {$formatted_time}. Action required.";
        
        return notifyAllAdmins($pdo, 'Reservation', $title, $message, $reservation_id);
    } catch (Exception $e) {
        error_log('Failed to notify new reservation: ' . $e->getMessage());
        return false;
    }
}

/**
 * Notify admins about new feedback
 */
function notifyNewFeedback($pdo, $feedback_id, $student_name, $category, $rating) {
    try {
        $star_rating = str_repeat('⭐', $rating);
        
        $title = '💬 New Feedback Received';
        $message = "{$student_name} submitted {$category} feedback with {$star_rating} ({$rating}/5 stars).";
        
        return notifyAllAdmins($pdo, 'Feedback', $title, $message, $feedback_id);
    } catch (Exception $e) {
        error_log('Failed to notify new feedback: ' . $e->getMessage());
        return false;
    }
}

/**
 * Notify admins about new sit-in session
 */
function notifyNewSitin($pdo, $sitin_id, $student_name, $lab_name, $purpose) {
    try {
        $title = '🪑 New Sit-in Session Started';
        $message = "{$student_name} has started a sit-in in {$lab_name} for: {$purpose}";
        
        return notifyAllAdmins($pdo, 'Sit-in', $title, $message, $sitin_id);
    } catch (Exception $e) {
        error_log('Failed to notify new sit-in: ' . $e->getMessage());
        return false;
    }
}

/**
 * Clear old admin notifications
 */
function cleanupOldAdminNotifications($pdo) {
    try {
        // Get admins with more than 100 notifications
        $stmt = $pdo->query('
            SELECT admin_id, COUNT(*) as count 
            FROM admin_notifications 
            GROUP BY admin_id 
            HAVING count > 100
        ');
        $admins = $stmt->fetchAll();
        
        foreach ($admins as $admin) {
            // Delete oldest read notifications
            $stmt = $pdo->prepare('
                DELETE FROM admin_notifications 
                WHERE admin_id = ? AND is_read = TRUE
                ORDER BY created_at ASC
                LIMIT ?
            ');
            $stmt->execute([
                $admin['admin_id'],
                $admin['count'] - 100
            ]);
        }
        return true;
    } catch (PDOException $e) {
        error_log('Cleanup old admin notifications error: ' . $e->getMessage());
        return false;
    }
}
?>
