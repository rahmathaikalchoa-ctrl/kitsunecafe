<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitsune Animal Cafe</title>
    <meta name="description" content="Visit Kitsune Animal Cafe — handcrafted food, specialty drinks, and six resident foxes. Order online and get to know each fox before you visit.">
    <meta property="og:title" content="Kitsune Animal Cafe">
    <meta property="og:description" content="Handcrafted food, specialty drinks, and six resident foxes. A little magic in every visit.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-amber-50 text-gray-900">

    {{-- Navigation --}}
    <header class="bg-white/80 backdrop-blur border-b border-amber-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5 font-bold text-lg text-gray-900 group">
                <x-application-logo class="h-8 w-8 text-amber-500 shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6" />
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
                <a href="{{ route('menu.index') }}" wire:navigate
                   class="text-white font-semibold rounded-lg px-6 py-2.5
                          bg-gradient-to-r from-amber-500 to-orange-400
                          hover:from-amber-600 hover:to-orange-500
                          hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                          active:translate-y-0 active:scale-[0.97]
                          transition-all duration-200
                          focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2">
                    View Our Menu
                </a>
                <a href="{{ route('animals.index') }}" wire:navigate
                   class="font-semibold rounded-lg px-6 py-2.5
                          border border-amber-300 text-amber-700 bg-white
                          hover:bg-amber-50 hover:border-amber-400 hover:-translate-y-0.5
                          active:translate-y-0 active:scale-[0.97]
                          transition-all duration-200
                          focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2">
                    Meet the Foxes
                </a>
            </div>
        </div>

        {{-- Decorative fox silhouette watermark --}}
        <div class="absolute -bottom-12 left-1/2 -translate-x-1/2 select-none pointer-events-none opacity-[0.07]" aria-hidden="true">
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
               class="group bg-white rounded-3xl shadow-sm border border-amber-100 overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-amber-100/70 hover:border-amber-200">
                <div class="h-48 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?auto=format&fit=crop&w=800&q=80"
                        alt="A bowl of ramen from our menu"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        loading="lazy"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                    />
                    {{-- Fallback: bowl with steam --}}
                    <div class="h-48 bg-gradient-to-br from-amber-100 to-orange-100 items-center justify-center" style="display:none">
                        <svg class="w-20 h-20 text-amber-500" fill="none" viewBox="0 0 80 80" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M23 26 Q26 18 23 11"/>
                            <path d="M40 23 Q43 15 40 8"/>
                            <path d="M57 26 Q60 18 57 11"/>
                            <path d="M10 40 Q10 65 40 65 Q70 65 70 40 Z"/>
                            <line x1="10" y1="40" x2="70" y2="40"/>
                        </svg>
                    </div>
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
               class="group bg-white rounded-3xl shadow-sm border border-amber-100 overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-amber-100/70 hover:border-amber-200">
                <div class="h-48 overflow-hidden">
                    <img
                        src="{{ asset('images/animals/kiku.jpg') }}"
                        alt="One of our resident foxes"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        loading="lazy"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                    />
                    <x-fox-placeholder class="h-48" style="display:none" />
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
    <footer class="border-t border-amber-100 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                {{-- Brand --}}
                <div>
                    <div class="flex items-center gap-2.5 mb-3">
                        <x-application-logo class="h-7 w-7 text-amber-500 shrink-0" />
                        <span class="font-bold text-lg text-gray-900">Kitsune Animal Cafe</span>
                    </div>
                    <p class="text-sm text-gray-500 max-w-xs leading-relaxed">
                        Handcrafted food, specialty drinks, and six resident foxes. A little magic in every visit.
                    </p>
                </div>

                {{-- Explore --}}
                <div class="md:text-right">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Explore</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('menu.index') }}" wire:navigate class="text-gray-500 hover:text-amber-600 transition">Menu</a></li>
                        <li><a href="{{ route('animals.index') }}" wire:navigate class="text-gray-500 hover:text-amber-600 transition">Our Foxes</a></li>
                    </ul>
                </div>

            </div>

            <div class="mt-10 pt-6 border-t border-amber-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} Kitsune Animal Cafe. All rights reserved.</p>
                <p>Created by <span class="font-semibold text-gray-600">Rahmat Haikal Choa</span></p>
            </div>
        </div>
    </footer>

</body>
</html>
