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

    #[Url]
    public string $search = '';

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

    public function updatedSearch(): void
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
        // Whole-table counts in one grouped query — independent of the active filter so the
        // overview always reflects the full picture.
        $counts = Order::selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

        return [
            'orders' => Order::query()
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->when(trim($this->search), function ($q, $term) {
                    // "ORD-0007", "0007", "7" → order id; otherwise match the customer.
                    $idTerm = ltrim(preg_replace('/\D/', '', $term), '0');

                    $q->where(function ($q) use ($term, $idTerm) {
                        $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));

                        if ($idTerm !== '') {
                            $q->orWhere('id', (int) $idTerm);
                        }
                    });
                })
                ->with(['user', 'orderItems.menuItem'])
                ->withSum('orderItems', 'quantity')
                ->latest()
                ->paginate(15),
            'statuses' => OrderStatus::cases(),
            'statusCounts' => $counts,
            'totalCount' => (int) $counts->sum(),
        ];
    }
}; ?>

<x-slot name="header">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Orders</h2>
        <p class="text-xs text-gray-400 mt-0.5">Track and advance customer orders through the kitchen.</p>
    </div>
</x-slot>

@php
    $statusMeta = [
        'pending' => ['tint' => 'bg-amber-50 text-amber-600', 'sub' => 'Awaiting action', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'confirmed' => ['tint' => 'bg-blue-50 text-blue-600', 'sub' => 'In progress', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'completed' => ['tint' => 'bg-green-50 text-green-600', 'sub' => 'Served', 'icon' => 'M4.5 12.75l6 6 9-13.5'],
        'cancelled' => ['tint' => 'bg-gray-100 text-gray-500', 'sub' => 'Cancelled', 'icon' => 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ];
@endphp

<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl">

        {{-- Overview --}}
        @if ($totalCount > 0)
            <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ($statuses as $case)
                    <x-admin.stat-card
                        :label="$case->label()"
                        :value="$statusCounts[$case->value] ?? 0"
                        :sub="$statusMeta[$case->value]['sub']"
                        :tint="$statusMeta[$case->value]['tint']"
                        :icon="$statusMeta[$case->value]['icon']" />
                @endforeach
            </div>
        @endif

        {{-- Search --}}
        <div class="mb-4">
            <flux:field>
                <flux:label class="sr-only">Search orders</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" type="search"
                    placeholder="Search by order # or customer…" icon="magnifying-glass" clearable />
            </flux:field>
        </div>

        {{-- Status filter --}}
        <div class="flex flex-wrap gap-2 mb-6">
            <button
                wire:click="$set('status', null)"
                aria-pressed="{{ $status === null ? 'true' : 'false' }}"
                @class([
                    'inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-medium transition',
                    'bg-amber-500 text-white' => $status === null,
                    'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' => $status !== null,
                ])>
                All
                <span @class([
                    'inline-flex items-center justify-center min-w-5 px-1.5 rounded-full text-xs font-semibold',
                    'bg-white/25 text-white' => $status === null,
                    'bg-gray-100 text-gray-500' => $status !== null,
                ])>{{ $totalCount }}</span>
            </button>
            @foreach ($statuses as $case)
                <button
                    wire:click="$set('status', '{{ $case->value }}')"
                    aria-pressed="{{ $status === $case->value ? 'true' : 'false' }}"
                    @class([
                        'inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-medium transition',
                        'bg-amber-500 text-white' => $status === $case->value,
                        'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' => $status !== $case->value,
                    ])>
                    {{ $case->label() }}
                    <span @class([
                        'inline-flex items-center justify-center min-w-5 px-1.5 rounded-full text-xs font-semibold',
                        'bg-white/25 text-white' => $status === $case->value,
                        'bg-gray-100 text-gray-500' => $status !== $case->value,
                    ])>{{ $statusCounts[$case->value] ?? 0 }}</span>
                </button>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-lg">@if ($search) No orders match “{{ $search }}”. @elseif ($status) No {{ $status }} orders. @else No orders yet. @endif</p>
            </div>
        @else
            <ul class="space-y-3">
                @foreach ($orders as $order)
                    <li wire:key="admin-order-{{ $order->id }}"
                        x-data="{ open: false }"
                        class="rounded-2xl bg-white border border-gray-100 border-l-4 {{ $order->status->accentClass() }} shadow-xs p-5 transition hover:border-amber-200 hover:shadow-md">
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

                        {{-- Details: line items + the customer's special requests --}}
                        <div class="mt-2">
                            <button type="button" x-on:click="open = ! open"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 hover:underline">
                                <span x-text="open ? 'Hide details' : 'View details'">View details</span>
                                <svg class="h-3.5 w-3.5 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition style="display:none" class="mt-3 rounded-xl bg-gray-50 border border-gray-100 p-4">
                                <ul class="divide-y divide-gray-100 text-sm">
                                    @foreach ($order->orderItems as $line)
                                        <li class="flex items-center justify-between py-1.5">
                                            <span class="text-gray-700"><span class="text-gray-400">{{ $line->quantity }}×</span> {{ $line->menuItem?->name ?? 'Removed item' }}</span>
                                            <span class="text-gray-500"><x-rupiah :amount="$line->price_cents * $line->quantity" /></span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($order->notes)
                                    <div class="mt-3 border-t border-gray-100 pt-3">
                                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400 mb-1">Special requests</p>
                                        <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                                    </div>
                                @else
                                    <p class="mt-3 border-t border-gray-100 pt-3 text-sm text-gray-400">No special requests.</p>
                                @endif
                            </div>
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
