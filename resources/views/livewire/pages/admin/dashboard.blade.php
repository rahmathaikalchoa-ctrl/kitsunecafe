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

<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">

        <a href="{{ route('admin.orders', ['status' => 'pending']) }}" wire:navigate
           class="rounded-2xl bg-white border border-gray-100 shadow-xs p-5 transition hover:-translate-y-0.5 hover:shadow-md hover:border-amber-200">
            <p class="text-sm font-medium text-gray-500">Pending orders</p>
            <p class="mt-1 text-3xl font-bold text-amber-700">{{ $this->pendingOrders }}</p>
            <p class="mt-1 text-xs text-gray-400">Need confirming</p>
        </a>

        <a href="{{ route('admin.orders') }}" wire:navigate
           class="rounded-2xl bg-white border border-gray-100 shadow-xs p-5 transition hover:-translate-y-0.5 hover:shadow-md hover:border-amber-200">
            <p class="text-sm font-medium text-gray-500">Orders today</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $this->ordersToday }}</p>
            <p class="mt-1 text-xs text-gray-400">Since local midnight</p>
        </a>

        <a href="{{ route('admin.menu') }}" wire:navigate
           class="rounded-2xl bg-white border border-gray-100 shadow-xs p-5 transition hover:-translate-y-0.5 hover:shadow-md hover:border-amber-200">
            <p class="text-sm font-medium text-gray-500">Menu items</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $this->availableItems }} <span class="text-base font-medium text-gray-400">/ {{ $this->menuItems }}</span></p>
            <p class="mt-1 text-xs text-gray-400">Available / total</p>
        </a>

        <a href="{{ route('admin.animals') }}" wire:navigate
           class="rounded-2xl bg-white border border-gray-100 shadow-xs p-5 transition hover:-translate-y-0.5 hover:shadow-md hover:border-amber-200">
            <p class="text-sm font-medium text-gray-500">Active foxes</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $this->activeFoxes }}</p>
            <p class="mt-1 text-xs text-gray-400">Shown to customers</p>
        </a>

        <div class="rounded-2xl bg-white border border-gray-100 shadow-xs p-5">
            <p class="text-sm font-medium text-gray-500">Customers</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $this->customers }}</p>
            <p class="mt-1 text-xs text-gray-400">Registered accounts</p>
        </div>

    </div>
</div>
