{{-- resources/views/partials/header.blade.php --}}
<header
    x-data="{ open: false, scrolled: false, dark: document.documentElement.classList.contains('dark') }"
    x-init="
        window.addEventListener('scroll', () => { scrolled = window.scrollY > 10; });
        // Sync with initial state from localStorage
        dark = document.documentElement.classList.contains('dark');
    "
    @dark-change.window="
        dark = $event.detail;
        if (dark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        localStorage.setItem('dark', dark);
    "
    class="sticky top-0 z-50 w-full transition-all duration-300"
    style="min-height: 96px;"
>
    <!-- Desktop Navbar -->
    <nav
        class="items-center justify-between hidden p-2 mx-auto transition-all duration-300 border rounded-full shadow-lg lg:flex bg-white/90 dark:bg-slate-800/90 backdrop-filter backdrop-blur-lg border-slate-200 dark:border-slate-700"
        :style="scrolled ? 'max-width: 55rem; margin-top: 0.5rem; margin-bottom: 0.5rem;' : 'max-width: 65rem; margin-top: 1.25rem; margin-bottom: 0.75rem;'"
    >
        <!-- Logo Only -->
        <a href="/" class="flex items-center" style="margin-left: 1.5rem;">
            <img src="/images/logo.png" alt="Fairpoint" class="w-12 h-12 rounded-lg">
        </a>

        <!-- Nav Items -->
        <div class="flex items-center justify-center flex-1 gap-x-2">
            <a href="#features" class="px-4 py-2 transition-colors rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Features</a>
            <a href="/pricing" class="px-4 py-2 transition-colors rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Pricing</a>
            <a href="/about" class="px-4 py-2 transition-colors rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">About</a>
            <a href="/contact" class="px-4 py-2 transition-colors rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Contact</a>
        </div>

        <!-- Dark Mode + Auth Buttons (Hardcoded) -->
        <div class="flex items-center gap-3" style="margin-right: 1.5rem;">
            <!-- Dark Mode Toggle -->
            <button
                @click="
                    dark = !dark;
                    if (dark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    localStorage.setItem('dark', dark);
                    $dispatch('dark-change', dark);
                "
                class="relative inline-flex items-center h-6 transition-colors rounded-full w-11"
                :class="dark ? 'bg-primary' : 'bg-gray-300'"
            >
                <span
                    class="inline-block w-4 h-4 transition-transform transform bg-white rounded-full"
                    :class="dark ? 'translate-x-6' : 'translate-x-1'"
                ></span>
            </button>

            <!-- SIGN IN + SIGN UP (ALWAYS VISIBLE) -->
            <a href="/login" class="px-5 py-2 text-sm font-medium transition-colors rounded-full text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                Sign In
            </a>
            <a href="/register" class="px-5 py-2 bg-primary text-white rounded-full text-sm font-bold hover:bg-[#2BA3E6] transition-all">
                Sign Up Free
            </a>
        </div>
    </nav>

    <!-- Mobile Navbar -->
    <div class="mx-auto lg:hidden" style="width: 95%; margin-top: 0.75rem;">
        <div
            class="flex items-center justify-between p-2 transition-all duration-300 shadow-xl rounded-xl bg-slate-50 dark:bg-slate-800"
            :style="scrolled ? 'transform: translateY(0.5rem);' : 'transform: translateY(0.75rem);'"
        >
            <!-- Logo -->
            <a href="/" class="flex items-center">
                <img src="/images/logo.png" alt="Fairpoint" class="w-10 h-10 rounded-lg">
            </a>

            <!-- Menu Button -->
            <button @click="open = !open" class="p-2">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-700 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-700 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            @click.outside="open = false"
            class="w-full p-4 mt-2 bg-white border rounded-lg shadow-xl dark:bg-slate-800 border-slate-200 dark:border-slate-700"
        >
            <a href="#features" @click="open = false" class="block py-2 text-lg font-medium text-slate-700 dark:text-slate-300">Features</a>
            <a href="/pricing" @click="open = false" class="block py-2 text-lg font-medium text-slate-700 dark:text-slate-300">Pricing</a>
            <a href="/about" @click="open = false" class="block py-2 text-lg font-medium text-slate-700 dark:text-slate-300">About</a>
            <a href="#contact" @click="open = false" class="block py-2 text-lg font-medium text-slate-700 dark:text-slate-300">Contact</a>

            <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-700">
                <!-- Dark Mode Toggle (Mobile) -->
                <div class="flex items-center justify-between py-3 mb-3">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Dark Mode</span>
                    <button
                        @click="
                            dark = !dark;
                            if (dark) {
                                document.documentElement.classList.add('dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                            }
                            localStorage.setItem('dark', dark);
                            $dispatch('dark-change', dark);
                        "
                        class="relative inline-flex items-center h-6 transition-colors rounded-full w-11"
                        :class="dark ? 'bg-primary' : 'bg-gray-300'"
                    >
                        <span
                            class="inline-block w-4 h-4 transition-transform transform bg-white rounded-full"
                            :class="dark ? 'translate-x-6' : 'translate-x-1'"
                        ></span>
                    </button>
                </div>
                <a href="/login" class="block w-full py-2 font-medium text-center text-slate-700 dark:text-slate-300">Sign In</a>
                <a href="/register" class="block w-full py-2 mt-2 font-bold text-center text-white rounded-full bg-primary">Sign Up Free</a>
            </div>
        </div>
    </div>
</header>

<style>
    [x-cloak] { display: none !important; }
</style>
