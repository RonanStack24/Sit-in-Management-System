<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

// Get feedback stats
$stmt = $pdo->query('SELECT category, COUNT(*) as count, AVG(rating) as avg_rating FROM feedback GROUP BY category');
$stats = $stmt->fetchAll();

// Get all feedback
$stmt = $pdo->query('
    SELECT f.*, s.first_name, s.last_name, s.id_number
    FROM feedback f
    JOIN students s ON f.student_id = s.id
    ORDER BY f.created_at DESC
');
$all_feedback = $stmt->fetchAll();

// Get feedback by category
$feedback_by_category = [];
foreach ($all_feedback as $fb) {
    $category = $fb['category'];
    if (!isset($feedback_by_category[$category])) {
        $feedback_by_category[$category] = [];
    }
    $feedback_by_category[$category][] = $fb;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback | CCS Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
            <a href="admin_feedback.php" class="px-3 py-2 text-sm font-semibold bg-white/20 rounded hover:bg-white/30 transition border-b-2 border-white">Feedback</a>
            <a href="admin_reservations.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reservations</a>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-900 mb-8">Student Feedback</h1>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Feedback</p>
                    <p class="text-3xl font-bold text-[#003366]"><?= count($all_feedback) ?></p>
                </div>
                <div class="text-4xl">💬</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Average Rating</p>
                    <p class="text-3xl font-bold text-[#003366]">
                        <?php 
                        $overall_avg = array_sum(array_column($all_feedback, 'rating')) / max(1, count($all_feedback));
                        echo number_format($overall_avg, 1);
                        ?>
                        /5
                    </p>
                </div>
                <div class="text-4xl">⭐</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">By Category</p>
                <div class="space-y-2">
                    <?php foreach ($feedback_by_category as $category => $items): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600"><?= htmlspecialchars($category) ?></span>
                            <span class="font-semibold text-slate-900"><?= count($items) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Rating Distribution</p>
            <div class="space-y-2">
                <?php for ($i = 5; $i >= 1; $i--): 
                    $count = count(array_filter($all_feedback, fn($f) => $f['rating'] == $i));
                    $percentage = count($all_feedback) > 0 ? intval(($count / count($all_feedback)) * 100) : 0;
                ?>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold w-6"><?= $i ?> ⭐</span>
                        <div class="flex-1 bg-slate-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <span class="text-xs text-slate-500 w-8 text-right"><?= $count ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Feedback List by Category -->
    <div class="space-y-6">
        <?php foreach (['Lab Quality', 'Admin Service', 'System Usability'] as $category): ?>
            <?php if (isset($feedback_by_category[$category])): ?>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">
                        <?= htmlspecialchars($category) ?> 
                        <span class="text-sm font-normal text-slate-500">(<?= count($feedback_by_category[$category]) ?>)</span>
                    </h2>

                    <div class="space-y-4">
                        <?php foreach ($feedback_by_category[$category] as $feedback):
                            $date = new DateTime($feedback['created_at']);
                            $formatted_date = $date->format('M d, Y H:i');
                            $initials = substr($feedback['first_name'], 0, 1) . substr($feedback['last_name'], 0, 1);
                        ?>
                            <div class="border-l-4 border-blue-500 pl-4 py-3">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            <?= htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']) ?>
                                            <span class="text-xs text-slate-500">(<?= htmlspecialchars($feedback['id_number']) ?>)</span>
                                        </p>
                                        <p class="text-xs text-slate-500"><?= $formatted_date ?></p>
                                    </div>
                                    <div class="text-lg">
                                        <?php for ($i = 0; $i < $feedback['rating']; $i++): ?>⭐<?php endfor; ?>
                                    </div>
                                </div>
                                <?php if ($feedback['comment']): ?>
                                    <p class="text-sm text-slate-700 mt-2"><?= htmlspecialchars($feedback['comment']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Empty State -->
    <?php if (count($all_feedback) === 0): ?>
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-12 text-center">
            <div class="text-5xl mb-3">📝</div>
            <p class="text-slate-600 font-semibold">No feedback yet</p>
            <p class="text-slate-500 text-sm">Feedback from students will appear here</p>
        </div>
    <?php endif; ?>

</main>

</body>
</html>
