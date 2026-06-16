<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    #[Url]
    public ?string $status = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('viewAny', Order::class), 403);

        // Ignore a hand-typed/stale ?status that isn't a real order status.
        if ($this->status !== null && OrderStatus::tryFrom($this->status) === null) {
            $this->status = null;
        }
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function confirm(int $orderId): void
    {
        $this->transition($orderId, OrderStatus::Confirmed);
    }

    public function complete(int $orderId): void
    {
        $this->transition($orderId, OrderStatus::Completed);
    }

    public function reject(int $orderId): void
    {
        $this->transition($orderId, OrderStatus::Cancelled);
    }

    private function transition(int $orderId, OrderStatus $to): void
    {
        $order = Order::findOrFail($orderId);

        abort_unless(auth()->user()->can('manage', $order), 403);

        if (! $order->status->canTransitionTo($to)) {
            return;
        }

        $order->update(['status' => $to]);
    }

    public function with(): array
    {
        return [
            'orders' => Order::query()
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->with('user')
                ->withSum('orderItems', 'quantity')
                ->latest()
                ->paginate(15),
            'statuses' => OrderStatus::cases(),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Orders</h2>
</x-slot>

<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl">

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
                <p class="text-lg">@if ($status) No {{ $status }} orders. @else No orders yet. @endif</p>
            </div>
        @else
            <ul class="space-y-3">
                @foreach ($orders as $order)
                    <li wire:key="admin-order-{{ $order->id }}"
                        class="rounded-2xl bg-white border border-gray-100 shadow-xs p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('orders.show', $order) }}" wire:navigate
                                       class="font-mono font-medium text-gray-700 hover:text-amber-600 transition">{{ $order->reference }}</a>
                                    <x-order-status-badge :status="$order->status" />
                                    <x-order-type-badge :type="$order->order_type" :table="$order->table_number" />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $order->user?->name ?? 'Unknown' }}
                                    · {{ $order->created_at->timezone(config('app.display_timezone'))->format('d M Y, H:i') }}
                                    · {{ $order->order_items_sum_quantity ?? 0 }} {{ Str::plural('item', $order->order_items_sum_quantity ?? 0) }}
                                </p>
                            </div>
                            <span class="text-base font-bold text-amber-700 shrink-0"><x-rupiah :amount="$order->total_cents" /></span>
                        </div>

                        @php
                            $canConfirm = $order->status->canTransitionTo(\App\Enums\OrderStatus::Confirmed);
                            $canComplete = $order->status->canTransitionTo(\App\Enums\OrderStatus::Completed);
                            $canReject = $order->status->canTransitionTo(\App\Enums\OrderStatus::Cancelled);
                        @endphp
                        @if ($canConfirm || $canComplete || $canReject)
                            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3">
                                @if ($canConfirm)
                                    <button wire:click="confirm({{ $order->id }})" wire:loading.attr="disabled" wire:target="confirm({{ $order->id }})" type="button"
                                            class="inline-flex items-center gap-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-1.5 px-3.5 transition-colors disabled:opacity-50 disabled:cursor-wait">
                                        Confirm
                                    </button>
                                @endif
                                @if ($canComplete)
                                    <button wire:click="complete({{ $order->id }})" wire:loading.attr="disabled" wire:target="complete({{ $order->id }})" type="button"
                                            class="inline-flex items-center gap-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-1.5 px-3.5 transition-colors disabled:opacity-50 disabled:cursor-wait">
                                        Mark completed
                                    </button>
                                @endif
                                @if ($canReject)
                                    <button wire:click="reject({{ $order->id }})" wire:confirm="Cancel this customer's order?" wire:loading.attr="disabled" wire:target="reject({{ $order->id }})" type="button"
                                            class="inline-flex items-center gap-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold py-1.5 px-3.5 transition-colors disabled:opacity-50 disabled:cursor-wait">
                                        Reject
                                    </button>
                                @endif
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
