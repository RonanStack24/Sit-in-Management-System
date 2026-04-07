<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch student info
$stmt = $pdo->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$stmt->execute([$user_id]);
$student = $stmt->fetch();

// Set current page for navbar
$current_page = 'sitin_history';

// Fetch all sit-in sessions for this student
$stmt = $pdo->prepare('
    SELECT id, purpose, lab, entry_time
    FROM sitin_sessions
    WHERE student_id = ?
    ORDER BY entry_time DESC
');
$stmt->execute([$user_id]);
$sitins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in History | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-[Inter]">

<!-- Navigation -->
<?php 
    include 'navbar.php'; 
?>

<main class="max-w-6xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">My Sit-in History</h1>
        <p class="text-slate-600">View all your sit-in sessions at a glance</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Sessions</p>
                    <p class="text-3xl font-bold text-[#003366]"><?= count($sitins) ?></p>
                </div>
                <div class="text-4xl">📊</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Most Recent</p>
                    <p class="text-lg font-bold text-[#003366]">
                        <?= count($sitins) > 0 ? (new DateTime($sitins[0]['entry_time']))->format('M d, Y') : 'No sessions' ?>
                    </p>
                </div>
                <div class="text-4xl">📅</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Favorite Lab</p>
                    <p class="text-lg font-bold text-[#003366]">
                        <?php
                        if (count($sitins) > 0) {
                            $labs = array_count_values(array_column($sitins, 'lab'));
                            $favorite = array_key_first($labs);
                            echo htmlspecialchars($favorite ?? 'N/A');
                        } else {
                            echo 'No data';
                        }
                        ?>
                    </p>
                </div>
                <div class="text-4xl">🏫</div>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-900">All Sit-in Sessions</h2>
        </div>
        
        <?php if (count($sitins) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">#</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Date & Time</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Purpose</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Laboratory</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sitins as $index => $sitin):
                            $entry = new DateTime($sitin['entry_time']);
                            $date = $entry->format('M d, Y');
                            $time = $entry->format('h:i A');
                        ?>
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-900"><?= $index + 1 ?></td>
                                <td class="px-6 py-4 text-slate-700">
                                    <div class="font-semibold"><?= $date ?></div>
                                    <div class="text-xs text-slate-500"><?= $time ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($sitin['purpose']) ?></td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($sitin['lab']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✓ Completed
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="text-5xl mb-3">📭</div>
                <p class="text-slate-600 font-semibold mb-1">No Sit-in History Yet</p>
                <p class="text-slate-500 text-sm">You haven't recorded any sit-in sessions yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <div class="mt-8">
        <a href="dashboard.php" class="inline-flex items-center rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-300 transition">
            ← Back to Dashboard
        </a>
    </div>

</main>

</body>
</html>
