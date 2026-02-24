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
            
            <a class="text-sm <?= ($current_page == 'home') ? 'font-semibold text-white border-b border-white/60 pb-0.5' : 'text-white/80 hover:text-white transition' ?>" href="index.php">Home</a>
            
            <div class="relative group">
                <button class="flex items-center gap-1 text-sm text-white/80 hover:text-white transition" type="button" aria-haspopup="true" aria-expanded="false">
                    Community
                    <svg class="w-3 h-3" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1 1l4 4 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="absolute left-0 top-[140%] min-w-[180px] rounded-[10px] bg-white py-2.5 text-slate-800 shadow-[0_10px_25px_rgba(0,0,0,0.15)] opacity-0 invisible -translate-y-1.5 transition duration-200 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-focus-within:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 z-10" role="menu">
                    <a class="block px-4 py-2.5 text-sm hover:bg-slate-100" href="events.php">Events</a>
                    <a class="block px-4 py-2.5 text-sm hover:bg-slate-100" href="announcements.php">Announcements</a>
                    <a class="block px-4 py-2.5 text-sm hover:bg-slate-100" href="support.php">Support</a>
                </div>
            </div>

            <a class="text-sm <?= ($current_page == 'about') ? 'font-semibold text-white border-b border-white/60 pb-0.5' : 'text-white/80 hover:text-white transition' ?>" href="about.php">About</a>
            
            <a class="text-sm <?= ($current_page == 'login') ? 'font-semibold text-white border-b border-white/60 pb-0.5' : 'text-white/80 hover:text-white transition' ?>" href="login.php">Login</a>
            
            <a class="text-sm <?= ($current_page == 'register') ? 'font-semibold text-white border-b border-white/60 pb-0.5' : 'text-white/80 hover:text-white transition' ?>" href="register.php">Register</a>
        </div>
    </div>
</nav>