<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\MenuItem;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';

    public ?int $categoryId = null;

    public ?int $priceCents = null;

    public string $description = '';

    public bool $isAvailable = true;

    public string $imagePath = '';

    public function openCreate(): void
    {
        $this->reset('editingId', 'name', 'categoryId', 'priceCents', 'description', 'imagePath');
        $this->isAvailable = true;
        $this->resetValidation();
        $this->dispatch('open-menu-form');
    }

    public function openEdit(int $id): void
    {
        $item = MenuItem::findOrFail($id);

        $this->editingId = $item->id;
        $this->name = $item->name;
        $this->categoryId = $item->category_id;
        $this->priceCents = $item->price_cents;
        $this->description = $item->description ?? '';
        $this->isAvailable = $item->is_available;
        $this->imagePath = $item->image_path ?? '';
        $this->resetValidation();

        $this->dispatch('open-menu-form');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'priceCents' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'imagePath' => ['nullable', 'string', 'max:255'],
        ]);

        MenuItem::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $validated['name'],
                'category_id' => $validated['categoryId'],
                'price_cents' => $validated['priceCents'],
                'description' => $this->description ?: null,
                'is_available' => $this->isAvailable,
                'image_path' => $this->imagePath ?: null,
            ]
        );

        $this->dispatch('close-menu-form');
    }

    public function toggleAvailable(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_available' => ! $item->is_available]);
    }

    public function delete(int $id): void
    {
        $item = MenuItem::findOrFail($id);

        // Order history references menu items (restrictOnDelete) — don't orphan it.
        if ($item->orderItems()->exists()) {
            $this->addError('delete', "“{$item->name}” has orders — mark it unavailable instead of deleting.");

            return;
        }

        $item->delete();
    }

    public function with(): array
    {
        return [
            'items' => MenuItem::with('category')->orderBy('name')->paginate(15),
            'categories' => Category::orderBy('name')->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Menu</h2>
        <flux:button wire:click="openCreate" variant="primary" size="sm">Add item</flux:button>
    </div>
</x-slot>

<div class="py-8 px-4 sm:px-6 lg:px-8"
     x-data="{ open: false }"
     x-on:open-menu-form.window="open = true"
     x-on:close-menu-form.window="open = false"
     x-on:keydown.escape.window="open = false">

    <div class="max-w-4xl">
        @error('delete')
            <div class="mb-4 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror

        @if ($items->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-lg">No menu items yet.</p>
                <button wire:click="openCreate" class="mt-3 text-sm font-medium text-amber-600 hover:underline">Add the first item</button>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Item</th>
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold text-right">Price</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($items as $item)
                            <tr wire:key="menu-{{ $item->id }}" class="hover:bg-amber-50/40 transition-colors">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $item->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-gray-700"><x-rupiah :amount="$item->price_cents" /></td>
                                <td class="px-4 py-3">
                                    @if ($item->is_available)
                                        <span class="inline-flex items-center rounded-full bg-green-50 text-green-700 border border-green-100 text-xs font-medium px-2.5 py-0.5">Available</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-500 border border-gray-200 text-xs font-medium px-2.5 py-0.5">Hidden</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3 text-sm">
                                        <button wire:click="toggleAvailable({{ $item->id }})" class="font-medium text-gray-500 hover:text-amber-600 transition">
                                            {{ $item->is_available ? 'Hide' : 'Show' }}
                                        </button>
                                        <button wire:click="openEdit({{ $item->id }})" class="font-medium text-amber-600 hover:underline">Edit</button>
                                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this item?" class="font-medium text-gray-400 hover:text-red-500 transition">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $items->links() }}</div>
        @endif

        {{-- Create/Edit modal --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 bg-black/50 z-40" x-on:click="open = false"></div>
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto" x-on:click="open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg my-10 p-6" x-on:click.stop>
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $editingId ? 'Edit item' : 'Add item' }}</h3>

                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name" type="text" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Category</flux:label>
                        <flux:select wire:model="categoryId" placeholder="Choose a category…">
                            @foreach ($categories as $category)
                                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="categoryId" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Price (Rp)</flux:label>
                        <flux:input wire:model="priceCents" type="number" min="0" step="1000" />
                        <flux:error name="priceCents" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                        <flux:textarea wire:model="description" rows="3" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Image URL or filename <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                        <flux:input wire:model="imagePath" type="text" placeholder="https://… or images/menu/file.jpg" />
                        <flux:error name="imagePath" />
                    </flux:field>

                    <flux:checkbox wire:model="isAvailable" label="Available to order" />

                    <div class="flex justify-end gap-2 pt-2">
                        <flux:button type="button" variant="ghost" x-on:click="open = false">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                            {{ $editingId ? 'Save changes' : 'Add item' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
