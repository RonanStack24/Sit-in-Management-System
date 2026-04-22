<?php
session_start();
require 'db.php';

// Check admin
$is_admin = isset($_SESSION['admin_id']) && $_SESSION['is_admin'];
if (!$is_admin) {
    header('Location: login.php');
    exit;
}

$sitin_records = [];
$filter_student = $_GET['student_id'] ?? null;

// Fetch sit-in records
if ($filter_student) {
    $stmt = $pdo->prepare('
        SELECT 
            ss.id, 
            ss.student_id, 
            ss.entry_time, 
            ss.purpose, 
            ss.lab,
            s.first_name,
            s.last_name,
            s.id_number,
            s.course
        FROM sitin_sessions ss
        JOIN students s ON ss.student_id = s.id
        WHERE ss.student_id = ?
        ORDER BY ss.entry_time DESC
    ');
    $stmt->execute([$filter_student]);
} else {
    $stmt = $pdo->query('
        SELECT 
            ss.id, 
            ss.student_id, 
            ss.entry_time, 
            ss.purpose, 
            ss.lab,
            s.first_name,
            s.last_name,
            s.id_number,
            s.course
        FROM sitin_sessions ss
        JOIN students s ON ss.student_id = s.id
        ORDER BY ss.entry_time DESC
        LIMIT 100
    ');
}

$sitin_records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in History | Admin Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- Navigation Bar -->
<nav class="bg-[#003366] text-white shadow-lg sticky top-0 z-50">
    <div class="px-[5%] py-4">
        <div class="flex items-center justify-between mb-3">
            <h1 class="font-bold text-xl">CCS Admin</h1>
            <a href="admin_logout.php" class="text-sm bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded transition">Logout</a>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="admin_home.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Home</a>
            <a href="admin_dashboard.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Record Sit-in</a>
            <a href="admin_current_sitin.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Current Sit-ins</a>
            <a href="admin_announcements.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Announcements</a>
            <a href="admin_feedback.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Feedback</a>
            <a href="admin_reservations.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reservations</a>
            <a href="admin_reports.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reports</a>
            <a href="admin_students.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Students</a>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-5 py-10">
    <h1 class="text-3xl font-bold text-slate-900 mb-8">📊 Sit-in History</h1>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-8">
        <form method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Filter by Student</label>
                <input 
                    type="text" 
                    name="student_id" 
                    placeholder="Enter student ID"
                    value="<?= htmlspecialchars($filter_student ?? '') ?>"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366]"
                >
            </div>
            <button type="submit" class="px-6 py-2.5 bg-[#003366] text-white font-semibold rounded-lg hover:bg-[#004b93] transition">
                Filter
            </button>
            <a href="admin_history.php" class="px-6 py-2.5 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition">
                Clear
            </a>
        </form>
    </div>

    <!-- Records Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">ID Number</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Entry Time</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Lab</th>
                    </tr>
                </thead>
                <tbody divide-y divide-slate-200">
                    <?php if (empty($sitin_records)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No sit-in records found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sitin_records as $record): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    <?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    <?= htmlspecialchars($record['id_number']) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    <?= htmlspecialchars($record['course']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <?= date('M d, Y H:i', strtotime($record['entry_time'])) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    <?= htmlspecialchars($record['purpose'] ?? '—') ?>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    <?= htmlspecialchars($record['lab'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-sm text-slate-500 mt-6 text-center">
        Showing <?= count($sitin_records) ?> records
    </p>
</main>

</body>
</html>
