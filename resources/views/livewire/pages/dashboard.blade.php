<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Animal;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    // Timestamps are stored in UTC; convert to the cafe's local timezone for display only.
    private function localNow(): Carbon
    {
        return now()->timezone(config('app.display_timezone'));
    }

    // ── Computed: greeting ───────────────────────────────────────────────────
    #[Computed]
    public function greeting(): string
    {
        $hour = $this->localNow()->hour;

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            $hour < 21 => 'Good evening',
            default => 'Still awake',
        };
    }

    // ── Computed: current local date (for the hero) ──────────────────────────
    #[Computed]
    public function currentDate(): string
    {
        return $this->localNow()->format('l, j F');
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
        // Rarely-changing read (changes once per local day) — cache it (CLAUDE.md §11).
        $local = $this->localNow();

        return Cache::remember(
            'dashboard:spotlight-fox:'.$local->toDateString(),
            $local->copy()->endOfDay(),
            function () use ($local) {
                $count = Animal::active()->count();

                if ($count === 0) {
                    return null;
                }

                // Day-of-year offset keeps the pick stable within a day but rotating.
                $offset = (int) $local->dayOfYear % $count;

                return Animal::active()->orderBy('id')->skip($offset)->first();
            }
        );
    }

    // ── Computed: popular items (by quantity sold, newest as fallback) ────────
    #[Computed]
    public function popularItems()
    {
        // Global, slow-changing read — cache with a short TTL (CLAUDE.md §11).
        return Cache::remember('dashboard:popular-items', now()->addMinutes(10), function () {
            // Cancelled orders shouldn't inflate popularity (matches ordersCount()).
            $rankedIds = OrderItem::query()
                ->whereHas('order', fn ($q) => $q->where('status', '!=', OrderStatus::Cancelled->value))
                ->selectRaw('menu_item_id, SUM(quantity) as sold')
                ->groupBy('menu_item_id')
                ->orderByDesc('sold')
                ->limit(8)
                ->pluck('menu_item_id');

            // No qualifying orders yet — show the freshest items instead.
            if ($rankedIds->isEmpty()) {
                return MenuItem::available()
                    ->with(['category', 'reviews'])
                    ->latest()
                    ->limit(4)
                    ->get();
            }

            $order = $rankedIds->flip();

            return MenuItem::available()
                ->with(['category', 'reviews'])
                ->whereIn('id', $rankedIds)
                ->get()
                ->sortBy(fn ($item) => $order[$item->id] ?? PHP_INT_MAX)
                ->take(4)
                ->values();
        });
    }

    // ── Computed: this user's favourite items (their most-ordered) ───────────
    #[Computed]
    public function favouriteItems()
    {
        $rankedIds = OrderItem::query()
            ->whereHas('order', fn ($q) => $q
                ->where('user_id', auth()->id())
                ->where('status', '!=', OrderStatus::Cancelled->value))
            ->selectRaw('menu_item_id, SUM(quantity) as qty')
            ->groupBy('menu_item_id')
            ->orderByDesc('qty')
            ->limit(8)
            ->pluck('menu_item_id');

        if ($rankedIds->isEmpty()) {
            return collect();
        }

        $order = $rankedIds->flip();

        return MenuItem::available()
            ->with(['category', 'reviews'])
            ->whereIn('id', $rankedIds)
            ->get()
            ->sortBy(fn ($item) => $order[$item->id] ?? PHP_INT_MAX)
            ->take(4)
            ->values();
    }

    // ── Action: add a single item to the cart (favourites quick-add) ─────────
    public function addToCart(int $menuItemId, CartService $cart): void
    {
        $cart->add($menuItemId);
        $this->dispatch('cart-updated');
    }

    // ── Action: re-add the last order to the cart ────────────────────────────
    public function orderAgain(CartService $cart): void
    {
        $order = $this->latestOrder;

        if (! $order) {
            return;
        }

        $result = $cart->addFromOrder($order);

        // Nothing could be re-added (every item is now unavailable) — tell the user
        // instead of leaving the button to spin with no result.
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
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
</x-slot>

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
            {{-- Oversized fox-face watermark (matches the app's fox motif) --}}
            <svg class="pointer-events-none absolute right-2 sm:right-8 top-1/2 -translate-y-1/2 h-44 w-44 sm:h-60 sm:w-60 text-white/15 select-none"
                 viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
                <polygon points="28,72 6,14 52,52"/>
                <polygon points="72,72 94,14 48,52"/>
                <circle cx="50" cy="74" r="27"/>
            </svg>

            <div class="relative max-w-xl">
                <p class="text-sm font-medium text-white/80">{{ $this->currentDate }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-white">
                    {{ $this->greeting }}, {{ auth()->user()->name }}
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
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                        </svg>
                        Browse the menu
                    </a>
                    <a href="{{ route('animals.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-5 py-2.5 text-sm font-semibold text-white ring-1 ring-inset ring-white/40
                              backdrop-blur-sm transition-all duration-200 hover:bg-white/25 hover:-translate-y-0.5 active:translate-y-0
                              focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-amber-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <ellipse cx="5.5" cy="11" rx="1.7" ry="2.3"/>
                            <ellipse cx="9.5" cy="7" rx="1.7" ry="2.4"/>
                            <ellipse cx="14.5" cy="7" rx="1.7" ry="2.4"/>
                            <ellipse cx="18.5" cy="11" rx="1.7" ry="2.3"/>
                            <path d="M12 11.5c-2.9 0-5.2 2.2-5.2 4.8 0 1.7 1.3 2.7 2.8 2.7.9 0 1.5-.4 2.4-.4s1.5.4 2.4.4c1.5 0 2.8-1 2.8-2.7 0-2.6-2.3-4.8-5.2-4.8Z"/>
                        </svg>
                        Meet the foxes
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
                    <div class="flex items-center gap-3">
                        <h2 class="font-semibold text-gray-900">Your latest order</h2>
                        @if ($this->latestOrder)
                            <x-order-status-badge :status="$this->latestOrder->status" />
                        @endif
                    </div>
                    <a href="{{ route('orders.index') }}" wire:navigate
                       class="text-sm font-medium text-amber-600 hover:text-amber-700 hover:underline">View all orders →</a>
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
                                <span class="text-gray-500"><x-rupiah :amount="$line->price_cents * $line->quantity" /></span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                        <p class="text-sm text-gray-500">
                            Total <span class="ml-1 text-lg font-bold text-amber-700"><x-rupiah :amount="$order->total_cents" /></span>
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
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 mb-3">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                            </svg>
                        </div>
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
                    <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
                    </svg>
                    <span class="text-xs font-semibold uppercase tracking-wide text-amber-600">Fox of the day</span>
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
                        <svg class="h-12 w-12 text-orange-200 mb-3" viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
                            <polygon points="28,72 6,14 52,52"/>
                            <polygon points="72,72 94,14 48,52"/>
                            <circle cx="50" cy="74" r="27"/>
                            <circle cx="38" cy="70" r="3.5" fill="white"/>
                            <circle cx="62" cy="70" r="3.5" fill="white"/>
                        </svg>
                        <p class="text-sm text-gray-500">Our foxes are napping. Check back soon!</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- ── Your favourites (this user's most-ordered) ──────────────────── --}}
        @if ($this->favouriteItems->isNotEmpty())
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Your favourites</h2>
                    <span class="text-sm text-gray-400">The things you order most</span>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($this->favouriteItems as $item)
                        <div wire:key="favourite-{{ $item->id }}"
                             class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-xs overflow-hidden
                                    transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-amber-100/70 hover:border-amber-200">
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
                            <div class="p-3 flex flex-col flex-1">
                                <h3 class="text-sm font-semibold text-gray-900 leading-snug line-clamp-1">{{ $item->name }}</h3>
                                <span class="mt-1 text-sm font-semibold text-amber-700"><x-rupiah :amount="$item->price_cents" /></span>
                                <button
                                    wire:click="addToCart({{ $item->id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-60 cursor-wait"
                                    wire:target="addToCart({{ $item->id }})"
                                    type="button"
                                    class="mt-3 w-full bg-linear-to-r from-amber-500 to-orange-400
                                           hover:from-amber-600 hover:to-orange-500 hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                                           active:translate-y-0 active:scale-[0.97] text-white text-xs font-semibold py-1.5 px-3 rounded-lg
                                           transition-all duration-200 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                                           disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="addToCart({{ $item->id }})">Add to cart</span>
                                    <span wire:loading wire:target="addToCart({{ $item->id }})">Adding…</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

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
                                    <span class="text-sm font-semibold text-amber-700"><x-rupiah :amount="$item->price_cents" /></span>
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

        {{-- Unavailable-items toast (shown when "Order again" can re-add nothing) --}}
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

        {{-- Added-to-cart toast (favourites quick-add) --}}
        <div
            x-data="{ show: false }"
            x-on:cart-updated.window="show = true; setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition
            class="fixed bottom-6 right-6 bg-gray-900 text-white text-sm px-4 py-2.5 rounded-xl shadow-lg z-50 flex items-center gap-2"
            role="status"
            aria-live="polite"
            style="display:none"
        >
            <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Added to cart
        </div>

    </div>
</div>
