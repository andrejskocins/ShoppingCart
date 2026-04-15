@php
    $manifestPath = public_path('build/manifest.json');
    $manifest = file_exists($manifestPath)
        ? json_decode(file_get_contents($manifestPath), true)
        : null;

    $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
    $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
@endphp

@if ($cssFile && $jsFile)
    <link rel="stylesheet" href="{{ '/build/' . $cssFile }}">
    <script type="module" src="{{ '/build/' . $jsFile }}"></script>
@elseif (file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endif