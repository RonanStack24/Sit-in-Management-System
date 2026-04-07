<?php
session_start();

// Check if logout is confirmed
if (isset($_GET['confirmed']) && $_GET['confirmed'] === '1') {
    // Destroy session and redirect to login with logout message
    session_destroy();
    header('Location: login.php?logout=1');
    exit;
}

// If not confirmed, show logout confirmation page
$user_name = $_SESSION['first_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.6/tailwind.min.css">
    <script src="js/utils.js"></script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-[Inter]">

<div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
        <!-- Icon -->
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Content -->
        <h2 class="text-2xl font-bold text-slate-900 text-center mb-2">Confirm Logout</h2>
        <p class="text-slate-600 text-center mb-8">
            Are you sure you want to log out, <strong><?= htmlspecialchars($user_name) ?></strong>?
        </p>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button onclick="window.history.back();" class="flex-1 px-4 py-2.5 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition">
                Cancel
            </button>
            <a href="logout.php?confirmed=1" class="flex-1 px-4 py-2.5 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition text-center">
                Yes, Logout
            </a>
        </div>
    </div>
</div>

</body>
</html>
