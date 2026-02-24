<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* ============================================
           PAGE ANIMATIONS
           Add these classes to any HTML element to
           make it animate when the page first loads.
           ============================================ */

        /* fadeIn — element goes from invisible to visible */
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
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- ================================
     NAVIGATION BAR
     ================================ -->

    <?php 
        // 1. Give this page a nametag
        $current_page = 'login'; 
        
        // 2. Stamp the navigation bar right here
        include 'navbar.php'; 
    ?>


<?php
// ================================================
// LOGIN FORM HANDLER
// This PHP code runs on the SERVER before the HTML
// is sent to the browser. The user never sees this.
// ================================================

// Start with empty messages (no errors yet)
$error   = '';
$success = '';

// Only run when the user clicks "Sign In" (form sends a POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read what the user typed and remove extra spaces
    $id_number = trim($_POST['id_number'] ?? '');
    $password  = trim($_POST['password'] ?? '');

    // Check that both fields are filled in before touching the DB
    if ($id_number === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        // -----------------------------------------------
        // TODO: Connect to the database and verify login
        // -----------------------------------------------
        // Step 1: Find the student by their ID number
        // $stmt = $pdo->prepare("SELECT * FROM students WHERE id_number = ?");
        // $stmt->execute([$id_number]);
        // $user = $stmt->fetch();
        //
        // Step 2: Check the password
        //   password_verify() safely compares the typed password
        //   against the hashed password stored in the database.
        // if ($user && password_verify($password, $user['password'])) {
        //     $_SESSION['user_id'] = $user['id']; // remember the user is logged in
        //     header('Location: dashboard.php');  // send them to the dashboard
        //     exit;
        // } else {
        //     $error = 'Invalid ID number or password.';
        // }
        // -----------------------------------------------
        $error = 'Database connection not configured yet.';
    }
}
?>

<!-- ================================
     LOGIN FORM SECTION
     White card with two columns:
       Left  = the form fields
       Right = info panel (hidden on small screens)
     anim-slide = card slides up when page loads
     ================================ -->
<section class="min-h-[calc(100vh-86px)] flex items-center px-5 py-10 bg-[radial-gradient(1000px_420px_at_50%_-10%,#eef2ff_0%,#f8fafc_55%,#f8fafc_100%)]">
    <div class="w-full">
        <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-[24px] shadow-[0_20px_45px_rgba(15,23,42,0.08)] p-6 sm:p-8 lg:p-10 anim-slide delay-1">
            <div class="grid gap-8 lg:grid-cols-2 items-center">

                <!-- Form column -->
                <div>
                    <a class="inline-flex items-center rounded-md bg-red-500 px-3 py-1 text-xs font-semibold text-white" href="index.php">Back</a>
                    <h1 class="text-2xl font-bold text-slate-900 mt-3 mb-1">Welcome back</h1>
                    <p class="text-sm text-slate-500 mb-5">Sign in to your CCS Sit-in account.</p>

                    <?php /* Show a red alert box if something went wrong */ ?>
                    <?php if ($error): ?>
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        <?= htmlspecialchars($error) /* htmlspecialchars stops XSS attacks */ ?>
                    </div>
                    <?php endif; ?>

                    <?php /* Show a green alert box on success */ ?>
                    <?php if ($success): ?>
                    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        <?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>

                    <form action="login.php" method="post" class="grid gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="id_number">ID Number</label>
                            <input
                                class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60"
                                id="id_number"
                                name="id_number"
                                type="text"
                                placeholder="e.g. 2024-00001"
                                value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>"
                                required
                                autofocus   
                            >
                        </div>

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

                    <p class="mt-5 text-xs text-slate-500">
                        Don't have an account?
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

</body>
</html>
