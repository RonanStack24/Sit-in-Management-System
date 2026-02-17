<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- Top navigation -->
<nav class="bg-[#003366] text-white shadow-sm">
    <div class="px-[5%] py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <a class="flex items-center gap-2.5 font-bold text-[1.2rem] text-white" href="index.php">
                <span class="inline-flex items-center rounded-full bg-white/90 p-1">
                    <img class="w-9 h-9 object-contain" src="ccs-logo.png" alt="CCS logo">
                </span>
                <span>CCS Sit-in System</span>
            </a>
            <span class="inline-flex items-center rounded-full bg-white/90 px-2.5 py-1">
                <img class="h-[26px] w-auto block" src="uc-logo.png" alt="University of Cebu logo">
            </span>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-5">
            <a class="text-sm text-white/80 hover:text-white transition" href="index.php">Home</a>
            <div class="relative group">
                <button class="flex items-center gap-1 text-sm text-white/80 hover:text-white transition" type="button" aria-haspopup="true" aria-expanded="false">
                    Community
                    <svg class="w-3 h-3" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1 1l4 4 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="absolute left-0 top-[140%] min-w-[180px] rounded-[10px] bg-white py-2.5 text-slate-800 shadow-[0_10px_25px_rgba(0,0,0,0.15)] opacity-0 invisible -translate-y-1.5 transition duration-200 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-focus-within:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 z-10" role="menu">
                    <a class="block px-4 py-2.5 text-sm text-slate-800 hover:bg-slate-100" href="events.php" role="menuitem">Events</a>
                    <a class="block px-4 py-2.5 text-sm text-slate-800 hover:bg-slate-100" href="announcements.php" role="menuitem">Announcements</a>
                    <a class="block px-4 py-2.5 text-sm text-slate-800 hover:bg-slate-100" href="support.php" role="menuitem">Support</a>
                </div>
            </div>
            <a class="text-sm text-white/80 hover:text-white transition" href="about.php">About</a>
            <a class="text-sm text-white/80 hover:text-white transition" href="login.php">Login</a>
            <a class="text-sm text-white/80 hover:text-white transition" href="register.php">Register</a>
        </div>
    </div>
</nav>

<!-- Hero banner -->
<section class="min-h-[75vh] flex items-center px-5 pt-8 pb-16 text-center bg-[radial-gradient(1200px_400px_at_50%_0%,#eef2ff_0%,#f8fafc_55%,#f8fafc_100%)]">
    <div class="w-full">
        <div class="max-w-[720px] mx-auto bg-white border border-slate-200 rounded-[20px] px-6 py-7 sm:px-12 sm:py-10 shadow-[0_20px_40px_rgba(15,23,42,0.08)]">
            <h1 class="text-[2.1rem] sm:text-[2.6rem] font-bold text-slate-900 mb-3">Efficient Lab Management</h1>
            <p class="text-[1.05rem] text-slate-500 max-w-[560px] mx-auto mb-7 leading-6">Track your laboratory hours, view available slots, and manage your sit-in sessions with the official CCS Monitoring System.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="login.php" class="px-8 py-3 rounded-lg font-semibold bg-[#003366] text-white hover:bg-[#004b93] transition">Student Login</a>
                <a href="register.php" class="px-8 py-3 rounded-lg font-semibold border-2 border-[#003366] text-[#003366] hover:bg-slate-100 transition">Create Account</a>
            </div>
        </div>
    </div>
</section>

</body>
</html>
