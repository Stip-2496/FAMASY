<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FAMASY')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> 
</head>
<body>
<div class="grid min-h-dvh grid-rows-[auto_1fr_auto]">
    {{-- Usar nav condicional según autenticación --}}
    @auth
        @include('partials.auth-nav')
    @else
        @include('partials.nav')
    @endauth

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
</div>
</body>
</html>