<?php
// Define nav link class helper
$navLinkClass = function($page, $current) {
    $baseClass = 'text-sm transition';
    return $page === $current 
        ? "$baseClass font-semibold text-white border-b border-white/60 pb-0.5" 
        : "$baseClass text-white/80 hover:text-white";
};
?>

<nav class="bg-[#003366] text-white shadow-sm sticky top-0 z-50">
    <div class="px-[5%] py-4 flex items-center justify-between">
        
        <!-- Logo Section -->
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2.5 font-bold text-[1.2rem]">
                <span class="inline-flex items-center rounded-full bg-white/90 p-1">
                    <img class="w-9 h-9 object-contain" src="assets/images/ccs-logo.png" alt="CCS logo">
                </span>
                <span class="hidden sm:inline">CCS Sit-in System</span>
            </div>
            <span class="hidden sm:inline-flex items-center rounded-full bg-white/90 px-2.5 py-1">
                <img class="h-[26px] w-auto" src="assets/images/uc-logo.png" alt="University of Cebu logo">
            </span>
        </div>

        <!-- Navigation Links -->
        <div class="flex items-center gap-5">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <button class="sm:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-white/30 text-white/90 hover:text-white hover:bg-white/10 transition" type="button" aria-label="Toggle menu" onclick="document.getElementById('guestMobileMenu').classList.toggle('hidden')">
                    <span class="text-lg">☰</span>
                </button>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                    require_once 'db.php';
                    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM notifications WHERE student_id = ? AND is_read = FALSE');
                    $stmt->execute([$_SESSION['user_id']]);
                    $unread = $stmt->fetch()['count'];

                    $recent_stmt = $pdo->prepare('SELECT id, title, message, type, is_read, created_at FROM notifications WHERE student_id = ? ORDER BY created_at DESC LIMIT 5');
                    $recent_stmt->execute([$_SESSION['user_id']]);
                    $recent_notifications = $recent_stmt->fetchAll();
                ?>

                <button class="sm:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-white/30 text-white/90 hover:text-white hover:bg-white/10 transition" type="button" aria-label="Toggle menu" onclick="document.getElementById('studentMobileMenu').classList.toggle('hidden')">
                    <span class="text-lg">☰</span>
                </button>

                <!-- Student Navigation - Minimal View -->
                <a class="hidden sm:inline-flex <?= $navLinkClass('dashboard', $current_page) ?> transition-all duration-300 hover:scale-105" href="dashboard.php">Dashboard</a>
                
                <div class="relative hidden sm:inline-flex" id="notifMenu">
                    <button class="<?= $navLinkClass('notifications', $current_page) ?> relative transition-all duration-300 hover:scale-110 rounded-lg px-1.5 py-1 active:ring-2 active:ring-white/70 focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-[#003366]" type="button" aria-label="Notifications" onclick="document.getElementById('notifDropdown').classList.toggle('hidden')">
                        🔔
                        <?php if ($unread > 0): ?>
                            <span class="absolute -top-2 -right-3 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-600 rounded-sm border border-white/80">
                                <?= min($unread, 9) ?><?= $unread > 9 ? '+' : '' ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <div id="notifDropdown" class="hidden absolute right-0 top-[140%] w-[320px] rounded-xl bg-white text-slate-800 shadow-[0_12px_30px_rgba(0,0,0,0.2)] border border-slate-200 z-20">
                        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">Notifications</p>
                            <span class="text-xs text-slate-500"><?= $unread ?> unread</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <?php if (!empty($recent_notifications)): ?>
                                <?php foreach ($recent_notifications as $notif): ?>
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
                            <a class="text-sm font-semibold text-blue-700 hover:text-blue-800" href="notifications.php">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- More Menu Dropdown -->
                <div class="relative hidden sm:inline-flex" id="moreMenu">
                    <button class="flex items-center gap-1 text-sm text-white/80 hover:text-white transition-all duration-300 hover:scale-105" type="button" onclick="document.getElementById('moreDropdown').classList.toggle('hidden')">
                        ⋯ More
                    </button>
                    <div id="moreDropdown" class="hidden absolute right-0 top-[140%] min-w-[180px] rounded-lg bg-white py-2 text-slate-800 shadow-[0_10px_25px_rgba(0,0,0,0.15)] z-10 animate-fadeIn">
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors duration-200 <?= ($current_page == 'announcements') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="announcements.php">Announcements</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors duration-200 <?= ($current_page == 'sitin_history') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="sitin_history.php">Sit-in History</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors duration-200 <?= ($current_page == 'sitin_summary' || $current_page == 'detailed_sessions') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="detailed_sessions.php">📋 Session Details</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors duration-200 <?= ($current_page == 'summary') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="sitin_summary.php">📊 Summary</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors duration-200 <?= ($current_page == 'reservation') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="lab_reservation.php">Lab Reservations</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors duration-200 <?= ($current_page == 'feedback') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="feedback.php">Feedback</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors duration-200 <?= ($current_page == 'profile') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="profile.php">My Profile</a>
                        <hr class="my-1">
                        <a class="block px-4 py-2 text-sm hover:bg-red-50 transition-colors duration-200 text-red-600 font-semibold" href="logout.php">Logout</a>
                    </div>
                </div>

                <script>
                    document.addEventListener('click', function(event) {
                        const moreMenu = document.getElementById('moreMenu');
                        const notifMenu = document.getElementById('notifMenu');

                        if (moreMenu && !moreMenu.contains(event.target)) {
                            document.getElementById('moreDropdown').classList.add('hidden');
                        }
                        if (notifMenu && !notifMenu.contains(event.target)) {
                            document.getElementById('notifDropdown').classList.add('hidden');
                        }
                    });
                </script>

            <?php else: ?>
                <!-- Guest Navigation -->
                <a class="hidden sm:inline text-sm <?= $navLinkClass('home', $current_page) ?> transition-all duration-300 hover:scale-105" href="index.php">Home</a>
                <a class="hidden sm:inline text-sm <?= $navLinkClass('leaderboards', $current_page) ?> transition-all duration-300 hover:scale-110 hover:drop-shadow-md" href="leaderboards.php">Leaderboards</a>
                <a class="hidden sm:inline text-sm <?= $navLinkClass('about', $current_page) ?> transition-all duration-300 hover:scale-105" href="about.php">About</a>
                <a class="text-sm <?= $navLinkClass('login', $current_page) ?> transition-all duration-300 hover:scale-105" href="login.php">Login</a>
                <a class="px-4 py-2 rounded-lg font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 transition-all duration-300 hover:shadow-lg hover:scale-105 text-sm" href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <div id="guestMobileMenu" class="sm:hidden hidden px-[5%] pb-4">
            <div class="mt-2 rounded-xl bg-white/10 border border-white/20 p-3 flex flex-col gap-2">
                <a class="text-sm <?= $navLinkClass('home', $current_page) ?>" href="index.php">Home</a>
                <a class="text-sm <?= $navLinkClass('leaderboards', $current_page) ?>" href="leaderboards.php">Leaderboards</a>
                <a class="text-sm <?= $navLinkClass('about', $current_page) ?>" href="about.php">About</a>
                <a class="text-sm <?= $navLinkClass('login', $current_page) ?>" href="login.php">Login</a>
                <a class="text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 transition-all duration-300 rounded-lg px-3 py-2 text-center" href="register.php">Register</a>
            </div>
        </div>
    <?php else: ?>
        <div id="studentMobileMenu" class="sm:hidden hidden px-[5%] pb-4">
            <div class="mt-2 rounded-xl bg-white/10 border border-white/20 p-3 flex flex-col gap-2">
                <a class="text-sm <?= $navLinkClass('dashboard', $current_page) ?>" href="dashboard.php">Dashboard</a>
                <a class="text-sm <?= $navLinkClass('notifications', $current_page) ?> flex items-center justify-between" href="notifications.php">
                    <span>Notifications</span>
                    <?php if ($unread > 0): ?>
                        <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-600 rounded-sm border border-white/80">
                            <?= min($unread, 9) ?><?= $unread > 9 ? '+' : '' ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a class="text-sm <?= $navLinkClass('announcements', $current_page) ?>" href="announcements.php">Announcements</a>
                <a class="text-sm <?= $navLinkClass('sitin_history', $current_page) ?>" href="sitin_history.php">Sit-in History</a>
                <a class="text-sm <?= $navLinkClass('detailed_sessions', $current_page) ?>" href="detailed_sessions.php">Session Details</a>
                <a class="text-sm <?= $navLinkClass('summary', $current_page) ?>" href="sitin_summary.php">Summary</a>
                <a class="text-sm <?= $navLinkClass('reservation', $current_page) ?>" href="lab_reservation.php">Lab Reservations</a>
                <a class="text-sm <?= $navLinkClass('feedback', $current_page) ?>" href="feedback.php">Feedback</a>
                <a class="text-sm <?= $navLinkClass('profile', $current_page) ?>" href="profile.php">My Profile</a>
                <a class="text-sm font-semibold text-red-100 bg-red-500/80 hover:bg-red-500 transition-all duration-300 rounded-lg px-3 py-2 text-center" href="logout.php">Logout</a>
            </div>
        </div>
    <?php endif; ?>
</nav>
