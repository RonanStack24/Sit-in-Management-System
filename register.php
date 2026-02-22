<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CCS Sit-in Monitoring</title>
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
     sticky = stays at the top when user scrolls
     z-50   = always shown above everything else
     Register link has a white underline (active page)
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
            <a class="text-sm font-semibold text-white border-b border-white/60 pb-0.5" href="register.php">Register</a>
        </div>
    </div>
</nav>

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
                    <!-- Back button — returns to the home page -->
                    <a class="inline-flex items-center rounded-md bg-red-500 px-3 py-1 text-xs font-semibold text-white" href="index.php">Back</a>
                    <h1 class="text-2xl font-bold text-slate-900 mt-3 mb-4">Sign up</h1>

                    <!-- Registration form — sends data to the server on submit -->
                    <form action="#" method="post" class="grid gap-3">
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

</body>
</html>
