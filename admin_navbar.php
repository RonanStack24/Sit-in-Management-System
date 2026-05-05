<?php
// Admin Navigation Bar
// Define active page class helper
$adminNavClass = function($page, $current) {
    return $page === $current 
        ? 'px-3 py-2 text-sm font-semibold bg-white/20 rounded hover:bg-white/30 transition border-b-2 border-white'
        : 'px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition';
};
?>

<!-- Admin Navigation Bar -->
<nav class="bg-[#003366] text-white shadow-lg sticky top-0 z-50">
    <div class="px-[5%] py-4">
        <!-- Header with Title and Logout -->
        <div class="flex items-center justify-between mb-3">
            <h1 class="font-bold text-xl">CCS Admin</h1>
            <a href="admin_logout.php" class="text-sm bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded transition">Logout</a>
        </div>

        <!-- Main Navigation -->
        <div class="flex gap-2 flex-wrap">
            <a href="admin_home.php" class="<?= $adminNavClass('admin_home', $current_page) ?>">Home</a>
            <a href="admin_dashboard.php" class="<?= $adminNavClass('admin_dashboard', $current_page) ?>">Record Sit-in</a>
            <a href="admin_current_sitin.php" class="<?= $adminNavClass('admin_current_sitin', $current_page) ?>">Current Sit-ins</a>
            <a href="admin_history.php" class="<?= $adminNavClass('admin_history', $current_page) ?>">Sit-in History</a>
            
            <!-- More Menu Dropdown for Remaining Items -->
            <div class="relative" id="adminMoreMenu">
                <button class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition" type="button" onclick="document.getElementById('adminMoreDropdown').classList.toggle('hidden')">
                    ⋯ More
                </button>
                <div id="adminMoreDropdown" class="hidden absolute left-0 top-[140%] min-w-[180px] rounded-lg bg-white py-2 text-slate-800 shadow-[0_10px_25px_rgba(0,0,0,0.15)] z-10">
                    <a href="admin_announcements.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_announcements') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Announcements</a>
                    <a href="admin_feedback.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_feedback') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Feedback</a>
                    <a href="admin_reservations.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_reservations') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Reservations</a>
                    <a href="admin_reports.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_reports') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Reports</a>
                    <a href="admin_students.php" class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'admin_students') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>">Students</a>
                </div>
            </div>

            <!-- Notifications with Badge -->
            <a href="admin_notifications.php" class="<?= $adminNavClass('admin_notifications', $current_page) ?> relative" id="adminNotifLink">
                🔔 Notifications
                <?php 
                    require_once 'db.php';
                    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM admin_notifications WHERE admin_id = ? AND is_read = FALSE');
                    $stmt->execute([$_SESSION['admin_id']]);
                    $unread = $stmt->fetch()['count'];
                    if ($unread > 0): 
                ?>
                    <span class="absolute -top-2 -right-2 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-600 rounded-full">
                        <?= min($unread, 9) ?><?= $unread > 9 ? '+' : '' ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('click', function(event) {
        const adminMoreMenu = document.getElementById('adminMoreMenu');
        if (adminMoreMenu && !adminMoreMenu.contains(event.target)) {
            document.getElementById('adminMoreDropdown').classList.add('hidden');
        }
    });
</script>
