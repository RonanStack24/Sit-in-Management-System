<?php
session_start();
require 'db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number    = trim($_POST['id_number']      ?? '');
    $last_name    = trim($_POST['last_name']       ?? '');
    $first_name   = trim($_POST['first_name']      ?? '');
    $middle_name  = trim($_POST['middle_name']     ?? '');
    $course       = trim($_POST['course']          ?? '');
    $course_level = trim($_POST['course_level']    ?? '');
    $email        = trim($_POST['email']           ?? '');
    $address      = trim($_POST['address']         ?? '');
    $password     = $_POST['password']             ?? '';
    $confirm      = $_POST['confirm_password']     ?? '';

    if (!$id_number || !$last_name || !$first_name || !$course || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $check = $pdo->prepare('SELECT id FROM students WHERE id_number = ? OR email = ? LIMIT 1');
        $check->execute([$id_number, $email]);
        if ($check->fetch()) {
            $error = 'That ID number or email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO students (id_number, last_name, first_name, middle_name, course, course_level, email, address, password)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$id_number, $last_name, $first_name, $middle_name, $course, $course_level, $email, $address, $hash]);
            $success = 'Account created! You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/utils.js"></script>
    <script src="js/app.js"></script>
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

        /* slideUp ΓÇö element fades in while rising up from below */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Use .anim-fade for a simple fade, .anim-slide for slide + fade */
        .anim-fade  { animation: fadeIn  0.6s ease both; }
        .anim-slide { animation: slideUp 0.55s ease both; }

        /* Delay class card starts animating slightly after the page loads */
        .delay-1 { animation-delay: 0.1s; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- ================================
     NAVIGATION BAR
     ================================ -->
    <?php 
        $current_page = 'register'; 
        include 'navbar.php'; 
    ?>


<!-- ================================
     REGISTER FORM SECTION
     White card with two columns:
       Left  = the sign-up form fields
       Right = illustration image
     anim-slide = card slides up when page loads
     ================================ -->
<section class="min-h-[calc(100vh-86px)] flex items-center px-5 py-10 bg-[radial-gradient(1000px_420px_at_50%_-10%,#eef2ff_0%,#f8fafc_55%,#f8fafc_100%)]">
    <div class="w-full">
        <div class="max-w-5xl mx-auto bg-white border border-slate-200 rounded-[24px] shadow-[0_20px_45px_rgba(15,23,42,0.08)] p-6 sm:p-8 lg:p-10 anim-slide delay-1">
            <div class="grid gap-8 lg:grid-cols-2 items-center">
                <div>
                    <a class="inline-flex items-center rounded-md bg-red-500 px-3 py-1 text-xs font-semibold text-white" href="index.php">Back</a>
                    <h1 class="text-2xl font-bold text-slate-900 mt-3 mb-4">Sign up</h1>

                    <?php if ($error): ?>
                    <div class="mb-3 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                    <div class="mb-3 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        ✅ Account created successfully! Go to <a href="login.php" class="font-semibold underline">Sign in</a>
                    </div>
                    <?php endif; ?>

                    <!-- Registration form -->
                    <form action="register.php" method="post" class="grid gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="id_number">ID Number</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="id_number" name="id_number" type="text" placeholder="Enter your ID" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="last_name">Last Name</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="last_name" name="last_name" type="text" placeholder="Enter your last name" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="first_name">First Name</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="first_name" name="first_name" type="text" placeholder="Enter your first name" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="middle_name">Middle Name</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="middle_name" name="middle_name" type="text" placeholder="Enter your middle name">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="course_level">Course Level</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="course_level" name="course_level" type="text" placeholder="e.g. 1">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="password">Password</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="password" name="password" type="password" placeholder="Create a password" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="confirm_password">Repeat your password</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="confirm_password" name="confirm_password" type="password" placeholder="Repeat your password" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="email">Email</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="email" name="email" type="email" placeholder="name@example.com" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="course">Course</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="course" name="course" type="text" placeholder="BSIT" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600" for="address">Address</label>
                            <input class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60" id="address" name="address" type="text" placeholder="Street, City, Province" required>
                        </div>

                        <button class="mt-2 inline-flex items-center rounded-lg bg-[#003366] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#004b93] transition" type="submit">Register</button>
                    </form>
                </div>

                <div class="flex items-center justify-center">
                    <div class="w-full max-w-[360px] bg-slate-50 border border-slate-200 rounded-[22px] overflow-hidden">
                        <img class="w-full h-auto block object-cover" src="register-illustration.jpg" alt="Register illustration">
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

<?php if ($success): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    showToast('<?= htmlspecialchars($success, ENT_QUOTES) ?>');
});
</script>
<?php endif; ?>

</body>
</html>
