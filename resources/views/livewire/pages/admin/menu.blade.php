<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\MenuItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $categoryFilter = null;

    #[Url]
    public ?string $availability = null; // 'available' | 'hidden'

    public ?int $editingId = null;

    public string $name = '';

    public ?int $categoryId = null;

    public ?int $priceCents = null;

    public string $description = '';

    public bool $isAvailable = true;

    public string $imagePath = '';

    #[Computed]
    public function previewUrl(): ?string
    {
        // Resolve the typed path through the model's helper so the http-vs-asset
        // logic stays in one place (App\Models\MenuItem::imageUrl()).
        return $this->imagePath
            ? (new MenuItem(['image_path' => $this->imagePath]))->imageUrl()
            : null;
    }

    public function mount(): void
    {
        // Ignore a hand-typed/stale ?availability that isn't a real value.
        if (! in_array($this->availability, ['available', 'hidden'], true)) {
            $this->availability = null;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAvailability(): void
    {
        $this->resetPage();
    }

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

    protected function validationAttributes(): array
    {
        return [
            'categoryId' => 'category',
            'priceCents' => 'price',
            'imagePath' => 'image',
        ];
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
            'items' => MenuItem::with('category')
                ->when(trim($this->search), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
                ->when($this->categoryFilter, fn ($q, $id) => $q->where('category_id', $id))
                ->when($this->availability === 'available', fn ($q) => $q->where('is_available', true))
                ->when($this->availability === 'hidden', fn ($q) => $q->where('is_available', false))
                ->orderBy('name')
                ->paginate(15),
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

        {{-- Filters --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:field class="flex-1">
                <flux:label class="sr-only">Search menu items</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" type="search"
                    placeholder="Search items…" icon="magnifying-glass" clearable />
            </flux:field>
            <flux:field class="sm:w-48">
                <flux:label class="sr-only">Filter by category</flux:label>
                <flux:select wire:model.live="categoryFilter" placeholder="All categories">
                    @foreach ($categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field class="sm:w-40">
                <flux:label class="sr-only">Filter by availability</flux:label>
                <flux:select wire:model.live="availability" placeholder="All statuses">
                    <flux:select.option value="available">Available</flux:select.option>
                    <flux:select.option value="hidden">Hidden</flux:select.option>
                </flux:select>
            </flux:field>
        </div>

        @if ($items->isEmpty())
            <div class="text-center py-16 text-gray-400">
                @if ($search || $categoryFilter || $availability)
                    <p class="text-lg">No items match your filters.</p>
                @else
                    <p class="text-lg">No menu items yet.</p>
                    <button wire:click="openCreate" class="mt-3 text-sm font-medium text-amber-600 hover:underline">Add the first item</button>
                @endif
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3"><span class="sr-only">Image</span></th>
                            <th class="px-4 py-3 font-semibold">Item</th>
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold text-right">Price</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($items as $item)
                            <tr wire:key="menu-{{ $item->id }}" class="hover:bg-amber-50/40 transition-colors">
                                <td class="px-5 py-3">
                                    @if ($item->image_path)
                                        <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" loading="lazy"
                                             class="h-10 w-10 rounded-lg object-cover border border-gray-100" />
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200" aria-hidden="true"></div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $item->name }}</td>
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

                    @if ($categories->isEmpty())
                        <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-3 text-sm text-amber-700">
                            Add a category first —
                            <a href="{{ route('admin.categories') }}" wire:navigate class="font-medium underline">go to Categories</a>.
                        </div>
                    @else
                        <flux:field>
                            <flux:label>Category</flux:label>
                            <flux:select wire:model="categoryId" placeholder="Choose a category…">
                                @foreach ($categories as $category)
                                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="categoryId" />
                        </flux:field>
                    @endif

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
                        <flux:input wire:model.live.debounce.500ms="imagePath" type="text" placeholder="https://… or file.jpg" />
                        <flux:error name="imagePath" />
                        @if ($this->previewUrl)
                            <img src="{{ $this->previewUrl }}" alt="Preview"
                                 class="mt-2 h-24 w-24 rounded-xl object-cover border border-gray-100" />
                        @endif
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
