<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-role" content="{{ optional(Auth::user())->is_admin ? 'admin' : 'user' }}">

    <!-- Viewport -->
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Prevent transition flicker -->
    <style>
        .preload * {
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -ms-transition: none !important;
            -o-transition: none !important;
        }
    </style>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead

    <!-- Dark Mode (optimized) -->
    <script>
        document.documentElement.classList.toggle(
            'dark',
            localStorage.theme === 'dark' ||
            (!('theme' in localStorage) &&
                window.matchMedia('(prefers-color-scheme: dark)').matches)
        );
    </script>
</head>

<body class="font-sans antialiased preload">

    @inertia

    <!-- Remove preload after load -->
    <script>
        window.addEventListener('load', () => {
            document.body.classList.remove('preload');
        });
    </script>

</body>

</html>