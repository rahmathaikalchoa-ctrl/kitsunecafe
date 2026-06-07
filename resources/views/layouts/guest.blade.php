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
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 px-4 bg-gradient-to-b from-amber-50 via-white to-white">
            <a href="/" wire:navigate class="flex flex-col items-center group">
                <span class="flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-sm border border-amber-100/70 text-amber-500 transition-transform duration-200 group-hover:-translate-y-0.5">
                    <x-application-logo class="w-10 h-10 fill-current" />
                </span>
                <span class="mt-3 text-lg font-bold text-gray-800 group-hover:text-amber-700 transition-colors">Kitsune Animal Cafe</span>
            </a>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white shadow-sm border border-amber-100/70 overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>

            <a href="/" wire:navigate class="mt-6 text-sm font-medium text-gray-500 hover:text-amber-600 transition-colors">
                &larr; Back to cafe
            </a>
        </div>
    </body>
</html>
