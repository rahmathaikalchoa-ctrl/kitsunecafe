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

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->editingId)],
        ]);

        Category::updateOrCreate(['id' => $this->editingId], ['name' => $validated['name']]);

        $this->dispatch('close-category-form');
    }

    public function delete(int $id): void
    {
        $this->ensureStaff();

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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
        <flux:button wire:click="openCreate" variant="primary" size="sm">Add category</flux:button>
    </div>
</x-slot>

<div class="py-8 px-4 sm:px-6 lg:px-8"
     x-data="{ open: false }"
     x-on:open-category-form.window="open = true"
     x-on:close-category-form.window="open = false"
     x-on:keydown.escape.window="open = false">

    <div class="max-w-2xl">
        @error('delete')
            <div class="mb-4 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror

        @if ($categories->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-lg">No categories yet.</p>
                <button wire:click="openCreate" class="mt-3 text-sm font-medium text-amber-600 hover:underline">Add the first category</button>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <ul class="divide-y divide-gray-50">
                    @foreach ($categories as $category)
                        <li wire:key="category-{{ $category->id }}" class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-amber-50/40 transition-colors">
                            <div>
                                <p class="font-medium text-gray-900">{{ $category->name }}</p>
                                <p class="text-xs text-gray-400">{{ $category->menu_items_count }} {{ Str::plural('item', $category->menu_items_count) }}</p>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <button wire:click="openEdit({{ $category->id }})" class="font-medium text-amber-600 hover:underline">Rename</button>
                                <button wire:click="delete({{ $category->id }})" wire:confirm="Delete this category?" class="font-medium text-gray-400 hover:text-red-500 transition">Delete</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Create/Edit modal --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 bg-black/50 z-40" x-on:click="open = false"></div>
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center p-4" x-on:click="open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm my-16 p-6" x-on:click.stop>
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $editingId ? 'Rename category' : 'Add category' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" type="text" />
                        <flux:error name="name" />
                    </flux:field>
                    <div class="flex justify-end gap-2 pt-2">
                        <flux:button type="button" variant="ghost" x-on:click="open = false">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                            {{ $editingId ? 'Save' : 'Add' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
