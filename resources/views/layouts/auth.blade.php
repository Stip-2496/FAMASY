<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi App')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Favicons -->
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>
    
    <script>
        // Manejar la inicialización de componentes
        function inicializarComponentes() {
            if (window.Alpine) {
                window.Alpine.start();
            }
            
            // Disparar evento personalizado para gráficos
            if (typeof inicializarGraficosUsuarios === 'function') {
                setTimeout(inicializarGraficosUsuarios, 300);
            }
        }

        document.addEventListener('DOMContentLoaded', inicializarComponentes);
        document.addEventListener('livewire:navigated', inicializarComponentes);
        document.addEventListener('livewire:load', inicializarComponentes);
    </script>
</head>
<body class="min-h-screen flex flex-col">
  <div class="flex flex-1">
    <!-- Sidebar -->
    <div class="flex flex-col h-screen sticky top-0 w-[180px]">
      <div class="flex-1 overflow-y-auto">
        @include('partials.auth-nav')
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="flex flex-col flex-1 min-w-0">
      <main class="flex-1 bg-gray-100 overflow-auto">
        {{ $slot }}
      </main>
      @include('partials.auth-footer')
    </div>
  </div>
  @livewireScripts
</body>
</html>