<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-role" content="{{ optional(Auth::user())->is_admin ? 'admin' : 'user' }}">


    <meta property="og:image" content="{{ asset('default-og-image.jpg') }}">


    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Dynamic Open Graph Meta Tags (Facebook will see these) -->
    @php
    $ogTitle = $og_meta['title'] ?? 'MMRatePro - Live Exchange Rates & Gold Prices';
    $ogDescription = $og_meta['description'] ?? 'Real-time USD, SGD, EUR, THB exchange rates to MMK. Live gold prices in
    Myanmar kyat.';
    $ogImage = $og_meta['image'] ?? url('/default-og-image.jpg');
    $ogUrl = $og_meta['url'] ?? url()->current();
    @endphp

    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $ogUrl }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="MMRatePro">
    <meta property="og:locale" content="en_US">
    <meta property="fb:app_id" content="{{ config('app.facebook_app_id') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $ogUrl }}">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <title inertia>{{ $ogTitle }}</title>

    <!-- Viewport -->
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">

    <meta name="description"
        content="Real-time USD, SGD, EUR, THB exchange rates to MMK. Live gold prices in Myanmar kyat. Updated every 30 minutes from bank averages. Free currency tools and alerts.">
    <meta name="keywords" content="exchange rate, Myanmar, MMK, USD, SGD, EUR, THB, gold price, currency converter">
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


    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZSY1190X65"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-XXXXXXXXXX', {
                page_path: window.location.pathname,
                send_page_view: false // We'll send page views manually via Inertia
            });
    </script>

    <!-- Dark Mode (optimized) -->
    <script>
        document.documentElement.classList.toggle(
            'dark',
            localStorage.theme === 'dark' ||
            (!('theme' in localStorage) &&
                window.matchMedia('(prefers-color-scheme: dark)').matches)
        );
    </script>

    @if(app()->environment('production'))
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4513869151029765"
        crossorigin="anonymous">
    </script>
    @endif
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