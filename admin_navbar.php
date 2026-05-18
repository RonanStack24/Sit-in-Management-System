<?php
// Admin Navigation Bar
// Define active page class helper
$adminNavClass = function($page, $current) {
    return $page === $current 
        ? 'px-3 py-2 text-sm font-semibold bg-white/20 rounded hover:bg-white/30 transition border-b-2 border-white'
        : 'px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition';
};

require_once 'db.php';
$admin_unread = 0;
$recent_admin_notifications = [];
if (isset($_SESSION['admin_id'])) {
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM admin_notifications WHERE admin_id = ? AND is_read = FALSE');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin_unread = $stmt->fetch()['count'];

    $recent_stmt = $pdo->prepare('SELECT id, title, message, type, is_read, created_at FROM admin_notifications WHERE admin_id = ? ORDER BY created_at DESC LIMIT 5');
    $recent_stmt->execute([$_SESSION['admin_id']]);
    $recent_admin_notifications = $recent_stmt->fetchAll();
}
?>

<!-- Admin Navigation Bar -->
<nav class="bg-[#003366] text-white shadow-lg sticky top-0 z-50">
    <div class="px-[5%] py-4">
        <!-- Header with Title and Logout -->
        <div class="flex items-center justify-between mb-3">
            <h1 class="font-bold text-xl">CCS Admin</h1>
            <div class="flex items-center gap-2">
                <button class="sm:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-white/30 text-white/90 hover:text-white hover:bg-white/10 transition" type="button" aria-label="Toggle menu" onclick="document.getElementById('adminMobileMenu').classList.toggle('hidden')">
                    <span class="text-lg">☰</span>
                </button>
                <a href="admin_logout.php" class="text-sm bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded transition">Logout</a>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="hidden sm:flex gap-2 flex-wrap">
            <a href="admin_home.php" class="<?= $adminNavClass('admin_home', $current_page) ?>">Home</a>
            <a href="admin_dashboard.php" class="<?= $adminNavClass('admin_dashboard', $current_page) ?>">Record Sit-in</a>
            <a href="admin_current_sitin.php" class="<?= $adminNavClass('admin_current_sitin', $current_page) ?>">Current Sit-ins</a>
            <a href="admin_history.php" class="<?= $adminNavClass('admin_history', $current_page) ?>">Sit-in History</a>
            
            <!-- More Menu Dropdown for Remaining Items -->
            <div class="relative" id="adminMoreMenu">
                <button class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition" type="button" onclick="document.getElementById('adminMoreDropdown').classList.toggle('hidden')">
                    ⋯ More
                </button>
                <div id="adminMoreDropdown" class="hidden absolute left-0 top-[140%] min-w-[200px] rounded-lg bg-white py-2 text-slate-800 shadow-[0_10px_25px_rgba(0,0,0,0.15)] z-10">
                    <a href="admin_announcements.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_announcements') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Announcements</a>
                    <a href="admin_feedback.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_feedback') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Feedback</a>
                    <a href="admin_reservations.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_reservations') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Reservations</a>
                    <a href="admin_manage_reservations.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'manage_reservations') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Manage Access</a>
                    <a href="admin_reports.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_reports') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Reports</a>
                    <a href="admin_students.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_students') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Students</a>
                </div>
            </div>

            <!-- Notifications with Badge -->
            <div class="relative" id="adminNotifMenu">
                <button class="<?= $adminNavClass('admin_notifications', $current_page) ?> relative rounded-lg px-3 py-2" type="button" aria-label="Notifications" onclick="document.getElementById('adminNotifDropdown').classList.toggle('hidden')">
                    🔔 Notifications
                    <?php if ($admin_unread > 0): ?>
                        <span class="absolute -top-2 -right-2 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-600 rounded-sm border border-white/80">
                            <?= min($admin_unread, 9) ?><?= $admin_unread > 9 ? '+' : '' ?>
                        </span>
                    <?php endif; ?>
                </button>

                <div id="adminNotifDropdown" class="hidden absolute right-0 top-[140%] w-[320px] rounded-xl bg-white text-slate-800 shadow-[0_12px_30px_rgba(0,0,0,0.2)] border border-slate-200 z-20">
                    <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">Notifications</p>
                        <span class="text-xs text-slate-500"><?= $admin_unread ?> unread</span>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        <?php if (!empty($recent_admin_notifications)): ?>
                            <?php foreach ($recent_admin_notifications as $notif): ?>
                                <?php
                                    $date = new DateTime($notif['created_at']);
                                    $time_label = $date->format('M d, h:i A');
                                ?>
                                <div class="px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition">
                                    <div class="flex items-start gap-3">
                                        <span class="text-sm"><?= htmlspecialchars($notif['type']) ?></span>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-slate-900 line-clamp-1">
                                                <?= htmlspecialchars($notif['title']) ?>
                                            </p>
                                            <p class="text-xs text-slate-600 line-clamp-2">
                                                <?= htmlspecialchars($notif['message']) ?>
                                            </p>
                                            <div class="mt-1 flex items-center justify-between">
                                                <span class="text-[11px] text-slate-400"><?= $time_label ?></span>
                                                <?php if (!$notif['is_read']): ?>
                                                    <span class="text-[11px] text-blue-600 font-semibold">New</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="px-4 py-6 text-center text-sm text-slate-500">No notifications yet</div>
                        <?php endif; ?>
                    </div>
                    <div class="px-4 py-2 border-t border-slate-200">
                        <a class="text-sm font-semibold text-blue-700 hover:text-blue-800" href="admin_notifications.php">View all notifications</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="adminMobileMenu" class="sm:hidden hidden pb-4">
        <div class="mt-2 rounded-xl bg-white/10 border border-white/20 p-3 flex flex-col gap-2">
            <a href="admin_home.php" class="<?= $adminNavClass('admin_home', $current_page) ?>">Home</a>
            <a href="admin_dashboard.php" class="<?= $adminNavClass('admin_dashboard', $current_page) ?>">Record Sit-in</a>
            <a href="admin_current_sitin.php" class="<?= $adminNavClass('admin_current_sitin', $current_page) ?>">Current Sit-ins</a>
            <a href="admin_history.php" class="<?= $adminNavClass('admin_history', $current_page) ?>">Sit-in History</a>
            <a href="admin_announcements.php" class="<?= $adminNavClass('admin_announcements', $current_page) ?>">Announcements</a>
            <a href="admin_feedback.php" class="<?= $adminNavClass('admin_feedback', $current_page) ?>">Feedback</a>
            <a href="admin_reservations.php" class="<?= $adminNavClass('admin_reservations', $current_page) ?>">Reservations</a>
            <a href="admin_manage_reservations.php" class="<?= $adminNavClass('manage_reservations', $current_page) ?>">Manage Access</a>
            <a href="admin_reports.php" class="<?= $adminNavClass('admin_reports', $current_page) ?>">Reports</a>
            <a href="admin_students.php" class="<?= $adminNavClass('admin_students', $current_page) ?>">Students</a>
            <a href="admin_notifications.php" class="<?= $adminNavClass('admin_notifications', $current_page) ?> flex items-center justify-between">
                <span>Notifications</span>
                <?php if ($admin_unread > 0): ?>
                    <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-600 rounded-sm border border-white/80">
                        <?= min($admin_unread, 9) ?><?= $admin_unread > 9 ? '+' : '' ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('click', function(event) {
        const adminMoreMenu = document.getElementById('adminMoreMenu');
        const adminNotifMenu = document.getElementById('adminNotifMenu');

        if (adminMoreMenu && !adminMoreMenu.contains(event.target)) {
            document.getElementById('adminMoreDropdown').classList.add('hidden');
        }
        if (adminNotifMenu && !adminNotifMenu.contains(event.target)) {
            document.getElementById('adminNotifDropdown').classList.add('hidden');
        }
    });
</script>
