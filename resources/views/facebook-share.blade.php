<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $metaTitle }} | MMRatePro</title>
    <meta name="description" content="{{ $metaDescription }}">

    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $metaUrl }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="MMRatePro">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">

    <!-- Redirect to the actual page after a short delay -->
    <meta http-equiv="refresh" content="0; url={{ $metaUrl }}">
</head>

<body>
    <p>Redirecting to <a href="{{ $metaUrl }}">MMRatePro</a>...</p>
</body>

</html>