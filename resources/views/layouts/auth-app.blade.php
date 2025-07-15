<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi App')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> 
</head>
<body>
<div class="grid min-h-dvh grid-rows-[auto_1fr_auto]">
    
    @include('partials.auth-nav')

    <main>
        @yield('content')
    </main>

    @include('partials.auth-footer')
</div>
</body>