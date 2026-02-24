<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring</title>
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

        /* Delay classes — stagger animations so elements appear one at a time */
        .delay-1 { animation-delay: 0.10s; }  /* waits 0.10s before starting */
        .delay-2 { animation-delay: 0.25s; }  /* waits 0.25s before starting */
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- ================================
     NAVIGATION BAR
     ================================ -->

    <?php 
        // 1. Give this page a nametag
        $current_page = 'home'; 
        
        // 2. Stamp the navigation bar right here
        include 'navbar.php'; 
    ?>


<!-- ================================
     HERO SECTION
     The big welcome card in the center of the page.
     anim-slide = card slides up when page loads
     hover:-translate-y-1 = card lifts slightly on hover
     ================================ -->
<section class="min-h-[75vh] flex items-center px-5 pt-8 pb-16 text-center bg-[radial-gradient(1200px_400px_at_50%_0%,#eef2ff_0%,#f8fafc_55%,#f8fafc_100%)]">
    <div class="w-full">
        <div class="max-w-[720px] mx-auto bg-white border border-slate-200 rounded-[20px] px-6 py-7 sm:px-12 sm:py-10 shadow-[0_20px_40px_rgba(15,23,42,0.08)] anim-slide delay-1 hover:shadow-[0_28px_50px_rgba(15,23,42,0.12)] hover:-translate-y-1 transition-all duration-300">
            <!-- Page heading — fades in first -->
            <h1 class="text-[2.1rem] sm:text-[2.6rem] font-bold text-slate-900 mb-3 anim-fade">Efficient Lab Management</h1>
            <!-- Short description — fades in slightly after the heading -->
            <p class="text-[1.05rem] text-slate-500 max-w-[560px] mx-auto mb-7 leading-6 anim-fade delay-1">Track your laboratory hours, view available slots, and manage your sit-in sessions with the unofficial CCS Monitoring System.</p>
            <!-- Call-to-action buttons — fades in last -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center anim-fade delay-2">
                <a href="login.php" class="px-8 py-3 rounded-lg font-semibold bg-[#003366] text-white hover:bg-[#004b93] transition">Student Login</a>
                <a href="register.php" class="px-8 py-3 rounded-lg font-semibold border-2 border-[#003366] text-[#003366] hover:bg-slate-100 transition">Create Account</a>
            </div>
        </div>
    </div>
</section>

</body>
</html>
