<nav class="flex items-center gap-1 -mx-3">
    <a href="{{ route('menu.index') }}" wire:navigate
       class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-amber-600 dark:text-white dark:hover:text-amber-400">
        Menu
    </a>
    <a href="{{ route('animals.index') }}" wire:navigate
       class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-amber-600 dark:text-white dark:hover:text-amber-400">
        Our Foxes
    </a>

    <span class="mx-1 text-gray-300 dark:text-gray-600">|</span>

    @auth
        <a href="{{ url('/dashboard') }}" wire:navigate
           class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-black/70 dark:text-white dark:hover:text-white/80">
            Dashboard
        </a>
    @else
        <a href="{{ route('login') }}" wire:navigate
           class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-black/70 dark:text-white dark:hover:text-white/80">
            Log in
        </a>
        @if (Route::has('register'))
            <a href="{{ route('register') }}" wire:navigate
               class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:text-black/70 dark:text-white dark:hover:text-white/80">
                Register
            </a>
        @endif
    @endauth
</nav>
