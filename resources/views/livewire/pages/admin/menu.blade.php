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

    private function ensureStaff(): void
    {
        abort_unless(auth()->user()?->is_staff, 403);
    }

    public function openEdit(int $id): void
    {
        $this->ensureStaff();

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
        $this->ensureStaff();
        $this->name = trim($this->name);

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
        $this->ensureStaff();

        $item = MenuItem::findOrFail($id);
        $item->update(['is_available' => ! $item->is_available]);
    }

    public function delete(int $id): void
    {
        $this->ensureStaff();
        $this->resetValidation('delete');

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
            'totalCount' => MenuItem::count(),
            'availableCount' => MenuItem::where('is_available', true)->count(),
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

        {{-- Overview --}}
        @php $hiddenCount = $totalCount - $availableCount; @endphp
        <div class="mb-5 grid grid-cols-2 sm:grid-cols-4 rounded-2xl bg-white border border-gray-100 shadow-xs divide-y divide-gray-100 sm:divide-y-0 sm:divide-x">
            <div class="flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Items</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xl font-bold text-gray-900 leading-none">{{ $availableCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Available</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </span>
                <div>
                    <p class="text-xl font-bold text-gray-900 leading-none">{{ $hiddenCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Hidden</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xl font-bold text-gray-900 leading-none">{{ $categories->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ Str::plural('Category', $categories->count()) }}</p>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-6 rounded-2xl bg-white border border-gray-100 shadow-xs p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
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
            @if ($totalCount > 0)
                <p class="mt-3 text-xs text-gray-400">Showing {{ $items->total() }} of {{ $totalCount }} {{ Str::plural('item', $totalCount) }}</p>
            @endif
        </div>

        @if ($items->isEmpty())
            <div class="rounded-2xl bg-white border border-gray-100 shadow-xs text-center py-16 px-4">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </span>
                @if ($search || $categoryFilter || $availability)
                    <p class="mt-4 text-lg text-gray-500">No items match your filters.</p>
                @else
                    <p class="mt-4 text-lg text-gray-500">No menu items yet.</p>
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
                                             class="h-12 w-12 rounded-xl object-cover border border-gray-100" />
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 border border-gray-200 text-gray-300" aria-hidden="true">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                    @if ($item->description)
                                        <p class="text-xs text-gray-400 line-clamp-1 max-w-xs">{{ $item->description }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($item->category)
                                        <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-700 border border-amber-100 text-xs font-medium px-2.5 py-0.5">{{ $item->category->name }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-amber-700 whitespace-nowrap"><x-rupiah :amount="$item->price_cents" /></td>
                                <td class="px-4 py-3">
                                    @if ($item->is_available)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 text-green-700 border border-green-100 text-xs font-medium px-2.5 py-0.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Available
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 text-gray-500 border border-gray-200 text-xs font-medium px-2.5 py-0.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Hidden
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="toggleAvailable({{ $item->id }})" wire:loading.attr="disabled" wire:target="toggleAvailable({{ $item->id }})"
                                                type="button" title="{{ $item->is_available ? 'Hide' : 'Show' }}"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition disabled:opacity-50 disabled:cursor-wait">
                                            <span class="sr-only">{{ $item->is_available ? 'Hide' : 'Show' }}</span>
                                            @if ($item->is_available)
                                                <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                </svg>
                                            @else
                                                <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            @endif
                                        </button>
                                        <button wire:click="openEdit({{ $item->id }})" type="button" title="Edit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition">
                                            <span class="sr-only">Edit</span>
                                            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this item?" type="button" title="Delete"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition">
                                            <span class="sr-only">Delete</span>
                                            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.16-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.04-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
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
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save" :disabled="$categories->isEmpty()">
                            {{ $editingId ? 'Save changes' : 'Add item' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
