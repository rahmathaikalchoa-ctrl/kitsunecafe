<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitsune Animal Cafe</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-amber-50 text-gray-900">

    {{-- Navigation --}}
    <header class="bg-white/80 backdrop-blur border-b border-amber-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-bold text-lg text-gray-900">
                🦊 <span>Kitsune Animal Cafe</span>
            </a>
            <livewire:welcome.navigation />
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-amber-100 to-amber-50 py-24 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <p class="text-amber-600 font-semibold tracking-widest uppercase text-sm mb-4">Welcome to</p>
            <h1 class="text-5xl sm:text-6xl font-bold text-gray-900 leading-tight mb-6">
                Kitsune<br>Animal Cafe
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-10">
                Sip your favourite drink, enjoy our handcrafted food, and spend quality time with our resident foxes.
                A little magic in every visit.
            </p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="{{ route('menu.index') }}" wire:navigate>
                    <flux:button variant="primary">View Our Menu</flux:button>
                </a>
                <a href="{{ route('animals.index') }}" wire:navigate>
                    <flux:button>Meet the Foxes 🦊</flux:button>
                </a>
            </div>
        </div>

        <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-9xl opacity-10 select-none pointer-events-none">🦊</div>
    </section>

    {{-- Highlights --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Menu card --}}
            <a href="{{ route('menu.index') }}" wire:navigate
               class="group bg-white rounded-3xl shadow-sm border border-amber-100 overflow-hidden hover:shadow-md transition hover:-translate-y-0.5">
                <div class="h-48 bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center text-7xl">
                    🍜
                </div>
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Our Menu</h2>
                    <p class="text-gray-500">
                        From hearty ramen and soft tamago sandwiches to matcha lattes and fox-shaped waffles —
                        everything made with care.
                    </p>
                    <p class="mt-4 text-amber-600 font-semibold group-hover:underline">Browse the menu →</p>
                </div>
            </a>

            {{-- Animals card --}}
            <a href="{{ route('animals.index') }}" wire:navigate
               class="group bg-white rounded-3xl shadow-sm border border-amber-100 overflow-hidden hover:shadow-md transition hover:-translate-y-0.5">
                <div class="h-48 bg-gradient-to-br from-orange-100 to-red-50 flex items-center justify-center text-7xl">
                    🦊
                </div>
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Meet the Foxes</h2>
                    <p class="text-gray-500">
                        Our six resident foxes each have their own personality, story, and favourite spot in the cafe.
                        Get to know them before you visit.
                    </p>
                    <p class="mt-4 text-amber-600 font-semibold group-hover:underline">See all foxes →</p>
                </div>
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-amber-100 bg-white py-8 text-center text-sm text-gray-400">
        © {{ date('Y') }} Kitsune Animal Cafe. All rights reserved.
    </footer>

</body>
</html>
