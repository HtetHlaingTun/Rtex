<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-role" content="{{ optional(Auth::user())->is_admin ? 'admin' : 'user' }}">

    {{-- <script src="https://pl29084850.profitablecpmratenetwork.com/24/fb/c6/24fbc6c3855b45418eb9f70f4c00a60f.js">
    </script> --}}

    @guest
    {{-- <script src="https://pl29084850.profitablecpmratenetwork.com/24/fb/c6/24fbc6c3855b45418eb9f70f4c00a60f.js">
    </script> --}}


    {{-- <script async="async" data-cfasync="false"
        src="https://pl29084878.profitablecpmratenetwork.com/bf7ea0869d15921e230d365bc66d753b/invoke.js"></script>
    <div id="container-bf7ea0869d15921e230d365bc66d753b"></div> --}}

    <script>
        atOptions = {
        'key' : '2a6c1da951959ae9b100cd1aa0786555',
        'format' : 'iframe',
        'height' : 300,
        'width' : 160,
        'params' : {}
      };
    </script>
    <script src="https://www.highperformanceformat.com/2a6c1da951959ae9b100cd1aa0786555/invoke.js"></script>
    @endguest

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