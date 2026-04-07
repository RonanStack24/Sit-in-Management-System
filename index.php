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
        .delay-1 { animation-delay: 0.10s; }
        .delay-2 { animation-delay: 0.25s; }

        /* ============================================
           SCROLL-REVEAL ANIMATIONS
           ============================================ */
        .reveal {
            opacity: 0;
            transform: translateY(36px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.10s; }
        .reveal-delay-2 { transition-delay: 0.20s; }
        .reveal-delay-3 { transition-delay: 0.30s; }
        .reveal-delay-4 { transition-delay: 0.40s; }
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
<section class="min-h-[75vh] flex items-center px-5 pt-8 pb-16 text-center bg-[radial-gradient(1200px_400px_at_50%_0%,#eef2ff_0%,#f8fafc_55%,#f8fafc_100%)](1200px_400px_at_50%_0%,#1e293b_0%,#0f172a_55%,#0f172a_100%)]">
    <div class="w-full">
        <div class="max-w-[720px] mx-auto bg-white border border-slate-200 rounded-[20px] px-6 py-7 sm:px-12 sm:py-10 shadow-[0_20px_40px_rgba(15,23,42,0.08)]_20px_40px_rgba(0,0,0,0.3)] anim-slide delay-1 hover:shadow-[0_28px_50px_rgba(15,23,42,0.12)]_28px_50px_rgba(0,0,0,0.4)] hover:-translate-y-1 transition-all duration-300">
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

<!-- ================================
     STATS BANNER
     ================================ -->
<section class="bg-[#003366] py-10 px-5">
    <div class="max-w-4xl mx-auto grid grid-cols-2 sm:grid-cols-4 gap-6 text-center text-white">
        <div class="reveal reveal-delay-1">
            <div class="text-3xl font-bold">30</div>
            <div class="text-xs text-white/60 mt-1 uppercase tracking-wide">Max Sessions</div>
        </div>
        <div class="reveal reveal-delay-2">
            <div class="text-3xl font-bold">5</div>
            <div class="text-xs text-white/60 mt-1 uppercase tracking-wide">Laboratories</div>
        </div>
        <div class="reveal reveal-delay-3">
            <div class="text-3xl font-bold">24/7</div>
            <div class="text-xs text-white/60 mt-1 uppercase tracking-wide">Monitoring</div>
        </div>
        <div class="reveal reveal-delay-4">
            <div class="text-3xl font-bold">100%</div>
            <div class="text-xs text-white/60 mt-1 uppercase tracking-wide">Free to Use</div>
        </div>
    </div>
</section>

<!-- ================================
     FEATURES SECTION
     ================================ -->
<section class="py-20 px-5 bg-slate-50">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12 reveal">
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-500">Features</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2">Everything you need in one place</h2>
            <p class="text-slate-500 mt-2 text-sm">Built specifically for CCS students and laboratory staff.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="reveal reveal-delay-1 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl mb-4">🕐</div>
                <h3 class="font-bold text-slate-800 mb-1">Session Tracking</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Keep track of how many sit-in sessions you have left out of your 30 allowed sessions.</p>
            </div>
            <div class="reveal reveal-delay-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl mb-4">🖥️</div>
                <h3 class="font-bold text-slate-800 mb-1">Lab Reservation</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Check available computers across all 5 CCS laboratories and reserve your slot in advance.</p>
            </div>
            <div class="reveal reveal-delay-3 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl mb-4">📋</div>
                <h3 class="font-bold text-slate-800 mb-1">Sit-in History</h3>
                <p class="text-sm text-slate-500 leading-relaxed">View a full log of your previous sit-in sessions including dates, labs used, and duration.</p>
            </div>
            <div class="reveal reveal-delay-1 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl mb-4">🔔</div>
                <h3 class="font-bold text-slate-800 mb-1">Instant Alerts</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Get notified when your sessions are running low or when your reservation is confirmed.</p>
            </div>
            <div class="reveal reveal-delay-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl mb-4">🔒</div>
                <h3 class="font-bold text-slate-800 mb-1">Secure Login</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Your account is protected with an encrypted password. Only you can access your data.</p>
            </div>
            <div class="reveal reveal-delay-3 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl mb-4">📱</div>
                <h3 class="font-bold text-slate-800 mb-1">Mobile Friendly</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Fully responsive design works on your phone, tablet, or desktop without any app install.</p>
            </div>
        </div>
    </div>
</section>

<!-- ================================
     HOW IT WORKS SECTION
     ================================ -->
<section class="py-20 px-5 bg-white">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12 reveal">
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-500">How It Works</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2">Get started in 3 simple steps</h2>
        </div>
        <div class="grid sm:grid-cols-3 gap-8 text-center">
            <div class="reveal reveal-delay-1">
                <div class="w-12 h-12 rounded-full bg-[#003366] text-white font-bold text-lg flex items-center justify-center mx-auto mb-4">1</div>
                <h3 class="font-bold text-slate-800 mb-1">Create an Account</h3>
                <p class="text-sm text-slate-500">Register using your student ID, name, course, and email. Takes less than a minute.</p>
            </div>
            <div class="reveal reveal-delay-2">
                <div class="w-12 h-12 rounded-full bg-[#003366] text-white font-bold text-lg flex items-center justify-center mx-auto mb-4">2</div>
                <h3 class="font-bold text-slate-800 mb-1">Log In</h3>
                <p class="text-sm text-slate-500">Sign in with your ID number and password to access your personal dashboard.</p>
            </div>
            <div class="reveal reveal-delay-3">
                <div class="w-12 h-12 rounded-full bg-[#003366] text-white font-bold text-lg flex items-center justify-center mx-auto mb-4">3</div>
                <h3 class="font-bold text-slate-800 mb-1">Manage Sessions</h3>
                <p class="text-sm text-slate-500">Track your remaining sessions, reserve a lab slot, and view your sit-in history.</p>
            </div>
        </div>
    </div>
</section>

<!-- ================================
     CTA SECTION
     ================================ -->
<section class="py-20 px-5 bg-[radial-gradient(900px_300px_at_50%_50%,#eef2ff,#f8fafc)]">
    <div class="max-w-xl mx-auto text-center reveal">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-3">Ready to get started?</h2>
        <p class="text-slate-500 text-sm mb-7">Create your free account now and take control of your lab sessions.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="register.php" class="px-8 py-3 rounded-lg font-semibold bg-[#003366] text-white hover:bg-[#004b93] transition">Register Now</a>
            <a href="login.php" class="px-8 py-3 rounded-lg font-semibold border-2 border-[#003366] text-[#003366] hover:bg-slate-100 transition">Sign In</a>
        </div>
    </div>
</section>

<script src="js/index.js"></script>
</body>
</html>
