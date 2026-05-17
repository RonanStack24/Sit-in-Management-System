<?php
session_start();
require 'db.php';

// Set current page for navbar
$current_page = 'leaderboards';

// Fetch top students by total sit-in hours
$stmt = $pdo->query('
    SELECT 
        s.id,
        s.first_name,
        s.last_name,
        s.id_number,
        s.course,
        COUNT(ss.id) as total_sessions,
        COALESCE(SUM(TIMESTAMPDIFF(MINUTE, ss.entry_time, COALESCE(ss.exit_time, NOW()))), 0) as total_minutes,
        ROUND(COALESCE(SUM(TIMESTAMPDIFF(MINUTE, ss.entry_time, COALESCE(ss.exit_time, NOW()))), 0) / 60.0, 1) as total_hours
    FROM students s
    LEFT JOIN sitin_sessions ss ON s.id = ss.student_id
    GROUP BY s.id, s.first_name, s.last_name, s.id_number, s.course
    HAVING total_sessions > 0
    ORDER BY total_hours DESC
    LIMIT 50
');
$top_students = $stmt->fetchAll();

// Fetch stats
$total_students = $pdo->query('SELECT COUNT(*) as count FROM students')->fetch()['count'] ?? 0;
$total_sessions = $pdo->query('SELECT COUNT(*) as count FROM sitin_sessions')->fetch()['count'] ?? 0;
$avg_hours = $pdo->query('SELECT ROUND(AVG(hours), 1) as avg FROM (SELECT ROUND(COALESCE(SUM(TIMESTAMPDIFF(MINUTE, entry_time, COALESCE(exit_time, NOW()))), 0) / 60.0, 1) as hours FROM sitin_sessions GROUP BY student_id) t')->fetch()['avg'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboards | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<?php include 'navbar.php'; ?>

<main class="max-w-7xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-10">
        <h1 class="text-4xl font-bold text-slate-900 mb-2">🏆 Leaderboards</h1>
        <p class="text-slate-600">Top students by sit-in hours and consistency</p>
    </div>

    <!-- Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Students</p>
            <p class="text-3xl font-bold text-[#003366]"><?= $total_students ?></p>
            <p class="text-sm text-slate-600 mt-2">Registered</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Sessions</p>
            <p class="text-3xl font-bold text-[#003366]"><?= $total_sessions ?></p>
            <p class="text-sm text-slate-600 mt-2">All sit-ins</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Average Hours</p>
            <p class="text-3xl font-bold text-[#003366]"><?= $avg_hours ?></p>
            <p class="text-sm text-slate-600 mt-2">Per student</p>
        </div>
    </div>

    <!-- Top Students Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-900">Top Students by Hours</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">ID Number</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Course</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase">Sessions</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase">Hours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (count($top_students) > 0): ?>
                        <?php foreach ($top_students as $index => $student): ?>
                            <?php 
                                $rank = $index + 1;
                                $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                            ?>
                            <tr class="hover:bg-slate-50 transition <?= $rank <= 3 ? 'bg-slate-50' : '' ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg"><?= $medal ?></span>
                                        <span class="font-semibold text-slate-900"><?= $rank ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-900 font-semibold">
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <?= htmlspecialchars($student['id_number']) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <?= htmlspecialchars($student['course']) ?>
                                </td>
                                <td class="px-6 py-4 text-right text-slate-900 font-semibold">
                                    <?= $student['total_sessions'] ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-sm">
                                        <?= $student['total_hours'] ?> hrs
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No sit-in records yet
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <p class="text-sm text-slate-700">
            <span class="font-semibold">How rankings work:</span> Students are ranked based on total sit-in hours completed. Hours are calculated from entry and exit times. Leaderboards are updated in real-time as new sit-in sessions are recorded.
        </p>
    </div>
</main>

</body>
</html>
