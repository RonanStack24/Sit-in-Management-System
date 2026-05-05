<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$current_page = 'admin_home';

// Get statistics
$stmt = $pdo->query('SELECT COUNT(DISTINCT student_id) as active_students FROM sitin_sessions');
$active_students = $stmt->fetch()['active_students'] ?? 0;

$stmt = $pdo->query('SELECT COUNT(*) as total_sessions FROM sitin_sessions');
$total_sessions = $stmt->fetch()['total_sessions'] ?? 0;

$stmt = $pdo->query('SELECT COUNT(*) as total_students FROM students');
$total_students = $stmt->fetch()['total_students'] ?? 0;

// Get sit-ins by purpose
$stmt = $pdo->query('SELECT purpose, COUNT(*) as count FROM sitin_sessions GROUP BY purpose ORDER BY count DESC LIMIT 5');
$top_purposes = $stmt->fetchAll();

// Get sit-ins by lab
$stmt = $pdo->query('SELECT lab, COUNT(*) as count FROM sitin_sessions GROUP BY lab ORDER BY count DESC LIMIT 5');
$top_labs = $stmt->fetchAll();

// Get recent sit-ins
$stmt = $pdo->query('
    SELECT s.first_name, s.last_name, s.id_number, ss.purpose, ss.lab, ss.entry_time
    FROM sitin_sessions ss
    JOIN students s ON ss.student_id = s.id
    ORDER BY ss.entry_time DESC
    LIMIT 10
');
$recent_sitins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<?php include 'admin_navbar.php'; ?>

<main class="max-w-7xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-900 mb-8">Dashboard Overview</h1>

    <!-- Key Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Active Students -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Active Students</p>
                    <p class="text-3xl font-bold text-[#003366]"><?= $active_students ?></p>
                </div>
                <div class="text-4xl text-green-500">👥</div>
            </div>
            <p class="text-xs text-slate-500 mt-3">Currently in sit-in</p>
        </div>

        <!-- Total Sessions -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Sessions</p>
                    <p class="text-3xl font-bold text-[#003366]"><?= $total_sessions ?></p>
                </div>
                <div class="text-4xl text-blue-500">📝</div>
            </div>
            <p class="text-xs text-slate-500 mt-3">All recorded sit-ins</p>
        </div>

        <!-- Total Students -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Students</p>
                    <p class="text-3xl font-bold text-[#003366]"><?= $total_students ?></p>
                </div>
                <div class="text-4xl text-purple-500">🎓</div>
            </div>
            <p class="text-xs text-slate-500 mt-3">Registered users</p>
        </div>

        <!-- Utilization Rate -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Utilization</p>
                    <p class="text-3xl font-bold text-[#003366]"><?= $total_students > 0 ? round(($active_students / $total_students) * 100) : 0 ?>%</p>
                </div>
                <div class="text-4xl text-orange-500">📊</div>
            </div>
            <p class="text-xs text-slate-500 mt-3">Active of total</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Purposes -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Top Purposes</h2>
            <div style="height: 300px;">
                <canvas id="purposesChart"></canvas>
            </div>
        </div>

        <!-- Top Labs -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Top Labs</h2>
            <div style="height: 300px;">
                <canvas id="labsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Recent Sit-ins</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">ID Number</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Student Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Purpose</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Lab</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_sitins as $sitin): 
                        $entry_time = new DateTime($sitin['entry_time']);
                        $time = $entry_time->format('h:i:sa');
                    ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-semibold text-slate-900"><?= htmlspecialchars($sitin['id_number']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($sitin['first_name'] . ' ' . $sitin['last_name']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($sitin['purpose']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($sitin['lab']) ?></td>
                            <td class="px-4 py-3 text-slate-700 text-xs"><?= $time ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Charts Script -->
<script>
    // Top Purposes Chart
    const purposesData = <?= json_encode(array_column($top_purposes, 'purpose')) ?>;
    const purposesCount = <?= json_encode(array_column($top_purposes, 'count')) ?>;
    
    const purposesCtx = document.getElementById('purposesChart').getContext('2d');
    const purposesChart = new Chart(purposesCtx, {
        type: 'doughnut',
        data: {
            labels: purposesData,
            datasets: [{
                data: purposesCount,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                ]
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

    // Top Labs Chart
    const labsData = <?= json_encode(array_column($top_labs, 'lab')) ?>;
    const labsCount = <?= json_encode(array_column($top_labs, 'count')) ?>;
    
    const labsCtx = document.getElementById('labsChart').getContext('2d');
    const labsChart = new Chart(labsCtx, {
        type: 'doughnut',
        data: {
            labels: labsData,
            datasets: [{
                data: labsCount,
                backgroundColor: [
                    '#FF6B9D', '#C44569', '#FFA502', '#F7931E', '#FDB915'
                ]
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

</body>
</html>
