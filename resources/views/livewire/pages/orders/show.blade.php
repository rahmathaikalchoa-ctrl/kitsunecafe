<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load('orderItems.menuItem');
        $this->order = $order;
    }

    // Re-add this order's still-available items to the cart.
    public function orderAgain(CartService $cart): void
    {
        $result = $cart->addFromOrder($this->order);

        if ($result['added'] === 0) {
            $this->dispatch('cart-unavailable');

            return;
        }

        // Some items were dropped — let the cart page explain why after we navigate.
        if ($result['skipped'] > 0) {
            session()->flash('reorder_skipped', $result['skipped']);
        }

        $this->redirectRoute('cart.index', navigate: true);
    }

    // Let the customer cancel their own order while it is still pending.
    public function cancel(): void
    {
        abort_unless($this->order->user_id === auth()->id(), 403);

        if (! $this->order->status->isCancellable()) {
            return;
        }

        $this->order->update(['status' => OrderStatus::Cancelled]);
        $this->order->refresh();
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Order #{{ $order->id }}</h2>
</x-slot>

<div class="py-8 sm:py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <a href="{{ route('orders.index') }}" wire:navigate
           class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-amber-600 transition mb-4">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
            All orders
        </a>

        <div class="rounded-2xl bg-white border border-gray-100 shadow-xs p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono font-semibold text-gray-800">#{{ $order->id }}</span>
                    <x-order-status-badge :status="$order->status" />
                    <x-order-type-badge :type="$order->order_type" :table="$order->table_number" />
                </div>
                <span class="text-sm text-gray-500">
                    {{ $order->created_at->timezone(config('app.display_timezone'))->format('d M Y, H:i') }}
                </span>
            </div>

            {{-- Line items --}}
            <ul class="divide-y divide-gray-100 border-t border-gray-100">
                @foreach ($order->orderItems as $line)
                    <li class="flex items-center justify-between py-3 text-sm">
                        <span class="text-gray-700">
                            <span class="font-medium text-gray-500">{{ $line->quantity }}×</span>
                            {{ $line->menuItem?->name ?? 'Removed item' }}
                        </span>
                        <span class="text-gray-500"><x-rupiah :amount="$line->price_cents * $line->quantity" /></span>
                    </li>
                @endforeach
            </ul>

            @if ($order->notes)
                <div class="mt-4 rounded-xl bg-gray-50 border border-gray-100 p-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 mb-1">Special requests</p>
                    <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                </div>
            @endif

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                <p class="text-sm text-gray-500">
                    Total <span class="ml-1 text-lg font-bold text-amber-700"><x-rupiah :amount="$order->total_cents" /></span>
                </p>
                <div class="flex items-center gap-2">
                    @if ($order->status->isCancellable())
                        <button
                            wire:click="cancel"
                            wire:confirm="Cancel this order? This can't be undone."
                            wire:loading.attr="disabled"
                            wire:target="cancel"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 text-gray-600 text-sm font-semibold py-2 px-4
                                   hover:border-red-300 hover:text-red-600 transition-colors duration-200
                                   focus:outline-hidden focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:ring-offset-1
                                   disabled:opacity-50 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="cancel">Cancel order</span>
                            <span wire:loading wire:target="cancel">Cancelling…</span>
                        </button>
                    @endif

                    <button
                        wire:click="orderAgain"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-wait"
                        wire:target="orderAgain"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-linear-to-r from-amber-500 to-orange-400
                               hover:from-amber-600 hover:to-orange-500 hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                               active:translate-y-0 active:scale-[0.97] text-white text-sm font-semibold py-2 px-4
                               transition-all duration-200 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                               disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        <span wire:loading.remove wire:target="orderAgain">Order again</span>
                        <span wire:loading wire:target="orderAgain">Adding…</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Unavailable-items toast --}}
        <div
            x-data="{ show: false }"
            x-on:cart-unavailable.window="show = true; setTimeout(() => show = false, 3500)"
            x-show="show"
            x-transition
            class="fixed bottom-6 right-6 bg-gray-900 text-white text-sm px-4 py-2.5 rounded-xl shadow-lg z-50 flex items-center gap-2"
            role="status"
            aria-live="polite"
            style="display:none"
        >
            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
            </svg>
            Those items aren't available right now
        </div>
    </div>
</div>
