<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} — Admin</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-gray-100">
            {{-- Sidebar --}}
            <aside class="w-60 shrink-0 bg-white border-r border-gray-100">
                <div class="sticky top-0 h-screen">
                    <livewire:admin.sidebar />
                </div>
            </aside>

            {{-- Content --}}
            <div class="flex-1 flex flex-col min-w-0">
                @if (isset($header))
                    <header class="bg-white shadow-sm">
                        <div class="px-4 sm:px-6 lg:px-8 py-6">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
