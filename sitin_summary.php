<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$current_page = 'sitin_summary';

// Fetch student info
$stmt = $pdo->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$stmt->execute([$user_id]);
$student = $stmt->fetch();

// Fetch all sit-in sessions for this student with entry and exit times
$stmt = $pdo->prepare('
    SELECT id, purpose, lab, entry_time, exit_time
    FROM sitin_sessions
    WHERE student_id = ?
    ORDER BY entry_time DESC
');
$stmt->execute([$user_id]);
$sitins = $stmt->fetchAll();

// Calculate statistics
$total_sessions = count($sitins);
$total_hours = 0;
$session_durations = [];
$labs_count = [];
$purposes_count = [];
$longest_session = 0;
$shortest_session = PHP_INT_MAX;

foreach ($sitins as $sitin) {
    // Calculate duration in minutes
    $entry = new DateTime($sitin['entry_time']);
    $exit = $sitin['exit_time'] ? new DateTime($sitin['exit_time']) : new DateTime();
    $interval = $entry->diff($exit);
    $minutes = ($interval->h * 60) + $interval->i;
    
    $session_durations[] = $minutes;
    $total_hours += $minutes / 60;
    $longest_session = max($longest_session, $minutes);
    $shortest_session = min($shortest_session, $minutes);
    
    // Count labs
    $lab = $sitin['lab'];
    $labs_count[$lab] = ($labs_count[$lab] ?? 0) + 1;
    
    // Count purposes
    $purpose = $sitin['purpose'];
    $purposes_count[$purpose] = ($purposes_count[$purpose] ?? 0) + 1;
}

$average_duration = $total_sessions > 0 ? array_sum($session_durations) / $total_sessions : 0;
$average_duration_hours = floor($average_duration / 60);
$average_duration_mins = round($average_duration % 60);

$total_hours_int = floor($total_hours);
$total_mins = round(($total_hours - $total_hours_int) * 60);

$longest_hours = floor($longest_session / 60);
$longest_mins = $longest_session % 60;

$shortest_hours = floor($shortest_session / 60);
$shortest_mins = $shortest_session % 60;

// Get most used lab
$most_used_lab = $labs_count ? array_key_first($labs_count) : 'N/A';
$most_used_lab_count = $labs_count ? max($labs_count) : 0;

// Get most frequent purpose
$most_frequent_purpose = $purposes_count ? array_key_first($purposes_count) : 'N/A';
$most_frequent_purpose_count = $purposes_count ? max($purposes_count) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in Summary | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- Navigation -->
<?php include 'navbar.php'; ?>

<main class="max-w-7xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">📊 Sit-in Summary</h1>
        <p class="text-slate-600">Your comprehensive sit-in analytics and statistics</p>
    </div>

    <!-- Main Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Sessions -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Sessions</p>
                <span class="text-2xl">📝</span>
            </div>
            <p class="text-4xl font-bold text-[#003366] mb-1"><?= $total_sessions ?></p>
            <p class="text-xs text-slate-600">session<?= $total_sessions !== 1 ? 's' : '' ?> completed</p>
        </div>

        <!-- Total Hours -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Hours</p>
                <span class="text-2xl">⏱️</span>
            </div>
            <p class="text-4xl font-bold text-blue-600 mb-1"><?= $total_hours_int ?>h <?= $total_mins ?>m</p>
            <p class="text-xs text-slate-600"><?= number_format($total_hours, 1) ?> hours total</p>
        </div>

        <!-- Average Duration -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Average Duration</p>
                <span class="text-2xl">📊</span>
            </div>
            <p class="text-4xl font-bold text-green-600 mb-1"><?= $average_duration_hours ?>h <?= $average_duration_mins ?>m</p>
            <p class="text-xs text-slate-600">per session</p>
        </div>

        <!-- Longest Session -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Longest Session</p>
                <span class="text-2xl">🏆</span>
            </div>
            <p class="text-4xl font-bold text-purple-600 mb-1"><?= $longest_hours ?>h <?= $longest_mins ?>m</p>
            <p class="text-xs text-slate-600"><?= $total_sessions > 0 ? 'your record' : 'no data' ?></p>
        </div>
    </div>

    <!-- Lab & Purpose Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Lab Usage -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">🏫 Lab Usage</h2>
            
            <?php if (count($labs_count) > 0): ?>
                <div class="space-y-3">
                    <?php 
                        arsort($labs_count);
                        foreach ($labs_count as $lab => $count): 
                            $percentage = ($count / $total_sessions) * 100;
                    ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($lab) ?></p>
                                <p class="text-xs font-semibold text-slate-500"><?= $count ?> times (<?= round($percentage) ?>%)</p>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full transition" style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-slate-500 text-sm">No lab data available</p>
            <?php endif; ?>
        </div>

        <!-- Sit-in Purpose -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">📌 Sit-in Purpose</h2>
            
            <?php if (count($purposes_count) > 0): ?>
                <div class="space-y-3">
                    <?php 
                        arsort($purposes_count);
                        foreach ($purposes_count as $purpose => $count): 
                            $percentage = ($count / $total_sessions) * 100;
                    ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($purpose) ?></p>
                                <p class="text-xs font-semibold text-slate-500"><?= $count ?> times (<?= round($percentage) ?>%)</p>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition" style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-slate-500 text-sm">No purpose data available</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Lab Distribution Chart -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">📍 Lab Distribution</h2>
            <?php if (count($labs_count) > 0): ?>
                <div style="position: relative; height: 250px;">
                    <canvas id="labChart"></canvas>
                </div>
            <?php else: ?>
                <p class="text-slate-500 text-sm text-center py-12">No lab data available</p>
            <?php endif; ?>
        </div>

        <!-- Purpose Distribution Chart -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">🎯 Purpose Distribution</h2>
            <?php if (count($purposes_count) > 0): ?>
                <div style="position: relative; height: 250px;">
                    <canvas id="purposeChart"></canvas>
                </div>
            <?php else: ?>
                <p class="text-slate-500 text-sm text-center py-12">No purpose data available</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="bg-gradient-to-r from-[#003366] to-[#004b93] rounded-lg shadow-lg p-8 text-white mb-8">
        <h2 class="text-2xl font-bold mb-6">📈 Your Achievement Summary</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white/10 rounded-lg p-4 backdrop-blur">
                <p class="text-sm text-white/80 mb-1">Most Used Lab</p>
                <p class="text-2xl font-bold text-white"><?= htmlspecialchars($most_used_lab) ?></p>
                <p class="text-xs text-white/70 mt-1"><?= $most_used_lab_count ?> session<?= $most_used_lab_count !== 1 ? 's' : '' ?></p>
            </div>

            <div class="bg-white/10 rounded-lg p-4 backdrop-blur">
                <p class="text-sm text-white/80 mb-1">Most Frequent Purpose</p>
                <p class="text-2xl font-bold text-white"><?= htmlspecialchars($most_frequent_purpose) ?></p>
                <p class="text-xs text-white/70 mt-1"><?= $most_frequent_purpose_count ?> session<?= $most_frequent_purpose_count !== 1 ? 's' : '' ?></p>
            </div>

            <div class="bg-white/10 rounded-lg p-4 backdrop-blur">
                <p class="text-sm text-white/80 mb-1">Consistency</p>
                <p class="text-2xl font-bold text-white"><?= $total_sessions > 0 ? '✅ Active' : 'Not Started' ?></p>
                <p class="text-xs text-white/70 mt-1">Keep it up!</p>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div>
        <a href="sitin_history.php" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition">
            ← Back to History
        </a>
    </div>
</main>

<script>
    <?php if (count($labs_count) > 0): ?>
    // Lab Distribution Chart
    const labCtx = document.getElementById('labChart').getContext('2d');
    const labChart = new Chart(labCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($labs_count)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($labs_count)) ?>,
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
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
    <?php endif; ?>

    <?php if (count($purposes_count) > 0): ?>
    // Purpose Distribution Chart
    const purposeCtx = document.getElementById('purposeChart').getContext('2d');
    const purposeChart = new Chart(purposeCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($purposes_count)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($purposes_count)) ?>,
                backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
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
    <?php endif; ?>
</script>

</body>
</html>
