<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center py-10 px-4 bg-gradient-to-b from-amber-50 via-white to-white overflow-hidden">

            {{-- Decorative soft glows for depth (purely cosmetic) --}}
            <div aria-hidden="true" class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-orange-200/40 blur-3xl"></div>

            <a href="/" wire:navigate class="relative flex flex-col items-center group">
                <span class="flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-sm border border-amber-100/70 text-amber-500 transition-all duration-200 group-hover:-translate-y-0.5 group-hover:shadow-md group-hover:text-amber-600">
                    <x-application-logo class="w-10 h-10 fill-current" />
                </span>
                <span class="mt-3 text-lg font-bold text-gray-800 group-hover:text-amber-700 transition-colors">Kitsune Animal Cafe</span>
                <span class="mt-1 text-sm text-gray-500">Order treats &middot; Meet the foxes</span>
            </a>

            <div
                x-data
                x-init="$el.animate([{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'translateY(0)' }], { duration: 350, easing: 'cubic-bezier(0.16, 1, 0.3, 1)' })"
                class="relative w-full sm:max-w-md mt-6 bg-white shadow-sm border border-amber-100/70 overflow-hidden rounded-2xl"
            >
                {{-- Top accent bar --}}
                <div class="h-1 bg-gradient-to-r from-amber-500 to-orange-400"></div>
                <div class="px-6 py-6">
                    {{ $slot }}
                </div>
            </div>

            <a href="/" wire:navigate class="relative mt-6 text-sm font-medium text-gray-500 hover:text-amber-600 transition-colors">
                &larr; Back to cafe
            </a>
        </div>
    </body>
</html>
