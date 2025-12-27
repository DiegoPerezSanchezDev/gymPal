<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @if(str_contains(request()->getHost(), 'trycloudflare.com'))
            <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
        @endif

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="preload" as="image" href="/images/logo-gympal.png">
        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
        <!-- Scripts -->
        @routes
        @if(str_contains(request()->getHost(), 'trycloudflare.com'))
            {{-- Cargamos los assets compilados para el tÃºnel (MÃ³vil) --}}
            <link rel="stylesheet" href="/build/assets/app-Fn3vPxaq.css">
            <script type="module" src="/build/assets/app-ljA-nvWZ.js"></script>
        @else
            {{-- Modo desarrollo normal para tu PC --}}
            @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @endif
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
