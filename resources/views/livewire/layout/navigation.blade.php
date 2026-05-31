<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- Logo + desktop nav links --}}
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-zinc-200" />
                    </a>
                </div>

                <div class="hidden sm:-my-px sm:ms-10 sm:flex sm:items-center sm:gap-6">
                    @foreach ([['menu.index', 'Menu'], ['animals.index', 'Our Foxes'], ['dashboard', 'Dashboard']] as [$routeName, $label])
                        <a href="{{ route($routeName) }}"
                           wire:navigate
                           @class([
                               'text-sm font-medium border-b-2 pb-0.5 transition',
                               'border-amber-500 text-gray-900 dark:text-white' => request()->routeIs($routeName),
                               'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-zinc-400 dark:hover:text-zinc-200' => ! request()->routeIs($routeName),
                           ])>
                            {{ $label }}
                        </a>
                    @endforeach

                    <a href="{{ route('cart.index') }}" wire:navigate
                       class="relative text-gray-500 hover:text-amber-600 transition">
                        🛒
                    </a>
                </div>
            </div>

            {{-- User dropdown (desktop) --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <flux:dropdown>
                        <flux:button
                            variant="ghost"
                            size="sm"
                            x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                            x-text="name"
                            x-on:profile-updated.window="name = $event.detail.name"
                            icon:trailing="chevron-down"
                        />

                        <flux:menu>
                            <flux:menu.item href="{{ route('profile') }}" wire:navigate>
                                {{ __('Profile') }}
                            </flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item wire:click="logout">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" wire:navigate
                           class="text-sm text-gray-600 hover:text-gray-900 transition">Log in</a>
                        <a href="{{ route('register') }}" wire:navigate>
                            <flux:button size="sm" variant="primary">Register</flux:button>
                        </a>
                    </div>
                @endauth
            </div>

            {{-- Hamburger (mobile) --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open}" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open}" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ([['menu.index', 'Menu'], ['animals.index', 'Our Foxes'], ['dashboard', 'Dashboard'], ['cart.index', 'Cart']] as [$routeName, $label])
                <a href="{{ route($routeName) }}"
                   wire:navigate
                   @class([
                       'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-sm font-medium transition duration-150 ease-in-out',
                       'border-amber-400 text-amber-700 bg-amber-50' => request()->routeIs($routeName),
                       'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' => ! request()->routeIs($routeName),
                   ])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @auth
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-zinc-700">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-zinc-200"
                         x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                         x-text="name"
                         x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <a href="{{ route('profile') }}"
                       wire:navigate
                       class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">
                        {{ __('Profile') }}
                    </a>

                    <button wire:click="logout"
                            class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">
                        {{ __('Log Out') }}
                    </button>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200 px-4 flex gap-4">
                <a href="{{ route('login') }}" wire:navigate
                   class="text-sm font-medium text-gray-600 hover:text-gray-900">Log in</a>
                <a href="{{ route('register') }}" wire:navigate
                   class="text-sm font-medium text-amber-600 hover:text-amber-700">Register</a>
            </div>
        @endauth
    </div>
</nav>
