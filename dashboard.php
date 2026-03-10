<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch fresh student data from DB
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<?php
    $current_page = 'dashboard';
    include 'navbar.php';
?>

<main class="max-w-5xl mx-auto px-5 py-10">

    <!-- Greeting -->
    <h1 class="text-2xl font-bold text-slate-900 mb-1">
        Welcome, <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>!
    </h1>
    <p class="text-sm text-slate-500 mb-8">Here's your sit-in overview.</p>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-10">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Sessions Left</p>
            <p class="text-4xl font-bold text-[#003366]"><?= (int)$student['sessions_left'] ?></p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">ID Number</p>
            <p class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($student['id_number']) ?></p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Course</p>
            <p class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($student['course']) ?></p>
        </div>
    </div>

    <!-- Student details -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wide">Your Information</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div><dt class="text-slate-500">Full Name</dt><dd class="font-semibold"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></dd></div>
            <div><dt class="text-slate-500">Email</dt><dd class="font-semibold"><?= htmlspecialchars($student['email']) ?></dd></div>
            <div><dt class="text-slate-500">Course Level</dt><dd class="font-semibold"><?= htmlspecialchars($student['course_level'] ?? '—') ?></dd></div>
            <div><dt class="text-slate-500">Address</dt><dd class="font-semibold"><?= htmlspecialchars($student['address'] ?? '—') ?></dd></div>
        </dl>
    </div>

    <!-- Logout -->
    <div class="mt-8">
        <a href="logout.php" class="inline-flex items-center rounded-lg bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600 transition">
            Sign Out
        </a>
    </div>

</main>

<!-- Toast notification -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 bg-[#003366] text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg">
    <svg class="w-5 h-5 shrink-0 text-green-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    <span id="toast-msg"></span>
</div>

<?php if (($_GET['toast'] ?? '') === 'login'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    showToast('Welcome back, <?= htmlspecialchars($student['first_name'], ENT_QUOTES) ?>! You are now logged in.');
});
</script>
<?php endif; ?>

<script>
function showToast(msg) {
    var t = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    t.classList.remove('hidden');
    t.classList.add('flex');
    setTimeout(function () {
        t.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        setTimeout(function () { t.classList.add('hidden'); t.classList.remove('flex', 'opacity-0'); }, 500);
    }, 3500);
}
</script>

</body>
</html>
