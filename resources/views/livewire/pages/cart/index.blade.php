<?php

use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function cartItems()
    {
        return app(CartService::class)->items();
    }

    #[Computed]
    public function total(): int
    {
        // Only available lines count toward what the customer can actually check out with.
        return (int) $this->cartItems
            ->filter(fn ($line) => $line->item->is_available)
            ->sum('subtotal_cents');
    }

    /** True when any line is no longer available — blocks checkout until it's removed. */
    #[Computed]
    public function hasUnavailable(): bool
    {
        return $this->cartItems->contains(fn ($line) => ! $line->item->is_available);
    }

    public function increment(int $menuItemId, CartService $cart): void
    {
        $cart->increment($menuItemId);
        $this->refreshTotals();
    }

    public function decrement(int $menuItemId, CartService $cart): void
    {
        $cart->decrement($menuItemId);
        $this->refreshTotals();
    }

    public function remove(int $menuItemId, CartService $cart): void
    {
        $cart->remove($menuItemId);
        $this->refreshTotals();
    }

    private function refreshTotals(): void
    {
        unset($this->cartItems, $this->total, $this->hasUnavailable);

        // Keep the navbar cart-count badge in sync with edits made on this page.
        $this->dispatch('cart-updated');
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Your Cart</h2>
</x-slot>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('reorder_skipped'))
            @php $skipped = (int) session('reorder_skipped'); @endphp
            <div class="mb-6 flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-100 px-4 py-3 text-sm text-amber-800" role="status">
                <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                </svg>
                <span>{{ $skipped }} {{ Str::plural('item', $skipped) }} from that order {{ $skipped === 1 ? "isn't" : "aren't" }} available right now and {{ $skipped === 1 ? 'was' : 'were' }} left out.</span>
            </div>
        @endif

        @if ($this->cartItems->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <div class="flex justify-center mb-4">
                    <svg class="w-14 h-14 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                </div>
                <p class="text-lg font-medium">Your cart is empty</p>
                <p class="text-sm mt-1">Head over to the menu to start adding items.</p>
                <div class="mt-6">
                    <a href="{{ route('menu.index') }}" wire:navigate>
                        <flux:button variant="primary">Browse the Menu</flux:button>
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-3 text-gray-500 font-medium">Item</th>
                            <th class="text-center px-4 py-3 text-gray-500 font-medium">Qty</th>
                            <th class="text-right px-6 py-3 text-gray-500 font-medium">Subtotal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($this->cartItems as $line)
                            <tr wire:key="cart-{{ $line->item->id }}" @class(['opacity-60' => ! $line->item->is_available])>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">
                                        {{ $line->item->name }}
                                        @unless ($line->item->is_available)
                                            <span class="ml-1 inline-flex items-center rounded-full bg-red-50 border border-red-100 text-red-600 text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5">Unavailable</span>
                                        @endunless
                                    </p>
                                    <p class="text-gray-400 text-xs"><x-rupiah :amount="$line->item->price_cents" /> each</p>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="decrement({{ $line->item->id }})"
                                                @disabled(! $line->item->is_available)
                                                aria-label="Decrease quantity of {{ $line->item->name }}"
                                                class="w-7 h-7 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-amber-400 hover:text-amber-600 transition text-base disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:border-gray-200 disabled:hover:text-gray-500">−</button>
                                        <span class="w-6 text-center font-medium text-gray-800">{{ $line->quantity }}</span>
                                        <button wire:click="increment({{ $line->item->id }})"
                                                @disabled(! $line->item->is_available)
                                                aria-label="Increase quantity of {{ $line->item->name }}"
                                                class="w-7 h-7 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-amber-400 hover:text-amber-600 transition text-base disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:border-gray-200 disabled:hover:text-gray-500">+</button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-800">
                                    <x-rupiah :amount="$line->subtotal_cents" />
                                </td>
                                <td class="px-4 py-4">
                                    <button wire:click="remove({{ $line->item->id }})"
                                            aria-label="Remove {{ $line->item->name }}"
                                            class="text-gray-300 hover:text-red-400 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-100">
                        <tr>
                            <td colspan="2" class="px-6 py-4 font-semibold text-gray-700">Total</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900 text-base">
                                <x-rupiah :amount="$this->total" />
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($this->hasUnavailable)
                <div class="mt-6 flex items-start gap-3 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700" role="alert">
                    <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <span>Some items are no longer available. Please remove them to continue to checkout.</span>
                </div>
            @endif

            <div class="mt-6 flex justify-end">
                @if ($this->hasUnavailable)
                    <flux:button variant="primary" disabled>
                        Proceed to Checkout
                    </flux:button>
                @else
                    <a href="{{ route('checkout.index') }}" wire:navigate>
                        <flux:button variant="primary">
                            <span class="flex items-center gap-2">
                                Proceed to Checkout
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                            </span>
                        </flux:button>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
