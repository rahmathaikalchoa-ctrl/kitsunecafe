<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?int $categoryId = null;

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function menuItems()
    {
        return MenuItem::available()
            ->with('category')
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->orderBy('name')
            ->get();
    }

    public function addToCart(int $menuItemId, CartService $cart): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $cart->add($menuItemId);

        $this->dispatch('cart-updated');
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Menu</h2>
</x-slot>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Category filter --}}
        <div class="flex flex-wrap gap-2 mb-8">
            <button
                wire:click="$set('categoryId', null)"
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
                    <x-menu-item-card :item="$item" />
                @endforeach
            </div>
        @endif

        {{-- Cart toast notification --}}
        <div
            x-data="{ show: false }"
            x-on:cart-updated.window="show = true; setTimeout(() => show = false, 2000)"
            x-show="show"
            x-transition
            class="fixed bottom-6 right-6 bg-gray-900 text-white text-sm px-4 py-2 rounded-xl shadow-lg"
        >
            Added to cart ✓
        </div>
    </div>
</div>
