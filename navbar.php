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
                    <img class="w-9 h-9 object-contain" src="ccs-logo.png" alt="CCS logo">
                </span>
                <span class="hidden sm:inline">CCS Sit-in System</span>
            </div>
            <span class="hidden sm:inline-flex items-center rounded-full bg-white/90 px-2.5 py-1">
                <img class="h-[26px] w-auto" src="uc-logo.png" alt="University of Cebu logo">
            </span>
        </div>

        <!-- Navigation Links -->
        <div class="flex items-center gap-5">
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Student Navigation - Minimal View -->
                <a class="<?= $navLinkClass('dashboard', $current_page) ?>" href="dashboard.php">Dashboard</a>
                
                <a class="<?= $navLinkClass('notifications', $current_page) ?> relative" href="notifications.php">
                    🔔
                    <?php 
                        require_once 'db.php';
                        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM notifications WHERE student_id = ? AND is_read = FALSE');
                        $stmt->execute([$_SESSION['user_id']]);
                        $unread = $stmt->fetch()['count'];
                        if ($unread > 0): 
                    ?>
                        <span class="absolute -top-2 -right-3 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-600 rounded-full">
                            <?= min($unread, 9) ?><?= $unread > 9 ? '+' : '' ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- More Menu Dropdown -->
                <div class="relative" id="moreMenu">
                    <button class="flex items-center gap-1 text-sm text-white/80 hover:text-white transition" type="button" onclick="document.getElementById('moreDropdown').classList.toggle('hidden')">
                        ⋯ More
                    </button>
                    <div id="moreDropdown" class="hidden absolute right-0 top-[140%] min-w-[180px] rounded-lg bg-white py-2 text-slate-800 shadow-[0_10px_25px_rgba(0,0,0,0.15)] z-10">
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'announcements') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="announcements.php">Announcements</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'sitin_history') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="sitin_history.php">Sit-in History</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'sitin_summary') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="sitin_summary.php">📊 Summary</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'reservation') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="lab_reservation.php">Lab Reservations</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'feedback') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="feedback.php">Feedback</a>
                        <a class="block px-4 py-2 text-sm hover:bg-slate-100 <?= ($current_page == 'profile') ? 'bg-blue-50 font-semibold text-[#003366]' : '' ?>" href="profile.php">My Profile</a>
                        <hr class="my-1">
                        <a class="block px-4 py-2 text-sm hover:bg-red-50 text-red-600 font-semibold" href="logout.php">Logout</a>
                    </div>
                </div>

                <script>
                    document.addEventListener('click', function(event) {
                        const moreMenu = document.getElementById('moreMenu');
                        if (!moreMenu.contains(event.target)) {
                            document.getElementById('moreDropdown').classList.add('hidden');
                        }
                    });
                </script>

            <?php else: ?>
                <!-- Guest Navigation -->
                <a class="hidden sm:inline text-sm <?= $navLinkClass('home', $current_page) ?>" href="index.php">Home</a>
                <a class="hidden sm:inline text-sm <?= $navLinkClass('about', $current_page) ?>" href="about.php">About</a>
                <a class="<?= $navLinkClass('login', $current_page) ?>" href="login.php">Login</a>
                <a class="<?= $navLinkClass('register', $current_page) ?>" href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
