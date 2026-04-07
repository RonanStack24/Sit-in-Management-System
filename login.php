<?php
session_start();
require 'db.php';

$error   = '';
$success = '';
$logout_message = '';

// Check for logout message
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $logout_message = 'You have been logged out.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $user_found = false;

        // First check if it's an admin (username login)
        try {
            $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['is_admin'] = true;
                header('Location: admin_home.php');
                exit;
            }
        } catch (Exception $e) {
            // Continue to check students
        }

        // If not admin, check if it's a student (ID number login)
        $stmt = $pdo->prepare('SELECT * FROM students WHERE id_number = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['id_number']  = $user['id_number'];
            $_SESSION['first_name'] = $user['first_name'];
            header('Location: dashboard.php?toast=login');
            exit;
        } else {
            $error = 'Invalid username/ID number or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.6/tailwind.min.css">
    <style>
        /* ============================================
           PAGE ANIMATIONS
           Add these classes to any HTML element to
           make it animate when the page first loads.
           ============================================ */

        /* fadeIn ΓÇö element goes from invisible to visible */
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* slideUp — element fades in while rising up from below */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Use .anim-fade for a simple fade, .anim-slide for slide + fade */
        .anim-fade  { animation: fadeIn  0.6s ease both; }
        .anim-slide { animation: slideUp 0.55s ease both; }

        /* Delay class — card starts animating slightly after the page loads */
        .delay-1 { animation-delay: 0.1s; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-[Inter]">

<!-- ================================
     NAVIGATION BAR
     ================================ -->

    <?php 
        $current_page = 'login'; 
        include 'navbar.php'; 
    ?>

<!-- ================================
     LOGIN FORM SECTION
     ================================ -->
<section class="min-h-[calc(100vh-86px)] flex items-center px-5 py-10 bg-[radial-gradient(1000px_420px_at_50%_-10%,#eef2ff_0%,#f8fafc_55%,#f8fafc_100%)]">
    <div class="w-full">
        <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-[24px] shadow-[0_20px_45px_rgba(15,23,42,0.08)] p-6 sm:p-8 lg:p-10 anim-slide delay-1">
            <div class="grid gap-8 lg:grid-cols-2 items-center">

                <!-- Form column -->
                <div>
                    <a class="inline-flex items-center rounded-md bg-red-500 px-3 py-1 text-xs font-semibold text-white" href="index.php">Back</a>
                    <h1 class="text-2xl font-bold text-slate-900 mt-3 mb-1">Sign In</h1>
                    <p class="text-sm text-slate-500 mb-5">Enter your credentials to log in.</p>

                    <?php /* Show a red alert box if something went wrong */ ?>
                    <?php if ($error): ?>
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <?php /* Show a green alert box on success */ ?>
                    <?php if ($success): ?>
                    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        <?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>

                    <form action="login.php" method="post" class="grid gap-4">
                        <!-- Username/ID Number Input -->
                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="username">Username or Student ID</label>
                            <input
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60"
                                id="username"
                                name="username"
                                type="text"
                                placeholder="e.g. 2024-00001 (student) or admin (admin)"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                autocomplete="username"
                                required
                                autofocus   
                            >
                        </div>

                        <!-- Password Input -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-slate-600" for="password">Password</label>
                                <a href="forgot-password.php" class="text-xs text-indigo-600 hover:underline">Forgot password?</a>
                            </div>
                            <input
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60"
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="remember" name="remember" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="remember" class="text-xs text-slate-600">Remember me</label>
                        </div>

                        <button
                            class="mt-1 inline-flex items-center justify-center rounded-lg bg-[#003366] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#004b93] transition"
                            type="submit"
                        >
                            Sign In
                        </button>
                    </form>

                    <!-- Student register link -->
                    <p class="mt-5 text-xs text-slate-500">
                        Don't have a student account?
                        <a href="register.php" class="text-indigo-600 font-semibold hover:underline">Register here</a>
                    </p>
                </div>

                <!-- Illustration column -->
                <div class="hidden lg:flex flex-col items-center justify-center gap-6 text-center">
                    <div class="w-full max-w-[320px] bg-[#003366] rounded-[22px] px-8 py-10 text-white">
                        <div class="text-4xl mb-4">🖥️</div>
                        <h2 class="text-lg font-bold mb-2">CCS Laboratory</h2>
                        <p class="text-sm text-white/75 leading-relaxed">
                            Monitor your sit-in sessions, track remaining hours, and reserve your lab slot all in one place.
                        </p>
                        <div class="mt-6 grid grid-cols-3 gap-3 text-center">
                            <div class="bg-white/10 rounded-xl px-2 py-3">
                                <div class="text-xl font-bold">30</div>
                                <div class="text-[10px] text-white/70 mt-0.5">Max Sessions</div>
                            </div>
                            <div class="bg-white/10 rounded-xl px-2 py-3">
                                <div class="text-xl font-bold">5</div>
                                <div class="text-[10px] text-white/70 mt-0.5">Labs</div>
                            </div>
                            <div class="bg-white/10 rounded-xl px-2 py-3">
                                <div class="text-xl font-bold">24/7</div>
                                <div class="text-[10px] text-white/70 mt-0.5">Monitoring</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Toast notification -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 bg-[#003366] text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg">
    <svg class="w-5 h-5 shrink-0 text-green-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    <span id="toast-msg"></span>
</div>

<?php if ($logout_message): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    showToast('<?= htmlspecialchars($logout_message, ENT_QUOTES) ?>');
});
</script>
<?php endif; ?>

</body>
</html>
