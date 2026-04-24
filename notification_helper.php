<?php
/**
 * Notification Helper Functions
 * Used to generate notifications when system events occur
 */

/**
 * Create a notification for a specific student
 */
function createNotification($pdo, $student_id, $type, $title, $message, $related_id = null) {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO notifications (student_id, type, title, message, related_id, is_read)
            VALUES (?, ?, ?, ?, ?, FALSE)
        ');
        return $stmt->execute([$student_id, $type, $title, $message, $related_id]);
    } catch (PDOException $e) {
        error_log('Failed to create notification: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create notifications for all students (e.g., new announcement)
 */
function notifyAllStudents($pdo, $type, $title, $message, $related_id = null) {
    try {
        // Get all students
        $stmt = $pdo->query('SELECT id FROM students');
        $students = $stmt->fetchAll();
        
        $success = true;
        foreach ($students as $student) {
            $created = createNotification($pdo, $student['id'], $type, $title, $message, $related_id);
            if (!$created) {
                $success = false;
            }
        }
        return $success;
    } catch (PDOException $e) {
        error_log('Failed to notify all students: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for reservation status change
 */
function notifyReservationStatus($pdo, $reservation_id, $student_id, $status) {
    try {
        $stmt = $pdo->prepare('
            SELECT lab_name, reservation_date, start_time 
            FROM lab_reservations 
            WHERE id = ? AND student_id = ?
        ');
        $stmt->execute([$reservation_id, $student_id]);
        $reservation = $stmt->fetch();
        
        if (!$reservation) {
            return false;
        }
        
        $date = new DateTime($reservation['reservation_date'] . ' ' . $reservation['start_time']);
        $formatted_time = $date->format('M d, Y h:i A');
        
        if ($status === 'Approved') {
            $title = '✅ Reservation Approved';
            $message = "Your reservation for {$reservation['lab_name']} on {$formatted_time} has been approved!";
        } elseif ($status === 'Rejected') {
            $title = '❌ Reservation Rejected';
            $message = "Your reservation for {$reservation['lab_name']} on {$formatted_time} was not approved. Please try another time.";
        } else {
            $title = '📋 Reservation Status Update';
            $message = "Your reservation for {$reservation['lab_name']} on {$formatted_time} status: {$status}";
        }
        
        return createNotification($pdo, $student_id, 'Reservation', $title, $message, $reservation_id);
    } catch (PDOException $e) {
        error_log('Failed to notify reservation status: ' . $e->getMessage());
        return false;
    }
}

/**
 * Clear old notifications (optional cleanup, run periodically)
 * Keeps last 100 notifications per student, deletes older read ones
 */
function cleanupOldNotifications($pdo) {
    try {
        // Get students with more than 100 notifications
        $stmt = $pdo->query('
            SELECT student_id, COUNT(*) as count 
            FROM notifications 
            GROUP BY student_id 
            HAVING count > 100
        ');
        $students = $stmt->fetchAll();
        
        foreach ($students as $student) {
            // Delete oldest read notifications
            $stmt = $pdo->prepare('
                DELETE FROM notifications 
                WHERE student_id = ? AND is_read = TRUE
                ORDER BY created_at ASC
                LIMIT ?
            ');
            $stmt->execute([
                $student['student_id'],
                $student['count'] - 100
            ]);
        }
        return true;
    } catch (PDOException $e) {
        error_log('Cleanup old notifications error: ' . $e->getMessage());
        return false;
    }
}
?>
