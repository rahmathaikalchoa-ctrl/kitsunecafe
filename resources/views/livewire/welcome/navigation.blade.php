<div>
    {{-- Desktop nav links --}}
    <nav class="hidden sm:flex items-center gap-1 -mx-3">
        <a href="{{ route('menu.index') }}" wire:navigate
           class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-amber-600">
            Menu
        </a>
        <a href="{{ route('animals.index') }}" wire:navigate
           class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-amber-600">
            Our Foxes
        </a>

        <span class="mx-1 text-gray-300">|</span>

        @auth
            <a href="{{ url('/dashboard') }}" wire:navigate
               class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-amber-600">
                Dashboard
            </a>
        @else
            <a href="{{ route('login') }}"
               class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-amber-600">
                Log in
            </a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-amber-600">
                    Register
                </a>
            @endif
        @endauth
    </nav>

    {{-- Mobile hamburger --}}
    <div class="sm:hidden" x-data="{ open: false }">
        <button @click="open = !open"
                aria-label="Toggle navigation"
                class="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
            <svg x-show="!open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div x-show="open"
             x-transition
             @click.outside="open = false"
             class="absolute top-16 right-4 bg-white border border-gray-100 rounded-xl shadow-lg py-2 w-48 z-50">
            <a href="{{ route('menu.index') }}" wire:navigate
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">
                Menu
            </a>
            <a href="{{ route('animals.index') }}" wire:navigate
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">
                Our Foxes
            </a>
            <div class="border-t border-gray-100 my-1"></div>
            @auth
                <a href="{{ url('/dashboard') }}" wire:navigate
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">
                    Log in
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="block px-4 py-2 text-sm text-amber-600 font-medium hover:bg-amber-50 transition">
                        Register
                    </a>
                @endif
            @endauth
        </div>
    </div>
</div>
