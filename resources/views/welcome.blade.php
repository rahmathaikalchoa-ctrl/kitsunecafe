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
            <a href="/" class="flex items-center gap-2.5 font-bold text-lg text-gray-900">
                {{-- Kitsune spark mark --}}
                <svg class="w-6 h-6 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                </svg>
                <span>Kitsune Animal Cafe</span>
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
                    <flux:button>Meet the Foxes</flux:button>
                </a>
            </div>
        </div>

        {{-- Decorative fox silhouette watermark --}}
        <div class="absolute -bottom-12 left-1/2 -translate-x-1/2 select-none pointer-events-none opacity-[0.07]">
            <svg class="w-80 h-80 text-amber-700" viewBox="0 0 200 200" fill="currentColor">
                {{-- Fox ears --}}
                <polygon points="35,170 8,35 85,105"/>
                <polygon points="165,170 192,35 115,105"/>
                {{-- Inner ear highlight --}}
                <polygon points="35,162 14,50 76,104" fill="white" opacity="0.35"/>
                <polygon points="165,162 186,50 124,104" fill="white" opacity="0.35"/>
                {{-- Head --}}
                <circle cx="100" cy="148" r="65"/>
            </svg>
        </div>
    </section>

    {{-- Highlights --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Menu card --}}
            <a href="{{ route('menu.index') }}" wire:navigate
               class="group bg-white rounded-3xl shadow-sm border border-amber-100 overflow-hidden hover:shadow-md transition hover:-translate-y-0.5">
                <div class="h-48 bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                    {{-- Bowl with steam --}}
                    <svg class="w-20 h-20 text-amber-500" fill="none" viewBox="0 0 80 80" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 26 Q26 18 23 11"/>
                        <path d="M40 23 Q43 15 40 8"/>
                        <path d="M57 26 Q60 18 57 11"/>
                        <path d="M10 40 Q10 65 40 65 Q70 65 70 40 Z"/>
                        <line x1="10" y1="40" x2="70" y2="40"/>
                    </svg>
                </div>
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Our Menu</h2>
                    <p class="text-gray-500">
                        From hearty ramen and soft tamago sandwiches to matcha lattes and fox-shaped waffles —
                        everything made with care.
                    </p>
                    <p class="mt-4 text-amber-600 font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                        Browse the menu
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </p>
                </div>
            </a>

            {{-- Animals card --}}
            <a href="{{ route('animals.index') }}" wire:navigate
               class="group bg-white rounded-3xl shadow-sm border border-amber-100 overflow-hidden hover:shadow-md transition hover:-translate-y-0.5">
                <div class="h-48 bg-gradient-to-br from-orange-100 to-red-50 flex items-center justify-center">
                    {{-- Fox face silhouette --}}
                    <svg class="w-20 h-20 text-orange-400" viewBox="0 0 100 100" fill="currentColor">
                        <polygon points="28,72 6,14 52,52"/>
                        <polygon points="72,72 94,14 48,52"/>
                        <polygon points="28,68 12,22 49,51" fill="white" opacity="0.4"/>
                        <polygon points="72,68 88,22 51,51" fill="white" opacity="0.4"/>
                        <circle cx="50" cy="74" r="27"/>
                        <circle cx="38" cy="70" r="4" fill="white"/>
                        <circle cx="62" cy="70" r="4" fill="white"/>
                        <circle cx="39" cy="70" r="2" fill="#1a1a1a"/>
                        <circle cx="63" cy="70" r="2" fill="#1a1a1a"/>
                        <ellipse cx="50" cy="79" rx="4" ry="2.5" fill="#c2602a"/>
                    </svg>
                </div>
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Meet the Foxes</h2>
                    <p class="text-gray-500">
                        Our six resident foxes each have their own personality, story, and favourite spot in the cafe.
                        Get to know them before you visit.
                    </p>
                    <p class="mt-4 text-amber-600 font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                        See all foxes
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </p>
                </div>
            </a>

        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-amber-100 bg-white py-8 text-center text-sm text-gray-400">
        &copy; {{ date('Y') }} Kitsune Animal Cafe. All rights reserved.
    </footer>

</body>
</html>
