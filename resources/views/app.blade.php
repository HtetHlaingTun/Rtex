<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-role" content="{{ optional(Auth::user())->is_admin ? 'admin' : 'user' }}">


    <meta property="og:image" content="{{ asset('default-og-image.jpg') }}">

    <!-- Dynamic Meta Tags for SEO/Social Media -->
    @if(isset($viewData['meta']))
    <title>{{ $viewData['meta']['title'] }} - MMRatePro</title>
    <meta name="description" content="{{ $viewData['meta']['description'] }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $viewData['meta']['url'] }}">
    <meta property="og:title" content="{{ $viewData['meta']['title'] }}">
    <meta property="og:description" content="{{ $viewData['meta']['description'] }}">
    <meta property="og:image" content="{{ $viewData['meta']['image'] }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="MMRatePro">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $viewData['meta']['url'] }}">
    <meta name="twitter:title" content="{{ $viewData['meta']['title'] }}">
    <meta name="twitter:description" content="{{ $viewData['meta']['description'] }}">
    <meta name="twitter:image" content="{{ $viewData['meta']['image'] }}">
    @else
    <title inertia>{{ config('app.name', 'MMRatePro') }}</title>
    @endif

    <!-- Viewport -->
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">

    <title inertia>{{ config('app.name', 'MMRatePro') }}</title>

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