<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body>
        <div class="grid min-h-dvh grid-rows-[auto_1fr_auto]">
            @include('partials.nav')
                <main class="flex justify-center px-4">
                    {{ $slot }}
                </main>

            @include('partials.footer')
        </div>
    </body>
</html>
