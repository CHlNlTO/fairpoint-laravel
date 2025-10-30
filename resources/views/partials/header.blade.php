<header
    x-data="{ open: false, scrolled: false }"
    x-init="
        window.addEventListener('scroll', () => {
            scrolled = window.scrollY > 10;
        });
    "
    class="sticky top-0 z-50 w-full transition-all duration-300"
    style="min-height: 96px;"
>
    <!-- Desktop Navbar -->
    <nav
        class="hidden lg:flex items-center justify-between mx-auto rounded-full border shadow-lg p-2 transition-all duration-300"
        :style="scrolled ? 'max-width: 55rem; margin-top: 0.5rem; margin-bottom: 0.5rem; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); border-color: #e2e8f0;' : 'max-width: 65rem; margin-top: 1.25rem; margin-bottom: 0.75rem; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); border-color: #e2e8f0;'"
    >
        <!-- Logo -->
        <a href="/" class="flex items-center space-x-2" style="margin-left: 1.5rem;">
            <div style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; font-weight: bold; color: white; font-size: 1.25rem;">
                F
            </div>
            <span style="font-size: 1.25rem; font-weight: bold; color: #1e293b;">Fairpoint</span>
        </a>

        <!-- Nav Items -->
        <div class="flex flex-1 items-center justify-center gap-x-2">
            <a href="#features"
               class="relative px-4 py-2 rounded-full transition-all duration-300"
               style="color: #475569;"
               onmouseover="this.style.background='#f1f5f9'; this.style.color='#1e293b';"
               onmouseout="this.style.background='transparent'; this.style.color='#475569';">
                Features
            </a>
            <a href="/pricing"
               class="relative px-4 py-2 rounded-full transition-all duration-300"
               style="color: #475569;"
               onmouseover="this.style.background='#f1f5f9'; this.style.color='#1e293b';"
               onmouseout="this.style.background='transparent'; this.style.color='#475569';">
                Pricing
            </a>
            <a href="/about"
               class="relative px-4 py-2 rounded-full transition-all duration-300"
               style="color: #475569;"
               onmouseover="this.style.background='#f1f5f9'; this.style.color='#1e293b';"
               onmouseout="this.style.background='transparent'; this.style.color='#475569';">
                About
            </a>
            <a href="#contact"
               class="relative px-4 py-2 rounded-full transition-all duration-300"
               style="color: #475569;"
               onmouseover="this.style.background='#f1f5f9'; this.style.color='#1e293b';"
               onmouseout="this.style.background='transparent'; this.style.color='#475569';">
                Contact
            </a>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-x-2" style="margin-right: 1.5rem;">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="px-4 py-2 rounded-full text-sm font-bold transition-all duration-200"
                       style="background: #38B6FF; color: white; box-shadow: 0 4px 6px rgba(56, 182, 255, 0.3);"
                       onmouseover="this.style.background='#2BA3E6'; this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.background='#38B6FF'; this.style.transform='translateY(0)';">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 rounded-full text-sm font-bold transition-all duration-200"
                       style="background: transparent; color: #475569;"
                       onmouseover="this.style.color='#1e293b';"
                       onmouseout="this.style.color='#475569';">
                        Sign In
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 rounded-full text-sm font-bold transition-all duration-200"
                           style="background: #38B6FF; color: white; box-shadow: 0 4px 6px rgba(56, 182, 255, 0.3);"
                           onmouseover="this.style.background='#2BA3E6'; this.style.transform='translateY(-2px)';"
                           onmouseout="this.style.background='#38B6FF'; this.style.transform='translateY(0)';">
                            Sign Up Free
                        </a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <!-- Mobile Navbar -->
    <div class="lg:hidden mx-auto" style="width: 95%; margin-top: 0.75rem;">
        <div
            class="flex items-center justify-between rounded-xl p-2 shadow-xl transition-all duration-300"
            style="background: #f1f5f9;"
            :style="scrolled ? 'transform: translateY(0.5rem);' : 'transform: translateY(0.75rem);'"
        >
            <!-- Logo -->
            <a href="/" class="flex items-center space-x-2" style="margin-left: 0.5rem;">
                <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; font-weight: bold; color: white; font-size: 1rem;">
                    F
                </div>
            </a>

            <!-- Menu Button -->
            <button @click="open = !open" class="p-2">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px; color: #1e293b;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px; color: #1e293b;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2"
            @click.outside="open = false"
            class="w-full rounded-lg p-4 shadow-xl flex flex-col gap-4 border"
            style="margin-top: 0.5rem; background: white; border-color: #e2e8f0;"
        >
            <a href="#features"
               @click="open = false"
               class="block w-full rounded-md p-2 text-lg font-semibold transition-colors"
               style="color: #1e293b;"
               onmouseover="this.style.background='#f1f5f9';"
               onmouseout="this.style.background='transparent';">
                Features
            </a>
            <a href="/pricing"
               @click="open = false"
               class="block w-full rounded-md p-2 text-lg font-semibold transition-colors"
               style="color: #1e293b;"
               onmouseover="this.style.background='#f1f5f9';"
               onmouseout="this.style.background='transparent';">
                Pricing
            </a>
            <a href="/about"
               @click="open = false"
               class="block w-full rounded-md p-2 text-lg font-semibold transition-colors"
               style="color: #1e293b;"
               onmouseover="this.style.background='#f1f5f9';"
               onmouseout="this.style.background='transparent';">
                About
            </a>
            <a href="#contact"
               @click="open = false"
               class="block w-full rounded-md p-2 text-lg font-semibold transition-colors"
               style="color: #1e293b;"
               onmouseover="this.style.background='#f1f5f9';"
               onmouseout="this.style.background='transparent';">
                Contact
            </a>

            <div class="flex w-full flex-col gap-4 border-t pt-4" style="margin-top: 0.5rem; border-color: #e2e8f0;">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="w-full text-center px-4 py-2 rounded-full text-sm font-bold transition-all"
                           style="background: #38B6FF; color: white; box-shadow: 0 4px 6px rgba(56, 182, 255, 0.3);">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="w-full text-center px-4 py-2 rounded-full text-sm font-bold transition-colors"
                           style="background: transparent; color: #475569;">
                            Sign In
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="w-full text-center px-4 py-2 rounded-full text-sm font-bold transition-all"
                               style="background: #38B6FF; color: white; box-shadow: 0 4px 6px rgba(56, 182, 255, 0.3);">
                                Sign Up Free
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </div>
</header>

<style>
    [x-cloak] { display: none !important; }
</style>