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
        s.profile_photo,
        COUNT(ss.id) as total_sessions,
        COALESCE(SUM(TIMESTAMPDIFF(MINUTE, ss.entry_time, COALESCE(ss.exit_time, NOW()))), 0) as total_minutes,
        ROUND(COALESCE(SUM(TIMESTAMPDIFF(MINUTE, ss.entry_time, COALESCE(ss.exit_time, NOW()))), 0) / 60.0, 1) as total_hours
    FROM students s
    LEFT JOIN sitin_sessions ss ON s.id = ss.student_id
    GROUP BY s.id, s.first_name, s.last_name, s.id_number, s.course, s.profile_photo
    HAVING total_sessions > 0
    ORDER BY total_hours DESC
    LIMIT 50
');
$top_students = $stmt->fetchAll();

// Compute weighted scores for ranking
$max_hours = 0;
$max_sessions = 0;
foreach ($top_students as $student) {
    $max_hours = max($max_hours, (float) $student['total_hours']);
    $max_sessions = max($max_sessions, (int) $student['total_sessions']);
}

foreach ($top_students as &$student) {
    $participation_score = $max_sessions > 0 ? ($student['total_sessions'] / $max_sessions) * 100 : 0;
    $hours_score = $max_hours > 0 ? ($student['total_hours'] / $max_hours) * 100 : 0;
    $task_completion = min(100, $student['total_sessions'] * 5);

    $student['task_completion'] = round($task_completion);
    $student['performance_points'] = round(
        ($participation_score * 0.5) + ($hours_score * 0.3) + ($task_completion * 0.2),
        1
    );
}
unset($student);

usort($top_students, function ($a, $b) {
    return $b['performance_points'] <=> $a['performance_points'];
});

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
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
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
        
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out forwards;
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
    <div class="mb-10 animate-fadeIn">
        <h1 class="text-4xl font-bold text-slate-900 mb-2">Student Leaderboard</h1>
        <p class="text-slate-600">Performance points, sit-in hours, and task completion</p>
    </div>

    <!-- Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.1s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Students</p>
            <p class="text-3xl font-bold text-[#003366]"><?= $total_students ?></p>
            <p class="text-sm text-slate-600 mt-2">Registered</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.2s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Sessions</p>
            <p class="text-3xl font-bold text-[#003366]"><?= $total_sessions ?></p>
            <p class="text-sm text-slate-600 mt-2">All sit-ins</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 animate-slideUp" style="animation-delay: 0.3s;">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Average Hours</p>
            <p class="text-3xl font-bold text-[#003366]"><?= $avg_hours ?></p>
            <p class="text-sm text-slate-600 mt-2">Per student</p>
        </div>
    </div>

    <!-- Top Students Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden animate-fadeIn" style="animation-delay: 0.4s;">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-900">Top Students</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Profile</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">ID Number</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Course</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase">Performance Points</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase">Sit-in Hours</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 uppercase">Task Completion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (count($top_students) > 0): ?>
                        <?php foreach ($top_students as $index => $student): ?>
                            <?php 
                                $rank = $index + 1;
                                $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : ''));
                                $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
                                $profile_pic = !empty($student['profile_photo']) && file_exists($student['profile_photo']) ? $student['profile_photo'] : null;
                                $performance_points = $student['performance_points'];
                                $task_completion = $student['task_completion'];
                            ?>
                            <tr class="hover:bg-slate-100 transition-all duration-300 animate-slideUp" style="animation-delay: <?= 0.4 + ($index * 0.05) ?>s;">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg"><?= $medal ?></span>
                                        <span class="font-semibold text-slate-900"><?= $rank ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($profile_pic): ?>
                                        <img src="<?= htmlspecialchars($profile_pic) ?>" alt="<?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>" class="w-10 h-10 rounded-full object-cover border-2 border-slate-200 transition-transform duration-300 hover:scale-110 hover:shadow-lg">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm transition-transform duration-300 hover:scale-110 hover:shadow-lg">
                                            <?= $initials ?>
                                        </div>
                                    <?php endif; ?>
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
                                <td class="px-6 py-4 text-right">
                                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold text-sm transition-all duration-300 hover:bg-emerald-200 hover:scale-105">
                                        <?= $performance_points ?> pts
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-sm transition-all duration-300 hover:bg-blue-200 hover:scale-105">
                                        <?= $student['total_hours'] ?> hrs
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-slate-700 font-semibold">
                                    <?= $task_completion ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                                No sit-in records yet
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6 animate-fadeIn" style="animation-delay: 0.8s;">
        <p class="text-sm text-slate-700">
            <span class="font-semibold">How rankings work:</span> Performance points use the 50/30/20 weights (participation, sit-in hours, task completion). Participation and hours are normalized across the leaderboard, while task completion is capped at 100%.
        </p>
    </div>
</main>

</body>
</html>
