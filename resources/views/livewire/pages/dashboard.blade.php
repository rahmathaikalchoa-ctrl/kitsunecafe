<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Animal;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    // ── Computed: greeting ───────────────────────────────────────────────────
    #[Computed]
    public function greeting(): string
    {
        return match (true) {
            now()->hour < 12 => 'Good morning',
            now()->hour < 17 => 'Good afternoon',
            now()->hour < 21 => 'Good evening',
            default => 'Still awake',
        };
    }

    // ── Computed: personal stats ─────────────────────────────────────────────
    #[Computed]
    public function ordersCount(): int
    {
        return auth()->user()->orders()
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->count();
    }

    #[Computed]
    public function reviewsCount(): int
    {
        return auth()->user()->reviews()->count();
    }

    #[Computed]
    public function memberSince(): string
    {
        return auth()->user()->created_at->format('M Y');
    }

    // ── Computed: latest order ───────────────────────────────────────────────
    #[Computed]
    public function latestOrder(): ?Order
    {
        return auth()->user()->orders()
            ->with('orderItems.menuItem')
            ->latest()
            ->first();
    }

    // ── Computed: fox spotlight (rotates daily) ──────────────────────────────
    #[Computed]
    public function spotlightFox(): ?Animal
    {
        $count = Animal::active()->count();

        if ($count === 0) {
            return null;
        }

        // Day-of-year offset keeps the pick stable within a day but rotating.
        $offset = (int) now()->dayOfYear % $count;

        return Animal::active()->orderBy('id')->skip($offset)->first();
    }

    // ── Computed: popular items (by quantity sold, newest as fallback) ────────
    #[Computed]
    public function popularItems()
    {
        $rankedIds = OrderItem::query()
            ->selectRaw('menu_item_id, SUM(quantity) as sold')
            ->groupBy('menu_item_id')
            ->orderByDesc('sold')
            ->limit(8)
            ->pluck('menu_item_id');

        $items = MenuItem::available()
            ->with(['category', 'reviews'])
            ->when($rankedIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $rankedIds))
            ->get();

        if ($rankedIds->isNotEmpty()) {
            $order = $rankedIds->flip();
            $items = $items->sortBy(fn ($item) => $order[$item->id] ?? PHP_INT_MAX)->values();
        } else {
            // No orders yet anywhere — show the freshest items instead.
            $items = MenuItem::available()
                ->with(['category', 'reviews'])
                ->latest()
                ->limit(4)
                ->get();
        }

        return $items->take(4);
    }

    // ── Action: re-add the last order to the cart ────────────────────────────
    public function orderAgain(CartService $cart): void
    {
        $order = $this->latestOrder;

        if (! $order) {
            return;
        }

        $added = false;

        foreach ($order->orderItems as $line) {
            if ($line->menuItem?->is_available) {
                $cart->add($line->menu_item_id, $line->quantity);
                $added = true;
            }
        }

        if ($added) {
            $this->redirectRoute('cart.index', navigate: true);
        }
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
</x-slot>

@php
    $statusStyles = [
        'pending'   => ['label' => 'Pending',   'dot' => 'bg-amber-400',   'chip' => 'bg-amber-50 text-amber-700 border-amber-100'],
        'confirmed' => ['label' => 'Confirmed', 'dot' => 'bg-blue-400',    'chip' => 'bg-blue-50 text-blue-700 border-blue-100'],
        'completed' => ['label' => 'Completed', 'dot' => 'bg-green-500',   'chip' => 'bg-green-50 text-green-700 border-green-100'],
        'cancelled' => ['label' => 'Cancelled', 'dot' => 'bg-gray-400',    'chip' => 'bg-gray-100 text-gray-500 border-gray-200'],
    ];
@endphp

<div class="py-8 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ── Order-placed flash ──────────────────────────────────────────── --}}
        @if (session('order_placed'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-2xl text-sm font-medium"
                 role="status">
                <svg class="w-5 h-5 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                <span>{{ session('order_placed') }} Thank you — pay at the counter when it's ready.</span>
            </div>
        @endif

        {{-- ── Hero ────────────────────────────────────────────────────────── --}}
        <section class="relative overflow-hidden rounded-3xl bg-linear-to-br from-amber-500 via-orange-400 to-amber-400 px-6 py-8 sm:px-10 sm:py-12 shadow-lg shadow-amber-200/50">
            {{-- Decorative blurred orbs --}}
            <div class="pointer-events-none absolute -top-10 -right-10 h-44 w-44 rounded-full bg-white/20 blur-2xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-16 -left-8 h-52 w-52 rounded-full bg-orange-300/30 blur-3xl" aria-hidden="true"></div>
            {{-- Oversized fox watermark --}}
            <div class="pointer-events-none absolute right-4 sm:right-10 top-1/2 -translate-y-1/2 text-[8rem] sm:text-[11rem] leading-none opacity-20 select-none" aria-hidden="true">🦊</div>

            <div class="relative max-w-xl">
                <p class="text-sm font-medium text-white/80">{{ now()->format('l, j F') }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-white">
                    {{ $this->greeting }}, {{ auth()->user()->name }}! <span class="inline-block">🦊</span>
                </h1>
                <p class="mt-2 text-white/90 leading-relaxed">
                    Warm drinks, handcrafted treats, and a den full of foxes are waiting for you.
                    What are you in the mood for today?
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('menu.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 shadow-sm
                              transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-amber-900/10 active:translate-y-0
                              focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-amber-500">
                        <span aria-hidden="true">🍜</span> Browse the menu
                    </a>
                    <a href="{{ route('animals.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-5 py-2.5 text-sm font-semibold text-white ring-1 ring-inset ring-white/40
                              backdrop-blur-sm transition-all duration-200 hover:bg-white/25 hover:-translate-y-0.5 active:translate-y-0
                              focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-amber-500">
                        <span aria-hidden="true">🐾</span> Meet the foxes
                    </a>
                </div>
            </div>
        </section>

        {{-- ── Stat pills ───────────────────────────────────────────────────── --}}
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Orders --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white border border-gray-100 shadow-xs p-5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 leading-none">{{ $this->ordersCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ Str::plural('Order', $this->ordersCount) }} placed</p>
                </div>
            </div>

            {{-- Reviews --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white border border-gray-100 shadow-xs p-5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 leading-none">{{ $this->reviewsCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ Str::plural('Review', $this->reviewsCount) }} written</p>
                </div>
            </div>

            {{-- Member since --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white border border-gray-100 shadow-xs p-5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 leading-none">{{ $this->memberSince }}</p>
                    <p class="mt-1 text-sm text-gray-500">Member since</p>
                </div>
            </div>
        </section>

        {{-- ── Latest order + Fox spotlight ─────────────────────────────────── --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Latest order (spans 2) --}}
            <div class="lg:col-span-2 rounded-2xl bg-white border border-gray-100 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Your latest order</h2>
                    @if ($this->latestOrder)
                        @php $s = $statusStyles[$this->latestOrder->status->value]; @endphp
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full border {{ $s['chip'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $s['dot'] }}"></span>
                            {{ $s['label'] }}
                        </span>
                    @endif
                </div>

                @if ($this->latestOrder)
                    @php $order = $this->latestOrder; @endphp

                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-sm text-gray-500">
                        <span class="font-mono font-medium text-gray-700">#{{ $order->id }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ $order->created_at->diffForHumans() }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ $order->orderItems->sum('quantity') }} {{ Str::plural('item', $order->orderItems->sum('quantity')) }}</span>
                    </div>

                    {{-- Line items --}}
                    <ul class="mt-4 divide-y divide-gray-100">
                        @foreach ($order->orderItems as $line)
                            <li class="flex items-center justify-between py-2.5 text-sm">
                                <span class="text-gray-700">
                                    <span class="font-medium text-gray-500">{{ $line->quantity }}×</span>
                                    {{ $line->menuItem?->name ?? 'Removed item' }}
                                </span>
                                <span class="text-gray-500">Rp {{ number_format($line->price_cents * $line->quantity, 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                        <p class="text-sm text-gray-500">
                            Total <span class="ml-1 text-lg font-bold text-amber-700">Rp {{ number_format($order->total_cents, 0, ',', '.') }}</span>
                        </p>
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
                @else
                    {{-- Empty state: first visit --}}
                    <div class="flex flex-col items-center text-center py-8">
                        <div class="text-5xl mb-3" aria-hidden="true">🍵</div>
                        <p class="font-medium text-gray-700">No orders yet</p>
                        <p class="mt-1 text-sm text-gray-500 max-w-xs">Your first treat is just a few taps away. Browse the menu and find something you love.</p>
                        <a href="{{ route('menu.index') }}" wire:navigate
                           class="mt-4 inline-flex items-center gap-2 rounded-lg bg-linear-to-r from-amber-500 to-orange-400
                                  hover:from-amber-600 hover:to-orange-500 hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                                  active:translate-y-0 text-white text-sm font-semibold py-2 px-5 transition-all duration-200
                                  focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1">
                            Start your first order
                        </a>
                    </div>
                @endif
            </div>

            {{-- Fox spotlight --}}
            <div class="rounded-2xl bg-white border border-gray-100 shadow-xs overflow-hidden flex flex-col">
                <div class="flex items-center gap-2 px-5 pt-5 pb-3">
                    <span class="text-xs font-semibold uppercase tracking-wide text-amber-600">Fox of the day</span>
                    <span class="text-base" aria-hidden="true">✨</span>
                </div>

                @if ($this->spotlightFox)
                    @php $fox = $this->spotlightFox; @endphp
                    <a href="{{ route('animals.show', $fox) }}" wire:navigate
                       class="group block focus:outline-hidden">
                        <div class="relative h-44 overflow-hidden">
                            @if ($fox->image_path)
                                <img src="{{ $fox->imageUrl() }}" alt="{{ $fox->name }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                     loading="lazy"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                                <x-fox-placeholder class="h-44" style="display:none" />
                            @else
                                <x-fox-placeholder class="h-44 transition-transform duration-500 group-hover:scale-110" />
                            @endif
                            <div class="absolute inset-0 bg-linear-to-t from-black/50 to-transparent"></div>
                            <h3 class="absolute bottom-3 left-4 text-lg font-bold text-white drop-shadow">{{ $fox->name }}</h3>
                        </div>
                    </a>
                    <div class="p-5 pt-4 flex flex-col flex-1">
                        @if (! empty($fox->personality))
                            <div class="flex flex-wrap gap-1.5 mb-3">
                                @foreach (array_slice($fox->personality, 0, 3) as $trait)
                                    <span class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full">{{ $trait }}</span>
                                @endforeach
                            </div>
                        @endif
                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3">{{ $fox->description }}</p>
                        <a href="{{ route('animals.show', $fox) }}" wire:navigate
                           class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-amber-600 hover:text-amber-700 hover:gap-2 transition-all">
                            Get to know {{ $fox->name }} →
                        </a>
                    </div>
                @else
                    <div class="p-5 flex flex-col items-center text-center py-10 flex-1 justify-center">
                        <div class="text-4xl mb-2" aria-hidden="true">🦊</div>
                        <p class="text-sm text-gray-500">Our foxes are napping. Check back soon!</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- ── Popular items ────────────────────────────────────────────────── --}}
        @if ($this->popularItems->isNotEmpty())
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Loved by other guests</h2>
                    <a href="{{ route('menu.index') }}" wire:navigate
                       class="text-sm font-medium text-amber-600 hover:text-amber-700 hover:underline">See full menu →</a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($this->popularItems as $item)
                        @php
                            $avg = round($item->reviews->avg('rating') ?? 0, 1);
                            $reviewCount = $item->reviews->count();
                        @endphp
                        <a href="{{ route('menu.index') }}" wire:navigate wire:key="popular-{{ $item->id }}"
                           class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-xs overflow-hidden
                                  transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-amber-100/70 hover:border-amber-200
                                  focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400">
                            <div class="overflow-hidden">
                                @if ($item->image_path)
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}"
                                         class="h-28 w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                         loading="lazy"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                                    <x-menu-category-placeholder :category="$item->category->name ?? ''" class="h-28" style="display:none" />
                                @else
                                    <x-menu-category-placeholder :category="$item->category->name ?? ''" class="h-28 transition-transform duration-500 group-hover:scale-110" />
                                @endif
                            </div>
                            <div class="p-3">
                                <h3 class="text-sm font-semibold text-gray-900 leading-snug line-clamp-1 group-hover:text-amber-700 transition-colors">{{ $item->name }}</h3>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-amber-700">Rp {{ number_format($item->price_cents, 0, ',', '.') }}</span>
                                    @if ($reviewCount > 0)
                                        <span class="inline-flex items-center gap-0.5 text-xs text-gray-400">
                                            <svg viewBox="0 0 20 20" class="w-3.5 h-3.5 fill-current text-amber-400" aria-hidden="true">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            {{ $avg }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>
