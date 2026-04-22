<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$records = [];
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$filter_student = $_GET['student_id'] ?? '';

// Fetch sit-in records based on filters
$query = '
    SELECT 
        ss.id, 
        ss.student_id, 
        ss.entry_time, 
        ss.purpose, 
        ss.lab,
        s.first_name,
        s.last_name,
        s.id_number,
        s.course,
        s.course_level
    FROM sitin_sessions ss
    JOIN students s ON ss.student_id = s.id
    WHERE 1=1
';

$params = [];

if ($start_date) {
    $query .= ' AND DATE(ss.entry_time) >= ?';
    $params[] = $start_date;
}

if ($end_date) {
    $query .= ' AND DATE(ss.entry_time) <= ?';
    $params[] = $end_date;
}

if ($filter_student) {
    $query .= ' AND s.id_number LIKE ?';
    $params[] = '%' . $filter_student . '%';
}

$query .= ' ORDER BY ss.entry_time DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();
$record_count = count($records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports | CCS Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/utils.js"></script>
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
            <a href="admin_reports.php" class="px-3 py-2 text-sm font-semibold bg-white/20 rounded hover:bg-white/30 transition border-b-2 border-white">Reports</a>
            <a href="admin_students.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Students</a>
        </div>
    </div>
</nav>

<main class="max-w-full mx-auto px-5 py-10">
    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-900 mb-2">Generate Reports</h1>
    <p class="text-slate-600 mb-6">Export sit-in records to PDF, CSV, Excel, or Print</p>

    <!-- Filter Section -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <!-- Start Date -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Start Date</label>
                <input 
                    type="date" 
                    name="start_date" 
                    value="<?= htmlspecialchars($start_date) ?>"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366]"
                >
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">End Date</label>
                <input 
                    type="date" 
                    name="end_date" 
                    value="<?= htmlspecialchars($end_date) ?>"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366]"
                >
            </div>

            <!-- Student ID -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Student ID</label>
                <input 
                    type="text" 
                    name="student_id" 
                    placeholder="Optional"
                    value="<?= htmlspecialchars($filter_student) ?>"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366]"
                >
            </div>

            <!-- Search Button -->
            <button type="submit" class="px-6 py-2 bg-[#003366] text-white font-semibold rounded-lg hover:bg-[#004b93] transition col-span-1 sm:col-span-3">
                Search
            </button>
        </form>
    </div>

    <!-- Export Buttons -->
    <?php if ($records): ?>
        <div class="flex flex-wrap gap-3 mb-6">
            <form method="POST" action="admin_export_pdf.php" style="display: inline;">
                <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                <input type="hidden" name="student_id" value="<?= htmlspecialchars($filter_student) ?>">
                <button type="submit" class="px-4 py-2 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF
                </button>
            </form>

            <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4H7a2 2 0 01-2-2v-4a2 2 0 012-2h10a2 2 0 012 2v4a2 2 0 01-2 2zm2-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Print
            </button>
        </div>
    <?php endif; ?>

    <!-- Records Table -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-x-auto">
        <?php if ($records): ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">ID Number</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Purpose</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Laboratory</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Login</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Logout</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): 
                        $entry_time = new DateTime($record['entry_time']);
                        $login = $entry_time->format('h:i:sa');
                        $logout = $entry_time->format('h:i:sa'); // Same as login since no exit_time in DB
                        $date = $entry_time->format('Y-m-d');
                    ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($record['id_number']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($record['purpose']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($record['lab']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= $login ?></td>
                            <td class="px-4 py-3 text-slate-700">-</td>
                            <td class="px-4 py-3 text-slate-700"><?= $date ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Summary -->
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 text-sm text-slate-600">
                <p><strong><?= $record_count ?></strong> record<?= $record_count !== 1 ? 's' : '' ?> found</p>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <p class="text-lg text-slate-500 mb-2">📭 No records found</p>
                <p class="text-sm text-slate-400">Try adjusting your filters or date range</p>
            </div>
        <?php endif; ?>
    </div>

</main>

</body>
</html>
