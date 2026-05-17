<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$current_page = 'sitin_history';

// Fetch student info
$stmt = $pdo->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$stmt->execute([$user_id]);
$student = $stmt->fetch();

// Fetch all sit-in sessions with detailed information
$stmt = $pdo->prepare('
    SELECT 
        id,
        purpose,
        lab,
        pc_no,
        entry_time,
        exit_time,
        status,
        TIMESTAMPDIFF(MINUTE, entry_time, COALESCE(exit_time, NOW())) as duration_minutes
    FROM sitin_sessions
    WHERE student_id = ?
    ORDER BY entry_time DESC
');
$stmt->execute([$user_id]);
$sessions = $stmt->fetchAll();

// Calculate statistics
$total_sessions = count($sessions);
$active_sessions = count(array_filter($sessions, fn($s) => $s['status'] === 'Active' || !$s['exit_time']));
$completed_sessions = count(array_filter($sessions, fn($s) => $s['status'] === 'Completed' && $s['exit_time']));
$total_hours = 0;
foreach ($sessions as $session) {
    $total_hours += $session['duration_minutes'];
}
$total_hours = round($total_hours / 60, 1);
$avg_duration = $total_sessions > 0 ? round(array_sum(array_column($sessions, 'duration_minutes')) / $total_sessions / 60, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Sessions | CCS Sit-in System</title>
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

<?php include 'navbar.php'; ?>

<main class="max-w-7xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-10 animate-slideUp">
        <h1 class="text-4xl font-bold text-slate-900 mb-2">📋 Detailed Session Records</h1>
        <p class="text-slate-600">View all your sit-in sessions with complete information</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.1s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Sessions</p>
            <p class="text-3xl font-bold text-[#003366]"><?= $total_sessions ?></p>
            <p class="text-sm text-slate-600 mt-2">All sessions</p>
        </div>
        
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.2s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Active Sessions</p>
            <p class="text-3xl font-bold text-green-600"><?= $active_sessions ?></p>
            <p class="text-sm text-slate-600 mt-2">Currently ongoing</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.3s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Hours</p>
            <p class="text-3xl font-bold text-blue-600"><?= $total_hours ?></p>
            <p class="text-sm text-slate-600 mt-2">Combined duration</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.4s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Average Duration</p>
            <p class="text-3xl font-bold text-purple-600"><?= $avg_duration ?></p>
            <p class="text-sm text-slate-600 mt-2">Per session (hrs)</p>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden animate-slideUp" style="animation-delay: 0.5s;">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-900">Session Details</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">Date</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">Time In</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">Time Out</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">Duration</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">Lab</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">PC No.</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700 uppercase">Purpose</th>
                        <th class="px-6 py-3 text-center font-semibold text-slate-700 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (count($sessions) > 0): ?>
                        <?php foreach ($sessions as $index => $session): ?>
                            <?php
                                $entry_date = new DateTime($session['entry_time']);
                                $formatted_date = $entry_date->format('M d, Y');
                                $entry_time = $entry_date->format('h:i A');
                                $exit_time = $session['exit_time'] ? (new DateTime($session['exit_time']))->format('h:i A') : '—';
                                $duration_hours = round($session['duration_minutes'] / 60, 2);
                                $status_colors = [
                                    'Active' => 'bg-green-100 text-green-700',
                                    'Completed' => 'bg-blue-100 text-blue-700',
                                    'Cancelled' => 'bg-red-100 text-red-700'
                                ];
                                $status = $session['status'] ?: ($session['exit_time'] ? 'Completed' : 'Active');
                                $status_class = $status_colors[$status] ?? $status_colors['Active'];
                            ?>
                            <tr class="hover:bg-slate-50 transition animate-slideUp" style="animation-delay: <?= 0.5 + ($index * 0.05) ?>s;">
                                <td class="px-6 py-4 font-semibold text-slate-900"><?= $formatted_date ?></td>
                                <td class="px-6 py-4 text-slate-700"><?= $entry_time ?></td>
                                <td class="px-6 py-4 text-slate-700"><?= $exit_time ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold text-xs">
                                        <?= $duration_hours ?> hrs
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($session['lab']) ?></td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($session['pc_no'] ?? '—') ?></td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($session['purpose'] ?? '—') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $status_class ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                No session records yet
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer Info -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6 animate-slideUp" style="animation-delay: 0.8s;">
        <p class="text-sm text-slate-700">
            <span class="font-semibold">Session Status:</span>
            <span class="text-green-600">Active</span> - Ongoing session (no exit time)
            <span class="text-blue-600">| Completed</span> - Session finished
            <span class="text-red-600">| Cancelled</span> - Session cancelled
        </p>
    </div>
</main>

</body>
</html>
