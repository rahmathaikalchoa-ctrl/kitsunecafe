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
        return app(CartService::class)->totalCents();
    }

    public function remove(int $menuItemId): void
    {
        app(CartService::class)->remove($menuItemId);
        unset($this->cartItems);
        unset($this->total);
    }

    public function update(int $menuItemId, int $qty): void
    {
        app(CartService::class)->update($menuItemId, $qty);
        unset($this->cartItems);
        unset($this->total);
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Your Cart</h2>
</x-slot>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        @if ($this->cartItems->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <p class="text-5xl mb-4">🛒</p>
                <p class="text-lg font-medium">Your cart is empty</p>
                <p class="text-sm mt-1">Head over to the menu to start adding items.</p>
                <div class="mt-6">
                    <a href="{{ route('menu.index') }}" wire:navigate>
                        <flux:button variant="primary">Browse the Menu</flux:button>
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                            <tr wire:key="cart-{{ $line->item->id }}">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $line->item->name }}</p>
                                    <p class="text-gray-400 text-xs">Rp {{ number_format($line->item->price_cents / 100, 0, ',', '.') }} each</p>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="update({{ $line->item->id }}, {{ $line->quantity - 1 }})"
                                                class="w-7 h-7 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-amber-400 hover:text-amber-600 transition text-base">−</button>
                                        <span class="w-6 text-center font-medium text-gray-800">{{ $line->quantity }}</span>
                                        <button wire:click="update({{ $line->item->id }}, {{ $line->quantity + 1 }})"
                                                class="w-7 h-7 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-amber-400 hover:text-amber-600 transition text-base">+</button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-800">
                                    Rp {{ number_format($line->subtotal_cents / 100, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4">
                                    <button wire:click="remove({{ $line->item->id }})"
                                            class="text-gray-300 hover:text-red-400 transition">✕</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-100">
                        <tr>
                            <td colspan="2" class="px-6 py-4 font-semibold text-gray-700">Total</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900 text-base">
                                Rp {{ number_format($this->total / 100, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('checkout.index') }}" wire:navigate>
                    <flux:button variant="primary">Proceed to Checkout →</flux:button>
                </a>
            </div>
        @endif
    </div>
</div>
