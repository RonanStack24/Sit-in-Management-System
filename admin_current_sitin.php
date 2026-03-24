<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$logout_message = '';

// Handle logout user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout_user') {
    $sitin_id = $_POST['sitin_id'] ?? null;
    $student_id = $_POST['student_id'] ?? null;
    
    if ($sitin_id && $student_id) {
        // Decrease sessions_left by 1
        $stmt = $pdo->prepare('SELECT sessions_left FROM students WHERE id = ?');
        $stmt->execute([$student_id]);
        $student_data = $stmt->fetch();
        $new_sessions = max(0, ($student_data['sessions_left'] ?? 30) - 1);
        
        $stmt = $pdo->prepare('UPDATE students SET sessions_left = ? WHERE id = ?');
        $stmt->execute([$new_sessions, $student_id]);
        
        // Delete the sit-in record to mark it as processed
        $stmt = $pdo->prepare('DELETE FROM sitin_sessions WHERE id = ?');
        $stmt->execute([$sitin_id]);
        
        $logout_message = 'User logged out and session decreased by 1!';
    }
}

// Fetch current sit-ins with student details
$stmt = $pdo->prepare('
    SELECT 
        ss.id as sitin_id,
        s.id as student_id,
        s.id_number,
        s.first_name,
        s.last_name,
        s.course,
        s.course_level,
        s.sessions_left,
        ss.purpose,
        ss.lab,
        ss.entry_time
    FROM sitin_sessions ss
    JOIN students s ON ss.student_id = s.id
    ORDER BY ss.entry_time DESC
');
$stmt->execute();
$current_sitins = $stmt->fetchAll();
$session_count = count($current_sitins);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-ins | CCS Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/utils.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- Navigation Bar -->
<nav class="bg-[#003366] text-white shadow-sm sticky top-0 z-50">
    <div class="px-[5%] py-4 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <span class="font-bold text-lg">CCS Admin Dashboard</span>
            <div class="flex gap-4">
                <a href="admin_dashboard.php" class="text-sm text-white/80 hover:text-white transition">Record Sit-in</a>
                <a href="admin_current_sitin.php" class="text-sm text-white font-semibold border-b-2 border-white">Current Sit-ins</a>
                <a href="admin_history.php" class="text-sm text-white/80 hover:text-white transition">View History</a>
                <a href="admin_reports.php" class="text-sm text-white/80 hover:text-white transition">Reports</a>
            </div>
        </div>
        <a href="admin_logout.php" class="text-sm text-white/80 hover:text-white">Logout</a>
    </div>
</nav>

<main class="max-w-full mx-auto px-5 py-10">
    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-900 mb-2">Current Sit-in</h1>
    <p class="text-slate-600 mb-6">Active sit-in sessions - <?= $session_count ?> student<?= $session_count !== 1 ? 's' : '' ?> logged in</p>

    <?php 
    // Calculate chart data
    $purpose_count = [];
    $lab_count = [];
    foreach ($current_sitins as $sitin) {
        $purpose_count[$sitin['purpose']] = ($purpose_count[$sitin['purpose']] ?? 0) + 1;
        $lab_count[$sitin['lab']] = ($lab_count[$sitin['lab']] ?? 0) + 1;
    }
    ?>

    <!-- Charts Section -->
    <?php if ($current_sitins): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Purpose Chart -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Sit-ins by Purpose</h2>
                <div style="height: 300px;">
                    <canvas id="purposeChart"></canvas>
                </div>
            </div>

            <!-- Lab Chart -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Sit-ins by Lab</h2>
                <div style="height: 300px;">
                    <canvas id="labChart"></canvas>
                </div>
            </div>
        </div>

        <script>
            // Purpose Chart
            const purposeCtx = document.getElementById('purposeChart').getContext('2d');
            const purposeChart = new Chart(purposeCtx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_keys($purpose_count)) ?>,
                    datasets: [{
                        data: <?= json_encode(array_values($purpose_count)) ?>,
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Lab Chart
            const labCtx = document.getElementById('labChart').getContext('2d');
            const labChart = new Chart(labCtx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_keys($lab_count)) ?>,
                    datasets: [{
                        data: <?= json_encode(array_values($lab_count)) ?>,
                        backgroundColor: [
                            '#FF6B9D', '#C44569', '#FFA502', '#F7931E', '#FDB915',
                            '#68BCB4', '#00B8E6', '#FF7C7C', '#A0D468', '#AB47BC'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        </script>
    <?php endif; ?>

    <?php if ($logout_message): ?>
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded max-w-2xl">
            <p class="text-sm font-semibold text-green-800">✅ <?= htmlspecialchars($logout_message) ?></p>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToastWithType('<?= htmlspecialchars($logout_message, ENT_QUOTES) ?>', 'success');
            });
        </script>
    <?php endif; ?>

    <!-- Entries Per Page Selector -->
    <div class="mb-4 flex items-center gap-2">
        <label for="entries_per_page" class="text-sm font-semibold text-slate-600">Entries per page:</label>
        <select id="entries_per_page" class="px-3 py-1 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#003366]">
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-x-auto">
        <?php if ($current_sitins): ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Sit ID Number</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">ID Number</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Purpose</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Sit Lab</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Session</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_sitins as $index => $sitin): ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-semibold text-slate-900"><?= (int)$sitin['sitin_id'] ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($sitin['id_number']) ?></td>
                            <td class="px-4 py-3 text-slate-700">
                                <p class="font-semibold"><?= htmlspecialchars($sitin['first_name'] . ' ' . $sitin['last_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= htmlspecialchars($sitin['course']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($sitin['purpose']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($sitin['lab']) ?></td>
                            <td class="px-4 py-3 text-center font-semibold text-blue-600">
                                <span class="bg-blue-100 px-3 py-1 rounded-full text-xs font-semibold">
                                    <?= (int)$sitin['sessions_left'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    Active
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="logout_user">
                                    <input type="hidden" name="sitin_id" value="<?= $sitin['sitin_id'] ?>">
                                    <input type="hidden" name="student_id" value="<?= $sitin['student_id'] ?>">
                                    <button 
                                        type="submit" 
                                        class="px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded hover:bg-red-600 transition"
                                        onclick="return confirm('Logout this student?');"
                                    >
                                        Logout
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-12 text-center">
                <p class="text-lg text-slate-500 mb-2">📭 No active sit-ins</p>
                <p class="text-sm text-slate-400">All students have been logged out</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination Info -->
    <?php if ($current_sitins): ?>
        <div class="mt-4 text-sm text-slate-600">
            <p>Showing 1 to <?= min(count($current_sitins), 10) ?> of <?= $session_count ?> entries</p>
        </div>
    <?php endif; ?>

</main>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 bg-green-500 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm">
    <span id="toast-msg"></span>
</div>

</body>
</html>
