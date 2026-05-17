<?php
session_start();
require 'db.php';
require 'admin_notification_helper.php';

// Redirect to login if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$current_page = 'manage_reservations';
$message = '';
$message_type = '';

// Handle enable/disable reservations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $student_id = (int)$_POST['student_id'];
    $action = $_POST['action'];

    if ($action === 'toggle') {
        try {
            // Get current status
            $stmt = $pdo->prepare('SELECT reservations_enabled FROM students WHERE id = ?');
            $stmt->execute([$student_id]);
            $student = $stmt->fetch();
            $current_status = $student['reservations_enabled'];

            // Toggle status
            $new_status = !$current_status;
            $stmt = $pdo->prepare('UPDATE students SET reservations_enabled = ? WHERE id = ?');
            $stmt->execute([$new_status, $student_id]);

            $message = $new_status ? 'Reservations enabled for this student.' : 'Reservations disabled for this student.';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error updating reservation status: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Fetch all students with reservation status and active session count
$stmt = $pdo->query('
    SELECT 
        s.id,
        s.first_name,
        s.last_name,
        s.id_number,
        s.course,
        s.reservations_enabled,
        COUNT(DISTINCT lr.id) as pending_reservations,
        COUNT(DISTINCT ss.id) as active_sessions
    FROM students s
    LEFT JOIN lab_reservations lr ON s.id = lr.student_id AND lr.status IN ("Pending", "Approved")
    LEFT JOIN sitin_sessions ss ON s.id = ss.student_id AND (ss.status = "Active" OR ss.exit_time IS NULL)
    GROUP BY s.id, s.first_name, s.last_name, s.id_number, s.course, s.reservations_enabled
    ORDER BY s.first_name ASC
');
$students = $stmt->fetchAll();

// Get summary stats
$total_students = count($students);
$reservations_enabled = count(array_filter($students, fn($s) => $s['reservations_enabled']));
$reservations_disabled = $total_students - $reservations_enabled;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Reservations | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slideUp {
            animation: slideUp 0.6s ease-out forwards;
            opacity: 0;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<?php include 'admin_navbar.php'; ?>

<main class="max-w-7xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-10 animate-slideUp">
        <h1 class="text-4xl font-bold text-slate-900 mb-2">🔒 Manage Student Reservations</h1>
        <p class="text-slate-600">Enable or disable lab reservation access for students</p>
    </div>

    <!-- Success/Error Message -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $message_type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?> animate-slideUp">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.1s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Students</p>
            <p class="text-3xl font-bold text-[#003366]"><?= $total_students ?></p>
            <p class="text-sm text-slate-600 mt-2">Registered</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.2s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Reservations Enabled</p>
            <p class="text-3xl font-bold text-green-600"><?= $reservations_enabled ?></p>
            <p class="text-sm text-slate-600 mt-2">Can make reservations</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.3s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Reservations Disabled</p>
            <p class="text-3xl font-bold text-red-600"><?= $reservations_disabled ?></p>
            <p class="text-sm text-slate-600 mt-2">Cannot make reservations</p>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden animate-slideUp" style="animation-delay: 0.4s;">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-900">Student Reservation Status</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">Student Name</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">ID Number</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">Course</th>
                        <th class="px-6 py-3 text-center font-semibold text-slate-700 uppercase">Pending Reservations</th>
                        <th class="px-6 py-3 text-center font-semibold text-slate-700 uppercase">Active Sessions</th>
                        <th class="px-6 py-3 text-center font-semibold text-slate-700 uppercase">Reservation Status</th>
                        <th class="px-6 py-3 text-center font-semibold text-slate-700 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $index => $student): ?>
                            <tr class="hover:bg-slate-50 transition animate-slideUp" style="animation-delay: <?= 0.4 + ($index * 0.03) ?>s;">
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($student['id_number']) ?></td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($student['course']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-semibold text-xs">
                                        <?= $student['pending_reservations'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs">
                                        <?= $student['active_sessions'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($student['reservations_enabled']): ?>
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold text-xs">
                                            ✅ Enabled
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 font-semibold text-xs">
                                            ❌ Disabled
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <button type="submit" onclick="return confirm('Toggle reservation status for this student?')" 
                                            class="px-4 py-2 rounded-lg font-semibold text-sm transition duration-300
                                            <?= $student['reservations_enabled'] 
                                                ? 'bg-red-100 text-red-700 hover:bg-red-200' 
                                                : 'bg-green-100 text-green-700 hover:bg-green-200' ?>">
                                            <?= $student['reservations_enabled'] ? '🔒 Disable' : '🔓 Enable' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                No students found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Section -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6 animate-slideUp" style="animation-delay: 0.8s;">
        <h3 class="font-bold text-slate-900 mb-3">How This Works:</h3>
        <ul class="text-sm text-slate-700 space-y-2">
            <li>✅ <span class="font-semibold">Enabled:</span> Student can make new lab reservations through the system</li>
            <li>❌ <span class="font-semibold">Disabled:</span> Student cannot make new lab reservations (existing reservations remain)</li>
            <li>📋 <span class="font-semibold">Pending Reservations:</span> Shows count of pending or approved reservations</li>
            <li>⏱️ <span class="font-semibold">Active Sessions:</span> Shows count of ongoing sit-in sessions</li>
            <li>🔄 <span class="font-semibold">Click Action Button:</span> Toggle a student's reservation capability</li>
        </ul>
    </div>
</main>

</body>
</html>
