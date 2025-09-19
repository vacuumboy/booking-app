<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <!-- Основной контейнер с адаптивным дизайном -->
        <div class="min-h-screen bg-adaptive-background">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="pb-adaptive-2xl">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-adaptive-surface border-t border-adaptive-border">
                <div class="container-adaptive py-adaptive-2xl">
                    <div class="mt-adaptive-lg border-t border-adaptive-border pt-adaptive-lg flex-adaptive md:items-center md:justify-between">
                        <div class="flex space-x-adaptive-lg md:order-2">
                            <a href="#" class="text-adaptive-text-secondary hover:text-adaptive-text transition-colors duration-adaptive">
                                <span class="sr-only">Instagram</span>
                            </a>
                        </div>
                        <div class="mt-adaptive-lg md:mt-0 md:order-1">
                            <p class="text-adaptive-sm text-adaptive-text-secondary text-center md:text-left">
                                © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Все права защищены.
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
