{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Fairpoint')</title>

        <script>
            // Apply dark mode immediately before page renders to prevent flash
            (function() {
                const isDark = localStorage.getItem('dark') === 'true';
                if (isDark) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        @filamentStyles
        @livewireStyles
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-slate-900">

        @include('partials.header')

        <main class="min-h-screen">
            @yield('content')
        </main>

        <footer class="py-8 text-sm text-center text-gray-600 bg-white border-t dark:bg-slate-800">
            © {{ date('Y') }} Fairpoint. All rights reserved.
        </footer>

        @livewireScripts
        @filamentScripts
        @vite(['resources/js/app.js'])
    </body>
</html>
