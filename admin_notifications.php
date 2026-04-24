<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$current_page = 'admin_notifications';

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'mark_read' && isset($_POST['notification_id'])) {
            $stmt = $pdo->prepare('UPDATE admin_notifications SET is_read = TRUE WHERE id = ? AND admin_id = ?');
            $stmt->execute([$_POST['notification_id'], $admin_id]);
        } elseif ($_POST['action'] === 'mark_all_read') {
            $stmt = $pdo->prepare('UPDATE admin_notifications SET is_read = TRUE WHERE admin_id = ?');
            $stmt->execute([$admin_id]);
        } elseif ($_POST['action'] === 'delete' && isset($_POST['notification_id'])) {
            $stmt = $pdo->prepare('DELETE FROM admin_notifications WHERE id = ? AND admin_id = ?');
            $stmt->execute([$_POST['notification_id'], $admin_id]);
        }
    } catch (PDOException $e) {
        error_log('Admin notification action error: ' . $e->getMessage());
    }
}

// Get filter type from query parameter
$filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$valid_filters = ['all', 'Reservation', 'Feedback', 'Sit-in', 'System'];
if (!in_array($filter, $valid_filters)) {
    $filter = 'all';
}

// Get notifications
if ($filter === 'all') {
    $stmt = $pdo->prepare('SELECT * FROM admin_notifications WHERE admin_id = ? ORDER BY created_at DESC LIMIT 100');
    $stmt->execute([$admin_id]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM admin_notifications WHERE admin_id = ? AND type = ? ORDER BY created_at DESC LIMIT 100');
    $stmt->execute([$admin_id, $filter]);
}
$notifications = $stmt->fetchAll();

// Count unread notifications
$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM admin_notifications WHERE admin_id = ? AND is_read = FALSE');
$stmt->execute([$admin_id]);
$unread_count = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | CCS Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
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
            <a href="admin_history.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Sit-in History</a>
            <a href="admin_announcements.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Announcements</a>
            <a href="admin_feedback.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Feedback</a>
            <a href="admin_reservations.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reservations</a>
            <a href="admin_reports.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reports</a>
            <a href="admin_students.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Students</a>
            <a href="admin_notifications.php" class="px-3 py-2 text-sm font-semibold bg-white/20 rounded hover:bg-white/30 transition border-b-2 border-white">🔔 Notifications</a>
        </div>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-5 py-10">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">🔔 Admin Notifications</h1>
        <p class="text-slate-600">Stay updated with reservations, feedback, and system alerts</p>
    </div>

    <!-- Unread Count & Actions -->
    <div class="bg-white border border-slate-200 rounded-lg p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="bg-blue-100 rounded-full w-10 h-10 flex items-center justify-center">
                <span class="text-blue-900 font-bold"><?= $unread_count ?></span>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-900">Unread Notifications</p>
                <p class="text-xs text-slate-600"><?= $unread_count === 0 ? 'All caught up!' : 'You have new updates' ?></p>
            </div>
        </div>
        <?php if ($unread_count > 0): ?>
            <form method="POST" class="inline">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition font-semibold">
                    Mark All as Read
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-lg p-4 mb-6 flex flex-wrap gap-2">
        <a href="?type=all" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= ($filter === 'all') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
            All
        </a>
        <a href="?type=Reservation" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= ($filter === 'Reservation') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
            📅 Reservations
        </a>
        <a href="?type=Feedback" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= ($filter === 'Feedback') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
            💬 Feedback
        </a>
        <a href="?type=Sit-in" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= ($filter === 'Sit-in') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
            🪑 Sit-in
        </a>
        <a href="?type=System" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= ($filter === 'System') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
            ⚙️ System
        </a>
    </div>

    <!-- Notifications List -->
    <?php if (count($notifications) > 0): ?>
        <div class="space-y-3">
            <?php foreach ($notifications as $notif):
                $date = new DateTime($notif['created_at']);
                $formatted_time = $date->format('M d, Y h:i A');
                
                // Icon based on type
                $icons = [
                    'Reservation' => '📅',
                    'Feedback' => '💬',
                    'Sit-in' => '🪑',
                    'System' => '⚙️'
                ];
                $icon = $icons[$notif['type']] ?? '📬';
                
                // Color based on type
                $colors = [
                    'Reservation' => 'border-l-4 border-l-green-500 bg-green-50',
                    'Feedback' => 'border-l-4 border-l-purple-500 bg-purple-50',
                    'Sit-in' => 'border-l-4 border-l-blue-500 bg-blue-50',
                    'System' => 'border-l-4 border-l-slate-500 bg-slate-50'
                ];
                $color_class = $colors[$notif['type']] ?? 'border-l-4 border-l-slate-500 bg-slate-50';
            ?>
                <div class="bg-white rounded-lg p-5 shadow-sm hover:shadow-md transition <?= $color_class ?> <?= !$notif['is_read'] ? 'ring-2 ring-blue-300' : '' ?>">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            <span class="text-2xl mt-1"><?= $icon ?></span>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($notif['title']) ?></h3>
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-slate-700 mb-3"><?= htmlspecialchars($notif['message']) ?></p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-500"><?= $formatted_time ?></span>
                                    <div class="flex gap-2">
                                        <?php if (!$notif['is_read']): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                                <button type="submit" class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition font-semibold">
                                                    Mark as Read
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this notification?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                            <button type="submit" class="text-xs px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition font-semibold">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white border border-slate-200 rounded-lg p-12 text-center">
            <div class="text-5xl mb-4">📭</div>
            <h2 class="text-xl font-bold text-slate-900 mb-2">No Notifications</h2>
            <p class="text-slate-600">Everything is quiet. Check back later!</p>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
