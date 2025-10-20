<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi App')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> 
    
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>
</head>
<body>
    @include('partials.nav')

    <main class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 grid min-h-dvh grid-rows-[auto_1fr_auto]">
        {{ $slot }}
    </main>

    @include('partials.footer')
    
    @livewireScripts
</body>
</html>
