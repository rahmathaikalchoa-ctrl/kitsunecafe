<?php

use App\Enums\OrderType;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $orderType = 'dine_in';

    public ?int $tableNumber = null;

    public string $notes = '';

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

    #[Computed]
    public function tablesCount(): int
    {
        return (int) config('app.tables_count');
    }

    public function placeOrder(OrderService $orderService): void
    {
        $dineIn = $this->orderType === OrderType::DineIn->value;

        $this->validate([
            'orderType' => ['required', Rule::enum(OrderType::class)],
            'tableNumber' => [
                $dineIn ? 'required' : 'nullable',
                'integer', 'min:1', 'max:'.$this->tablesCount,
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'tableNumber.required' => 'Please choose your table.',
        ]);

        $order = $orderService->placeOrder(
            auth()->user(),
            OrderType::from($this->orderType),
            $this->tableNumber,
            $this->notes ?: null,
        );

        session()->flash('order_placed', "Order #{$order->id} placed successfully!");

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout</h2>
</x-slot>

<div class="py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        @if ($this->cartItems->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <p class="text-lg">Nothing to checkout. Add items to your cart first.</p>
                <div class="mt-6">
                    <a href="{{ route('menu.index') }}" wire:navigate>
                        <flux:button variant="primary">Back to Menu</flux:button>
                    </a>
                </div>
            </div>
        @else
            <form wire:submit="placeOrder" class="space-y-6">

                {{-- Order summary --}}
                <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">Order Summary</h3>
                    </div>
                    <ul class="divide-y divide-gray-50">
                        @foreach ($this->cartItems as $line)
                            <li class="flex justify-between px-6 py-3 text-sm">
                                <span class="text-gray-700">{{ $line->item->name }} × {{ $line->quantity }}</span>
                                <span class="font-medium text-gray-900"><x-rupiah :amount="$line->subtotal_cents" /></span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex justify-between px-6 py-4 bg-gray-50 border-t border-gray-100">
                        <span class="font-semibold text-gray-700">Total</span>
                        <span class="font-bold text-gray-900"><x-rupiah :amount="$this->total" /></span>
                    </div>
                </div>

                {{-- Dine-in or takeaway --}}
                <flux:radio.group wire:model.live="orderType" label="Order type" variant="segmented">
                    <flux:radio value="dine_in" label="Dine-in" />
                    <flux:radio value="takeaway" label="Takeaway" />
                </flux:radio.group>

                {{-- Table (dine-in only) --}}
                @if ($orderType === 'dine_in')
                    <flux:field>
                        <flux:label>Table</flux:label>
                        <flux:select wire:model="tableNumber" placeholder="Choose your table…">
                            @for ($i = 1; $i <= $this->tablesCount; $i++)
                                <flux:select.option value="{{ $i }}">Table {{ $i }}</flux:select.option>
                            @endfor
                        </flux:select>
                        <flux:error name="tableNumber" />
                    </flux:field>
                @endif

                {{-- Notes --}}
                <flux:field>
                    <flux:label>Special requests <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Allergies, seating preferences, etc." />
                    <flux:error name="notes" />
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">
                    Place Order
                </flux:button>

                <p class="text-center text-sm text-gray-400">
                    You can pay at the counter when your order is ready.
                </p>
            </form>
        @endif
    </div>
</div>
