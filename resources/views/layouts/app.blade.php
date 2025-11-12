{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Fairpoint')</title>

    @filamentStyles
    @livewireStyles
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-gray-50 dark:bg-slate-900">

    @include('partials.header')

    <main class="min-h-screen">
        @yield('content')
    </main>

    <footer class="bg-white dark:bg-slate-800 border-t py-8 text-center text-sm text-gray-600">
        © {{ date('Y') }} Fairpoint. All rights reserved.
    </footer>

    @livewireScripts
    @filamentScripts
    @vite(['resources/js/app.js'])
</body>
</html>