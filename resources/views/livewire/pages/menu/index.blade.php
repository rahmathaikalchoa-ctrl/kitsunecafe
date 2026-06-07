<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Review;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    // ── Filter state ─────────────────────────────────────────────────────────
    public ?int $categoryId = null;

    // ── Detail modal state ───────────────────────────────────────────────────
    public ?int $selectedItemId = null;

    // ── Review form state ────────────────────────────────────────────────────
    public int $reviewRating = 5;

    public string $reviewComment = '';

    public bool $editingReview = false;

    // ── Computed: menu grid ──────────────────────────────────────────────────
    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function menuItems()
    {
        return MenuItem::available()
            ->with(['category', 'reviews'])
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function totalItems(): int
    {
        return MenuItem::available()->count();
    }

    // ── Computed: detail modal ────────────────────────────────────────────────
    #[Computed]
    public function selectedItem(): ?MenuItem
    {
        if (! $this->selectedItemId) {
            return null;
        }

        return MenuItem::with(['category', 'reviews.user'])->find($this->selectedItemId);
    }

    #[Computed]
    public function userReview(): ?Review
    {
        if (! $this->selectedItemId || ! auth()->check()) {
            return null;
        }

        return Review::where('user_id', auth()->id())
            ->where('menu_item_id', $this->selectedItemId)
            ->first();
    }

    #[Computed]
    public function userHasOrdered(): bool
    {
        if (! $this->selectedItemId || ! auth()->check()) {
            return false;
        }

        return OrderItem::whereHas('order', fn ($q) => $q
            ->where('user_id', auth()->id())
            ->whereNotIn('status', [OrderStatus::Cancelled->value])
        )->where('menu_item_id', $this->selectedItemId)->exists();
    }

    // ── Actions: cart ─────────────────────────────────────────────────────────
    public function addToCart(int $menuItemId, CartService $cart): void
    {
        if (! auth()->check()) {
            $this->dispatch('login-required');

            return;
        }

        $cart->add($menuItemId);

        $this->dispatch('cart-updated');
    }

    // ── Actions: detail modal ─────────────────────────────────────────────────
    public function openItem(int $id): void
    {
        $this->selectedItemId = $id;
        $this->editingReview = false;
        $this->reviewRating = 5;
        $this->reviewComment = '';
        unset($this->selectedItem, $this->userReview, $this->userHasOrdered);

        $this->dispatch('open-menu-detail-modal');
    }

    public function closeItem(): void
    {
        $this->selectedItemId = null;
        $this->editingReview = false;
        $this->reviewRating = 5;
        $this->reviewComment = '';
        unset($this->selectedItem, $this->userReview, $this->userHasOrdered);
    }

    public function startEditReview(): void
    {
        if ($this->userReview) {
            $this->reviewRating = $this->userReview->rating;
            $this->reviewComment = $this->userReview->comment ?? '';
            $this->editingReview = true;
        }
    }

    // ── Actions: reviews ──────────────────────────────────────────────────────
    public function submitReview(): void
    {
        if (! auth()->check() || ! $this->userHasOrdered) {
            return;
        }

        $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewComment' => ['nullable', 'string', 'max:500'],
        ]);

        Review::updateOrCreate(
            ['user_id' => auth()->id(), 'menu_item_id' => $this->selectedItemId],
            ['rating' => $this->reviewRating, 'comment' => $this->reviewComment ?: null]
        );

        $this->editingReview = false;
        $this->reviewComment = '';
        unset($this->selectedItem, $this->userReview);
    }

    public function deleteReview(): void
    {
        if (! auth()->check()) {
            return;
        }

        $review = Review::where('user_id', auth()->id())
            ->where('menu_item_id', $this->selectedItemId)
            ->first();

        if (! $review) {
            return;
        }

        $review->delete();

        $this->editingReview = false;
        $this->reviewRating = 5;
        $this->reviewComment = '';
        unset($this->selectedItem, $this->userReview);
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Menu</h2>
</x-slot>

<div class="py-10"
     x-data="{ modalOpen: false }"
     x-effect="document.body.style.overflow = modalOpen ? 'hidden' : ''; if (modalOpen) $nextTick(() => $refs.dialog?.focus())"
     x-on:open-menu-detail-modal.window="modalOpen = true"
     x-on:keydown.escape.window="modalOpen = false; $wire.closeItem()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <p class="text-gray-600 mb-2 max-w-2xl">
            From hearty ramen and soft tamago sandwiches to matcha lattes and fox-shaped waffles —
            every dish and drink at Kitsune Animal Cafe is handcrafted with care. Pick your favourites
            and add them to your cart.
        </p>
        <p class="text-sm font-medium text-amber-600 mb-8">{{ $this->totalItems }} {{ Str::plural('item', $this->totalItems) }} on the menu</p>

        {{-- Category filter --}}
        <div class="flex flex-wrap gap-2 mb-8">
            <button
                wire:click="$set('categoryId', null)"
                wire:loading.class="opacity-50 cursor-wait"
                wire:target="$set('categoryId', null)"
                @class([
                    'px-4 py-1.5 rounded-full text-sm font-medium transition',
                    'bg-amber-500 text-white' => $categoryId === null,
                    'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' => $categoryId !== null,
                ])>
                All
            </button>
            @foreach ($this->categories as $category)
                <button
                    wire:click="$set('categoryId', {{ $category->id }})"
                    wire:loading.class="opacity-50 cursor-wait"
                    wire:target="$set('categoryId', {{ $category->id }})"
                    @class([
                        'px-4 py-1.5 rounded-full text-sm font-medium transition',
                        'bg-amber-500 text-white' => $categoryId === $category->id,
                        'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' => $categoryId !== $category->id,
                    ])>
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        {{-- Menu grid --}}
        @if ($this->menuItems->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <p class="text-lg">No items in this category yet.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($this->menuItems as $item)
                    <x-menu-item-card :item="$item" wire:key="menu-item-{{ $item->id }}" />
                @endforeach
            </div>
        @endif

        {{-- Cart toast --}}
        <div
            x-data="{ show: false }"
            x-on:cart-updated.window="show = true; setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition
            class="fixed bottom-6 right-6 bg-gray-900 text-white text-sm px-4 py-2.5 rounded-xl shadow-lg z-50 flex items-center gap-2"
            role="status"
            aria-live="polite"
        >
            <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Added to cart
        </div>

        {{-- Login-required toast (guest adds to cart) --}}
        <div
            x-data="{ show: false }"
            x-on:login-required.window="show = true; setTimeout(() => { window.location = '{{ route('login') }}'; }, 1800)"
            x-show="show"
            x-transition
            class="fixed bottom-6 right-6 bg-amber-600 text-white text-sm px-4 py-2.5 rounded-xl shadow-lg z-50 flex items-center gap-2"
            role="status"
            aria-live="polite"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
            </svg>
            Log in to add items to your cart
        </div>

        {{-- ── Backdrop ──────────────────────────────────────────────────────── --}}
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-on:click="modalOpen = false; $wire.closeItem()"
             class="fixed inset-0 bg-black/50 z-40"
             style="display:none">
        </div>

        {{-- ── Detail modal panel ────────────────────────────────────────────── --}}
        <div x-show="modalOpen"
             x-on:click="modalOpen = false; $wire.closeItem()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
             style="display:none">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl relative my-auto focus:outline-none"
                 x-ref="dialog"
                 tabindex="-1"
                 x-on:click.stop
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="menu-item-modal-title">

                {{-- Close button --}}
                <button type="button"
                        aria-label="Close"
                        x-on:click="modalOpen = false; $wire.closeItem()"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition z-10">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="p-6">
                    @if ($this->selectedItem)
                        @php
                            $item = $this->selectedItem;
                            $avg = round($item->reviews->avg('rating') ?? 0, 1);
                            $reviewCount = $item->reviews->count();
                        @endphp

                        {{-- Top section --}}
                        <div class="flex gap-5 pr-6">
                            @if ($item->image_path)
                                <img
                                    src="{{ $item->imageUrl() }}"
                                    alt="{{ $item->name }}"
                                    class="w-28 h-28 shrink-0 rounded-xl object-cover"
                                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                                />
                                <x-menu-category-placeholder
                                    :category="$item->category->name ?? ''"
                                    class="w-28 h-28 shrink-0 rounded-xl"
                                    style="display:none"
                                />
                            @else
                                <x-menu-category-placeholder
                                    :category="$item->category->name ?? ''"
                                    class="w-28 h-28 shrink-0 rounded-xl"
                                />
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h2 id="menu-item-modal-title" class="text-xl font-bold text-gray-900">{{ $item->name }}</h2>
                                    <span class="shrink-0 text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                                        {{ $item->category->name }}
                                    </span>
                                </div>
                                <p class="mt-1 text-2xl font-bold text-amber-700">
                                    Rp {{ number_format($item->price_cents, 0, ',', '.') }}
                                </p>
                                @if ($item->description)
                                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $item->description }}</p>
                                @endif
                                <div class="mt-4">
                                    @if ($item->is_available)
                                        <button
                                            wire:click="addToCart({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-60 cursor-wait"
                                            wire:target="addToCart({{ $item->id }})"
                                            type="button"
                                            class="bg-gradient-to-r from-amber-500 to-orange-400
                                                   hover:from-amber-600 hover:to-orange-500
                                                   hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                                                   active:translate-y-0 active:shadow-sm active:scale-[0.97]
                                                   text-white text-sm font-semibold py-2 px-5 rounded-lg
                                                   transition-all duration-200
                                                   focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none"
                                        >
                                            <span wire:loading.remove wire:target="addToCart({{ $item->id }})">Add to Cart</span>
                                            <span wire:loading wire:target="addToCart({{ $item->id }})" class="flex items-center gap-1.5">
                                                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                                </svg>
                                                Adding...
                                            </span>
                                        </button>
                                    @else
                                        <button disabled type="button"
                                            class="bg-gray-100 text-gray-400 text-sm font-semibold py-2 px-5 rounded-lg cursor-not-allowed opacity-60">
                                            Not Available
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 my-5"></div>

                        {{-- Rating summary --}}
                        <div class="flex items-center gap-3 mb-5" @if ($reviewCount > 0) aria-label="Average rating {{ $avg }} out of 5" @endif>
                            <div class="flex" aria-hidden="true">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg viewBox="0 0 20 20" aria-hidden="true" @class(['w-5 h-5 fill-current', $i <= $avg ? 'text-amber-400' : 'text-gray-200'])>
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            @if ($reviewCount > 0)
                                <span class="font-semibold text-gray-800">{{ $avg }}</span>
                                <span class="text-sm text-gray-400">{{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</span>
                            @else
                                <span class="text-sm text-gray-400">No reviews yet</span>
                            @endif
                        </div>

                        {{-- Reviews list --}}
                        @if ($reviewCount > 0)
                            <div class="space-y-4 mb-5 max-h-48 overflow-y-auto pr-1">
                                @foreach ($item->reviews->sortByDesc('created_at') as $review)
                                    <div class="flex gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0 text-xs font-semibold text-gray-500 uppercase">
                                            {{ substr($review->user->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-medium text-gray-800">{{ $review->user->name }}</span>
                                                    <div class="flex" aria-label="Rated {{ $review->rating }} out of 5">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <svg viewBox="0 0 20 20" aria-hidden="true" @class(['w-3 h-3 fill-current', $i <= $review->rating ? 'text-amber-400' : 'text-gray-200'])>
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                            </svg>
                                                        @endfor
                                                    </div>
                                                </div>
                                                @auth
                                                    @if ($review->user_id === auth()->id())
                                                        <div class="flex gap-2">
                                                            <button wire:click="startEditReview"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="startEditReview"
                                                                    class="text-xs text-gray-400 hover:text-amber-600 transition">Edit</button>
                                                            <button wire:click="deleteReview"
                                                                    wire:loading.attr="disabled"
                                                                    wire:loading.class="opacity-50"
                                                                    wire:target="deleteReview"
                                                                    wire:confirm="Delete your review?"
                                                                    class="text-xs text-gray-400 hover:text-red-500 transition">Delete</button>
                                                        </div>
                                                    @endif
                                                @endauth
                                            </div>
                                            @if ($review->comment)
                                                <p class="text-sm text-gray-600 mt-0.5">{{ $review->comment }}</p>
                                            @endif
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Review form --}}
                        <div class="border-t border-gray-100 pt-4">
                            @auth
                                @if ($this->userHasOrdered)
                                    @if ($this->userReview && ! $editingReview)
                                        <p class="text-sm text-gray-500">
                                            You reviewed this item.
                                            <button wire:click="startEditReview" class="text-amber-600 hover:underline ml-1">Edit your review</button>
                                        </p>
                                    @else
                                        <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                            {{ $editingReview ? 'Edit your review' : 'Leave a review' }}
                                        </h4>

                                        <form wire:submit="submitReview" class="space-y-3">
                                            <div>
                                                <label class="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Rating</label>
                                                <div class="flex gap-1">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <button type="button" wire:click="$set('reviewRating', {{ $i }})"
                                                                aria-label="Rate {{ $i }} {{ $i === 1 ? 'star' : 'stars' }}"
                                                                class="focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1 rounded transition-transform hover:scale-110">
                                                            <svg viewBox="0 0 20 20" aria-hidden="true" @class(['w-7 h-7 fill-current transition', $i <= $reviewRating ? 'text-amber-400' : 'text-gray-200'])>
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                            </svg>
                                                        </button>
                                                    @endfor
                                                    <span class="ml-2 text-sm text-gray-500 self-center">{{ $reviewRating }}/5</span>
                                                </div>
                                                <flux:error name="reviewRating" />
                                            </div>

                                            <flux:field>
                                                <flux:label>Comment <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                                                <flux:textarea wire:model="reviewComment" rows="2" placeholder="Share your experience..." />
                                                <flux:error name="reviewComment" />
                                            </flux:field>

                                            <div class="flex gap-2">
                                                <flux:button
                                                    type="submit"
                                                    variant="primary"
                                                    size="sm"
                                                    wire:loading.attr="disabled"
                                                    wire:loading.class="opacity-50"
                                                    wire:target="submitReview"
                                                >
                                                    {{ $editingReview ? 'Save Changes' : 'Submit Review' }}
                                                </flux:button>
                                                @if ($editingReview)
                                                    <flux:button type="button" wire:click="$set('editingReview', false)" size="sm" variant="ghost">
                                                        Cancel
                                                    </flux:button>
                                                @endif
                                            </div>
                                        </form>
                                    @endif
                                @else
                                    <p class="text-sm text-gray-400">Order this item to leave a review.</p>
                                @endif
                            @else
                                <p class="text-sm text-gray-400">
                                    <a href="{{ route('login') }}" wire:navigate class="text-amber-600 hover:underline">Log in</a>
                                    to leave a review.
                                </p>
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
