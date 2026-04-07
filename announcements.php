<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get all announcements
$stmt = $pdo->query('SELECT * FROM announcements ORDER BY created_at DESC');
$announcements = $stmt->fetchAll();

$current_page = 'announcements';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-[Inter]">

<!-- Navigation -->
<?php include 'navbar.php'; ?>

<main class="max-w-4xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">📢 Announcements</h1>
        <p class="text-slate-600">Latest updates and important information from the CCS Department</p>
    </div>

    <!-- Announcements List -->
    <?php if (count($announcements) > 0): ?>
        <div class="space-y-4">
            <?php foreach ($announcements as $announcement):
                $date = new DateTime($announcement['created_at']);
                $formatted_date = $date->format('M d, Y');
                $formatted_time = $date->format('h:i A');
            ?>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 hover:shadow-md transition">
                    <div class="flex items-start justify-between mb-3">
                        <h2 class="text-xl font-bold text-slate-900 flex-1"><?= htmlspecialchars($announcement['title']) ?></h2>
                        <span class="text-xs text-slate-500 whitespace-nowrap ml-4"><?= $formatted_date ?></span>
                    </div>
                    
                    <p class="text-slate-600 mb-3 leading-relaxed"><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                    
                    <div class="text-xs text-slate-400">
                        Published at <?= $formatted_time ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-12 text-center">
            <div class="text-6xl mb-4">📭</div>
            <p class="text-slate-600 font-semibold text-lg mb-2">No Announcements Yet</p>
            <p class="text-slate-500">Check back later for important updates from the CCS Department!</p>
        </div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="mt-8">
        <a href="dashboard.php" class="inline-flex items-center rounded-lg bg-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-400 transition">
            ← Back to Dashboard
        </a>
    </div>

</main>

</body>
</html>
