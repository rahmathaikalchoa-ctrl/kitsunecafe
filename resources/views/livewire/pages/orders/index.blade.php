<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public ?string $status = null;

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'orders' => auth()->user()->orders()
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->withSum('orderItems', 'quantity')
                ->latest()
                ->paginate(10),
            'hasOrders' => auth()->user()->orders()->exists(),
            'statuses' => OrderStatus::cases(),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Your Orders</h2>
</x-slot>

<div class="py-8 sm:py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (! $hasOrders)
            <div class="flex flex-col items-center text-center py-16 rounded-2xl bg-white border border-gray-100 shadow-xs">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 mb-3">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                    </svg>
                </div>
                <p class="font-medium text-gray-700">No orders yet</p>
                <p class="mt-1 text-sm text-gray-500 max-w-xs">When you place an order it'll show up here so you can track and reorder it.</p>
                <a href="{{ route('menu.index') }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-2 rounded-lg bg-linear-to-r from-amber-500 to-orange-400
                          hover:from-amber-600 hover:to-orange-500 hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                          active:translate-y-0 text-white text-sm font-semibold py-2 px-5 transition-all duration-200
                          focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1">
                    Browse the menu
                </a>
            </div>
        @else
            {{-- Status filter --}}
            <div class="flex flex-wrap gap-2 mb-6">
                <button
                    wire:click="$set('status', null)"
                    aria-pressed="{{ $status === null ? 'true' : 'false' }}"
                    @class([
                        'px-4 py-1.5 rounded-full text-sm font-medium transition',
                        'bg-amber-500 text-white' => $status === null,
                        'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' => $status !== null,
                    ])>
                    All
                </button>
                @foreach ($statuses as $case)
                    <button
                        wire:click="$set('status', '{{ $case->value }}')"
                        aria-pressed="{{ $status === $case->value ? 'true' : 'false' }}"
                        @class([
                            'px-4 py-1.5 rounded-full text-sm font-medium transition',
                            'bg-amber-500 text-white' => $status === $case->value,
                            'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' => $status !== $case->value,
                        ])>
                        {{ $case->label() }}
                    </button>
                @endforeach
            </div>

            @if ($orders->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <p class="text-lg">No {{ $status }} orders.</p>
                    <button wire:click="$set('status', null)" class="mt-3 text-sm font-medium text-amber-600 hover:underline">Show all orders</button>
                </div>
            @else
            <ul class="space-y-3">
                @foreach ($orders as $order)
                    <li wire:key="order-{{ $order->id }}">
                        <a href="{{ route('orders.show', $order) }}" wire:navigate
                           class="group flex items-center justify-between gap-4 rounded-2xl bg-white border border-gray-100 shadow-xs p-5
                                  transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-amber-100/70 hover:border-amber-200
                                  focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span class="font-mono font-medium text-gray-700">#{{ $order->id }}</span>
                                    <x-order-status-badge :status="$order->status" />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $order->created_at->timezone(config('app.display_timezone'))->format('d M Y, H:i') }}
                                    · {{ $order->order_items_sum_quantity ?? 0 }} {{ Str::plural('item', $order->order_items_sum_quantity ?? 0) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-base font-bold text-amber-700"><x-rupiah :amount="$order->total_cents" /></span>
                                <svg class="h-4 w-4 text-gray-300 group-hover:text-amber-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                </svg>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
