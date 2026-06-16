<?php

declare(strict_types=1);

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

@php
    $links = [
        ['admin.dashboard', 'Overview'],
        ['admin.orders', 'Orders'],
        ['admin.menu', 'Menu'],
        ['admin.categories', 'Categories'],
        ['admin.animals', 'Animals'],
    ];
@endphp

<div class="flex h-full flex-col">
    {{-- Brand --}}
    <div class="px-5 py-5 border-b border-gray-100">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 group">
            <x-application-logo class="block h-8 w-8 text-amber-500 transition-transform duration-300 group-hover:-rotate-6" />
            <span class="font-bold tracking-tight text-gray-900 leading-none">
                Kitsune<span class="text-amber-500"> Admin</span>
            </span>
        </a>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 space-y-1">
        @foreach ($links as [$routeName, $label])
            <a href="{{ route($routeName) }}" wire:navigate
               @class([
                   'block rounded-lg px-3 py-2 text-sm font-medium transition',
                   'bg-amber-100 text-amber-700' => request()->routeIs($routeName),
                   'text-gray-600 hover:bg-amber-50 hover:text-amber-600' => ! request()->routeIs($routeName),
               ])>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    {{-- Footer actions --}}
    <div class="px-3 py-4 border-t border-gray-100 space-y-1">
        <a href="{{ route('dashboard') }}" wire:navigate
           class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition">
            ← Back to site
        </a>
        <button wire:click="logout" type="button"
                class="block w-full text-left rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600 transition">
            Log Out
        </button>
    </div>
</div>
