<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public function openCreate(): void
    {
        $this->reset('editingId', 'name');
        $this->resetValidation();
        $this->dispatch('open-category-form');
    }

    private function ensureStaff(): void
    {
        abort_unless(auth()->user()?->is_staff, 403);
    }

    public function openEdit(int $id): void
    {
        $this->ensureStaff();

        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->resetValidation();
        $this->dispatch('open-category-form');
    }

    public function save(): void
    {
        $this->ensureStaff();
        $this->name = trim($this->name);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->editingId)],
        ]);

        Category::updateOrCreate(['id' => $this->editingId], ['name' => $validated['name']]);

        $this->dispatch('close-category-form');
    }

    public function delete(int $id): void
    {
        $this->ensureStaff();
        $this->resetValidation('delete');

        $category = Category::findOrFail($id);

        if ($category->menuItems()->exists()) {
            $this->addError('delete', "“{$category->name}” still has menu items — move or delete them first.");

            return;
        }

        $category->delete();
    }

    public function with(): array
    {
        return [
            'categories' => Category::withCount('menuItems')->orderBy('name')->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
            <p class="text-xs text-gray-400 mt-0.5">Organise the menu into sections customers can browse.</p>
        </div>
        <flux:button wire:click="openCreate" variant="primary" size="sm" icon="plus">Add category</flux:button>
    </div>
</x-slot>

@php
    $totalCategories = $categories->count();
    $totalItems = $categories->sum('menu_items_count');
    $emptyCategories = $categories->where('menu_items_count', 0)->count();

    // Combined tag + dot path, matching the sidebar's Categories glyph.
    $tagIcon = 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z';
    $listIcon = 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z';
    $inboxIcon = 'M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z';
@endphp

<div class="py-8 px-4 sm:px-6 lg:px-8"
     x-data="{ open: false }"
     x-on:open-category-form.window="open = true; $nextTick(() => $refs.nameInput?.focus())"
     x-on:close-category-form.window="open = false"
     x-on:keydown.escape.window="open = false">

    <div class="max-w-3xl">
        @error('delete')
            <div class="mb-5 flex items-start gap-3 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700" role="alert">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        @if ($categories->isEmpty())
            <div class="rounded-2xl bg-white border border-gray-100 shadow-xs text-center py-16 px-4">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tagIcon }}" />
                    </svg>
                </span>
                <p class="mt-4 text-lg text-gray-500">No categories yet.</p>
                <p class="mt-1 text-sm text-gray-400">Create your first category to start grouping menu items.</p>
                <flux:button wire:click="openCreate" variant="primary" size="sm" icon="plus" class="mt-5">Add the first category</flux:button>
            </div>
        @else
            {{-- Overview --}}
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-admin.stat-card label="Categories" :value="$totalCategories" sub="Menu sections" tint="bg-amber-50 text-amber-600" :icon="$tagIcon" />
                <x-admin.stat-card label="Menu items" :value="$totalItems" sub="Across all categories" tint="bg-orange-50 text-orange-600" :icon="$listIcon" />
                <x-admin.stat-card label="Empty" :value="$emptyCategories" sub="No items yet" tint="bg-gray-100 text-gray-500" :icon="$inboxIcon" />
            </div>

            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <ul class="divide-y divide-gray-50">
                    @foreach ($categories as $category)
                        <li wire:key="category-{{ $category->id }}" class="group flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-amber-50/40 transition-colors">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100/70">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tagIcon }}" />
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ $category->name }}</p>
                                    @if ($category->menu_items_count > 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-100 text-xs font-medium px-2 py-0.5 mt-1">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                            </svg>
                                            {{ $category->menu_items_count }} {{ Str::plural('item', $category->menu_items_count) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-400 border border-gray-200 text-xs font-medium px-2 py-0.5 mt-1">Empty</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1 sm:opacity-60 sm:group-hover:opacity-100 transition-opacity">
                                <button wire:click="openEdit({{ $category->id }})" type="button" title="Rename"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition">
                                    <span class="sr-only">Rename {{ $category->name }}</span>
                                    <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $category->id }})" wire:confirm="Delete this category?" type="button" title="Delete"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition">
                                    <span class="sr-only">Delete {{ $category->name }}</span>
                                    <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.16-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.04-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Create/Edit modal --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 bg-black/50 z-40" x-on:click="open = false"></div>
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center p-4" x-on:click="open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm my-16 p-6"
                 x-on:click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 role="dialog" aria-modal="true">
                <div class="flex items-center gap-3 mb-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tagIcon }}" />
                        </svg>
                    </span>
                    <h3 class="text-lg font-bold text-gray-900">{{ $editingId ? 'Rename category' : 'Add category' }}</h3>
                </div>
                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" type="text" placeholder="e.g. Hot Drinks" x-ref="nameInput" autocomplete="off" />
                        <flux:error name="name" />
                    </flux:field>
                    <div class="flex justify-end gap-2 pt-2">
                        <flux:button type="button" variant="ghost" x-on:click="open = false">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save' : 'Add' }}</span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
