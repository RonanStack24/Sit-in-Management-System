<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* fade-in: just fades from invisible to visible */
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* slide-up: fades in AND moves up from below */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Apply animations as utility classes */
        .anim-fade   { animation: fadeIn  0.6s ease both; }
        .anim-slide  { animation: slideUp 0.5s ease both; }

        /* Stagger delays — each card starts a bit later */
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.15s; }
        .delay-3 { animation-delay: 0.25s; }
        .delay-4 { animation-delay: 0.35s; }
        .delay-5 { animation-delay: 0.45s; }
        .delay-6 { animation-delay: 0.55s; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- ================================
     NAVIGATION BAR
     sticky = stays at the top when user scrolls
     z-50   = always shown above everything else
     About link has a white underline (active page)
     ================================ -->
<nav class="bg-[#003366] text-white shadow-sm sticky top-0 z-50">
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
                <button class="flex items-center gap-1 text-sm text-white/80 hover:text-white transition" type="button">
                    Community
                    <svg class="w-3 h-3" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1l4 4 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="absolute left-0 top-[140%] min-w-[180px] rounded-[10px] bg-white py-2.5 text-slate-800 shadow-[0_10px_25px_rgba(0,0,0,0.15)] opacity-0 invisible -translate-y-1.5 transition duration-200 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 z-10">
                    <a class="block px-4 py-2.5 text-sm hover:bg-slate-100" href="events.php">Events</a>
                    <a class="block px-4 py-2.5 text-sm hover:bg-slate-100" href="announcements.php">Announcements</a>
                    <a class="block px-4 py-2.5 text-sm hover:bg-slate-100" href="support.php">Support</a>
                </div>
            </div>
            <a class="text-sm font-semibold text-white border-b border-white/60 pb-0.5" href="about.php">About</a>
            <a class="text-sm text-white/80 hover:text-white transition" href="login.php">Login</a>
            <a class="text-sm text-white/80 hover:text-white transition" href="register.php">Register</a>
        </div>
    </div>
</nav>

<!-- Page content -->
<main class="px-[5%] py-12 max-w-4xl mx-auto">

    <!-- Page title -->
    <div class="mb-10 text-center anim-fade">
        <h1 class="text-3xl font-bold text-slate-900">About the System</h1>
        <p class="mt-2 text-slate-500 text-sm max-w-xl mx-auto">
            Learn what the CCS Sit-in Monitoring System is, why it exists, and how it helps students and staff.
        </p>
    </div>

    <!-- What is it -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm mb-6 anim-slide delay-1 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
        <div class="flex items-center gap-3 mb-3">
            <span class="text-2xl">📋</span>
            <h2 class="text-lg font-bold text-slate-900">What is this system?</h2>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed">
            The <strong>CCS Sit-in Monitoring System</strong> is a web-based tool for the College of Computer Studies at the
            University of Cebu. It lets students log and track their laboratory sit-in sessions, while giving administrators
            a clear view of lab activity and occupancy in real time.
        </p>
    </div>

    <!-- Why it exists -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm mb-6 anim-slide delay-2 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
        <div class="flex items-center gap-3 mb-3">
            <span class="text-2xl">💡</span>
            <h2 class="text-lg font-bold text-slate-900">Why was it built?</h2>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed">
            Before this system, sit-in monitoring was done manually paper logs were error prone and hard to review.
            This system replaces that process with a fast, organized, and paperless workflow that benefits both students
            and lab administrators.
        </p>
    </div>

    <!-- Features -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm mb-6 anim-slide delay-3 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">⚙️</span>
            <h2 class="text-lg font-bold text-slate-900">Key Features</h2>
        </div>
        <ul class="grid sm:grid-cols-2 gap-3">
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="mt-0.5 text-green-500 font-bold">✓</span>
                Student registration and login
            </li>
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="mt-0.5 text-green-500 font-bold">✓</span>
                Sit-in session logging and history
            </li>
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="mt-0.5 text-green-500 font-bold">✓</span>
                Remaining session tracking (max 30)
            </li>
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="mt-0.5 text-green-500 font-bold">✓</span>
                Lab availability and seat monitoring
            </li>
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="mt-0.5 text-green-500 font-bold">✓</span>
                Admin dashboard with live occupancy
            </li>
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="mt-0.5 text-green-500 font-bold">✓</span>
                Announcements and community notices
            </li>
        </ul>
    </div>

    <!-- Who uses it -->
    <div class="grid sm:grid-cols-2 gap-6 mb-6">
        <div class="bg-[#003366] text-white rounded-2xl p-6 shadow-sm anim-slide delay-4 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
            <div class="text-2xl mb-2">🎓</div>
            <h3 class="font-bold text-base mb-1">For Students</h3>
            <p class="text-sm text-white/75 leading-relaxed">
                Register, log in, reserve a lab seat, view your remaining sit-in sessions, and check your history all in one place.
            </p>
        </div>
        <div class="bg-slate-800 text-white rounded-2xl p-6 shadow-sm anim-slide delay-5 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
            <div class="text-2xl mb-2">🛠️</div>
            <h3 class="font-bold text-base mb-1">For Administrators</h3>
            <p class="text-sm text-white/75 leading-relaxed">
                Monitor live lab occupancy, approve or deny requests, manage student records, and generate attendance reports.
            </p>
        </div>
    </div>

    <!-- School info -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm text-center anim-slide delay-6 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
        <p class="text-xs text-slate-400 uppercase tracking-widest mb-1">Developed for</p>
        <h3 class="text-base font-bold text-slate-900">College of Computer Studies</h3>
        <p class="text-sm text-slate-500">University of Cebu</p>
        <div class="mt-4 flex justify-center gap-4">
            <a href="login.php" class="px-5 py-2 rounded-lg bg-[#003366] text-white text-sm font-semibold hover:bg-[#004b93] transition">
                Student Login
            </a>
            <a href="register.php" class="px-5 py-2 rounded-lg border-2 border-[#003366] text-[#003366] text-sm font-semibold hover:bg-slate-100 transition">
                Register
            </a>
        </div>
    </div>

</main>

</body>
</html>
