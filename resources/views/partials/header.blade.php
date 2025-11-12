{{-- resources/views/partials/header.blade.php --}}
<header
    x-data="{ open: false, scrolled: false, dark: false }"
    x-init="
        window.addEventListener('scroll', () => { scrolled = window.scrollY > 10; });
        dark = localStorage.getItem('dark') === 'true';
        if (dark) document.documentElement.classList.add('dark');
    "
    @dark-change.window="dark = $event.detail; if (dark) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark'); localStorage.setItem('dark', dark);"
    class="sticky top-0 z-50 w-full transition-all duration-300"
    style="min-height: 96px;"
>
    <!-- Desktop Navbar -->
    <nav
        class="hidden lg:flex items-center justify-between mx-auto rounded-full border shadow-lg p-2 transition-all duration-300"
        :style="scrolled ? 'max-width: 55rem; margin-top: 0.5rem; margin-bottom: 0.5rem; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); border-color: #e2e8f0;' : 'max-width: 65rem; margin-top: 1.25rem; margin-bottom: 0.75rem; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); border-color: #e2e8f0;'"
    >
        <!-- Logo Only -->
        <a href="/" class="flex items-center" style="margin-left: 1.5rem;">
            <img src="/images/logo.png" alt="Fairpoint" class="w-12 h-12 rounded-lg">
        </a>

        <!-- Nav Items -->
        <div class="flex flex-1 items-center justify-center gap-x-2">
            <a href="#features" class="px-4 py-2 rounded-full text-slate-600 hover:bg-slate-100 transition-colors">Features</a>
            <a href="/pricing" class="px-4 py-2 rounded-full text-slate-600 hover:bg-slate-100 transition-colors">Pricing</a>
            <a href="/about" class="px-4 py-2 rounded-full text-slate-600 hover:bg-slate-100 transition-colors">About</a>
            <a href="#contact" class="px-4 py-2 rounded-full text-slate-600 hover:bg-slate-100 transition-colors">Contact</a>
        </div>

        <!-- Dark Mode + Auth Buttons (Hardcoded) -->
        <div class="flex items-center gap-3" style="margin-right: 1.5rem;">
            <!-- Dark Mode Toggle -->
            <button
                @click="dark = !dark; $dispatch('dark-change', dark)"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                :class="dark ? 'bg-primary' : 'bg-gray-300'"
            >
                <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="dark ? 'translate-x-6' : 'translate-x-1'"
                ></span>
            </button>

            <!-- SIGN IN + SIGN UP (ALWAYS VISIBLE) -->
            <a href="/login" class="px-5 py-2 text-slate-700 rounded-full text-sm font-medium hover:bg-slate-100 transition-colors">
                Sign In
            </a>
            <a href="/register" class="px-5 py-2 bg-primary text-white rounded-full text-sm font-bold hover:bg-[#2BA3E6] transition-all">
                Sign Up Free
            </a>
        </div>
    </nav>

    <!-- Mobile Navbar -->
    <div class="lg:hidden mx-auto" style="width: 95%; margin-top: 0.75rem;">
        <div
            class="flex items-center justify-between rounded-xl p-2 shadow-xl transition-all duration-300"
            style="background: #f8fafc;"
            :style="scrolled ? 'transform: translateY(0.5rem);' : 'transform: translateY(0.75rem);'"
        >
            <!-- Logo -->
            <a href="/" class="flex items-center">
                <img src="/images/logo.png" alt="Fairpoint" class="w-10 h-10 rounded-lg">
            </a>

            <!-- Menu Button -->
            <button @click="open = !open" class="p-2">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            class="w-full rounded-lg p-4 mt-2 shadow-xl bg-white border"
            style="border-color: #e2e8f0;"
        >
            <a href="#features" @click="open = false" class="block py-2 text-lg font-medium text-slate-700">Features</a>
            <a href="/pricing" @click="open = false" class="block py-2 text-lg font-medium text-slate-700">Pricing</a>
            <a href="/about" @click="open = false" class="block py-2 text-lg font-medium text-slate-700">About</a>
            <a href="#contact" @click="open = false" class="block py-2 text-lg font-medium text-slate-700">Contact</a>

            <div class="border-t pt-4 mt-4" style="border-color: #e2e8f0;">
                <a href="/login" class="block w-full text-center py-2 text-slate-700 font-medium">Sign In</a>
                <a href="/register" class="block w-full text-center py-2 mt-2 bg-primary text-white rounded-full font-bold">Sign Up Free</a>
            </div>
        </div>
    </div>
</header>

<style>
    [x-cloak] { display: none !important; }
</style>