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
        ['route' => 'admin.dashboard', 'label' => 'Overview', 'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z'],
        ['route' => 'admin.orders', 'label' => 'Orders', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z'],
        ['route' => 'admin.menu', 'label' => 'Menu', 'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
        ['route' => 'admin.categories', 'label' => 'Categories', 'icon' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z'],
        ['route' => 'admin.animals', 'label' => 'Animals', 'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z'],
    ];

    $user = auth()->user();
    $initials = collect(explode(' ', trim($user?->name ?? '')))
        ->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
@endphp

<div class="flex h-full flex-col">
    {{-- Brand --}}
    <div class="px-5 py-5">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2.5 group">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-linear-to-br from-amber-500 to-orange-400 text-white shadow-sm transition-transform duration-300 group-hover:-rotate-6">
                <x-application-logo class="h-5 w-5" />
            </span>
            <span class="font-bold tracking-tight text-gray-900 leading-none">
                Kitsune<span class="text-amber-500"> Admin</span>
            </span>
        </a>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 space-y-1">
        @foreach ($links as $link)
            @php $active = request()->routeIs($link['route']) || ($link['route'] === 'admin.dashboard' && request()->routeIs('admin.dashboard')); @endphp
            <a href="{{ route($link['route']) }}" wire:navigate
               @class([
                   'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
                   'bg-linear-to-r from-amber-500 to-orange-400 text-white shadow-sm shadow-amber-300/40' => $active,
                   'text-gray-500 hover:bg-amber-50 hover:text-amber-700' => ! $active,
               ])>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                </svg>
                {{ $link['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- User + actions --}}
    <div class="mt-2 px-3 py-4 border-t border-gray-100">
        <div class="flex items-center gap-3 px-2 pb-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-amber-500 to-orange-400 text-sm font-bold text-white">
                {{ $initials ?: '?' }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-gray-800">{{ $user?->name }}</p>
                <p class="text-xs text-amber-600 font-medium">Staff</p>
            </div>
        </div>

        <a href="{{ route('dashboard') }}" wire:navigate
           class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back to site
        </a>
        <button wire:click="logout" type="button"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
            Log Out
        </button>
    </div>
</div>
