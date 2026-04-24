<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['user_id'];
$current_page = 'notifications';

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'mark_read' && isset($_POST['notification_id'])) {
            $stmt = $pdo->prepare('UPDATE notifications SET is_read = TRUE WHERE id = ? AND student_id = ?');
            $stmt->execute([$_POST['notification_id'], $student_id]);
        } elseif ($_POST['action'] === 'mark_all_read') {
            $stmt = $pdo->prepare('UPDATE notifications SET is_read = TRUE WHERE student_id = ?');
            $stmt->execute([$student_id]);
        } elseif ($_POST['action'] === 'delete' && isset($_POST['notification_id'])) {
            $stmt = $pdo->prepare('DELETE FROM notifications WHERE id = ? AND student_id = ?');
            $stmt->execute([$_POST['notification_id'], $student_id]);
        }
    } catch (PDOException $e) {
        error_log('Notification action error: ' . $e->getMessage());
    }
}

// Get filter type from query parameter
$filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$valid_filters = ['all', 'Announcement', 'Reservation', 'Sit-in', 'System'];
if (!in_array($filter, $valid_filters)) {
    $filter = 'all';
}

// Get notifications
if ($filter === 'all') {
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE student_id = ? ORDER BY created_at DESC LIMIT 100');
    $stmt->execute([$student_id]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE student_id = ? AND type = ? ORDER BY created_at DESC LIMIT 100');
    $stmt->execute([$student_id, $filter]);
}
$notifications = $stmt->fetchAll();

// Count unread notifications
$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM notifications WHERE student_id = ? AND is_read = FALSE');
$stmt->execute([$student_id]);
$unread_count = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- Navigation -->
<?php include 'navbar.php'; ?>

<main class="max-w-5xl mx-auto px-5 py-10">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">🔔 Notifications</h1>
        <p class="text-slate-600">Stay updated with announcements, reservations, and sit-in updates</p>
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
        <a href="?type=Announcement" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= ($filter === 'Announcement') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
            📢 Announcements
        </a>
        <a href="?type=Reservation" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= ($filter === 'Reservation') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
            📅 Reservations
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
                    'Announcement' => '📢',
                    'Reservation' => '📅',
                    'Sit-in' => '🪑',
                    'System' => '⚙️'
                ];
                $icon = $icons[$notif['type']] ?? '📬';
                
                // Color based on type
                $colors = [
                    'Announcement' => 'border-l-4 border-l-amber-500 bg-amber-50',
                    'Reservation' => 'border-l-4 border-l-green-500 bg-green-50',
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
            <p class="text-slate-600">You're all set! Check back later for updates.</p>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
