<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Animal;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Computed]
    public function pendingOrders(): int
    {
        return Order::where('status', OrderStatus::Pending->value)->count();
    }

    #[Computed]
    public function ordersToday(): int
    {
        // Orders placed since local midnight (timestamps are stored in UTC).
        $startOfDay = now()->timezone(config('app.display_timezone'))->startOfDay()->utc();

        return Order::where('created_at', '>=', $startOfDay)->count();
    }

    #[Computed]
    public function menuItems(): int
    {
        return MenuItem::count();
    }

    #[Computed]
    public function availableItems(): int
    {
        return MenuItem::available()->count();
    }

    #[Computed]
    public function activeFoxes(): int
    {
        return Animal::active()->count();
    }

    #[Computed]
    public function customers(): int
    {
        return User::where('is_staff', false)->count();
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Overview</h2>
</x-slot>

@php
    $firstName = explode(' ', trim(auth()->user()->name))[0] ?? 'there';
@endphp

<div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-3xl bg-linear-to-br from-amber-500 via-orange-400 to-amber-400 px-6 py-7 sm:px-8 shadow-lg shadow-amber-200/50">
        <div class="pointer-events-none absolute -top-10 -right-8 h-40 w-40 rounded-full bg-white/20 blur-2xl" aria-hidden="true"></div>
        <svg class="pointer-events-none absolute right-4 sm:right-10 top-1/2 -translate-y-1/2 h-40 w-40 text-white/15 select-none" viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
            <polygon points="28,72 6,14 52,52"/><polygon points="72,72 94,14 48,52"/><circle cx="50" cy="74" r="32"/>
        </svg>
        <div class="relative">
            <p class="text-amber-50/90 text-sm font-medium">Admin · Kitsune Animal Cafe</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-white">Welcome back, {{ $firstName }} 🦊</h1>
            <p class="mt-2 text-amber-50/90 text-sm max-w-lg">
                @if ($this->pendingOrders > 0)
                    You have <span class="font-semibold text-white">{{ $this->pendingOrders }}</span> {{ Str::plural('order', $this->pendingOrders) }} waiting to be confirmed.
                @else
                    No orders waiting — everything's caught up.
                @endif
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.orders', ['status' => 'pending']) }}" wire:navigate
                   class="inline-flex items-center gap-1.5 rounded-lg bg-white/95 hover:bg-white text-amber-700 text-sm font-semibold px-4 py-2 transition">
                    Review orders
                </a>
                <a href="{{ route('admin.menu') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 rounded-lg bg-white/15 hover:bg-white/25 text-white text-sm font-semibold px-4 py-2 transition">
                    Manage menu
                </a>
            </div>
        </div>
    </section>

    {{-- Stat cards --}}
    @php
        $cards = [
            ['label' => 'Pending orders', 'value' => $this->pendingOrders, 'sub' => 'Need confirming', 'href' => route('admin.orders', ['status' => 'pending']), 'tint' => 'bg-amber-50 text-amber-600', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['label' => 'Orders today', 'value' => $this->ordersToday, 'sub' => 'Since local midnight', 'href' => route('admin.orders'), 'tint' => 'bg-blue-50 text-blue-600', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
            ['label' => 'Menu items', 'value' => $this->availableItems.' / '.$this->menuItems, 'sub' => 'Available / total', 'href' => route('admin.menu'), 'tint' => 'bg-orange-50 text-orange-600', 'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
            ['label' => 'Active foxes', 'value' => $this->activeFoxes, 'sub' => 'Shown to customers', 'href' => route('admin.animals'), 'tint' => 'bg-green-50 text-green-600', 'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z'],
            ['label' => 'Customers', 'value' => $this->customers, 'sub' => 'Registered accounts', 'href' => null, 'tint' => 'bg-gray-100 text-gray-500', 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach ($cards as $card)
            @php $tag = $card['href'] ? 'a' : 'div'; @endphp
            <{{ $tag }}
                @if ($card['href']) href="{{ $card['href'] }}" wire:navigate @endif
                @class([
                    'rounded-2xl bg-white border border-gray-100 shadow-xs p-5 flex items-center gap-4',
                    'transition hover:-translate-y-0.5 hover:shadow-md hover:border-amber-200' => $card['href'],
                ])>
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $card['tint'] }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                    <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    <p class="text-xs text-gray-400">{{ $card['sub'] }}</p>
                </div>
            </{{ $tag }}>
        @endforeach
    </div>
</div>
