<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-gray-900 leading-tight">
                {{ __('Your Profile') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Manage your account details, password, and security.') }}</p>
        </div>
    </x-slot>

    @php
        $user = auth()->user();
        // Initials for the avatar (first letter of up to two name words).
        $initials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->take(2)
            ->implode('');
        $ordersCount = $user->orders()->count();
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Identity card (sticky on desktop) --}}
                <aside class="lg:col-span-1">
                    <div class="lg:sticky lg:top-8 bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
                        <div class="h-16 bg-linear-to-r from-amber-500 to-orange-400"></div>
                        <div class="px-6 pb-6">
                            <div class="-mt-10 mb-4">
                                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-linear-to-br from-amber-500 to-orange-400 text-2xl font-bold text-white shadow-sm ring-4 ring-white">
                                    {{ $initials ?: '?' }}
                                </div>
                            </div>

            
                            <h3 class="text-lg font-bold text-gray-900 leading-tight"
                                x-data="{{ json_encode(['name' => $user->name]) }}"
                                x-text="name"
                                x-on:profile-updated.window="name = $event.detail.name">{{ $user->name }}</h3>
                            <p class="mt-0.5 text-sm text-gray-500 break-all">{{ $user->email }}</p>

                            <dl class="mt-5 space-y-3 border-t border-amber-100 pt-5 text-sm">
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500">{{ __('Member since') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $user->created_at?->format('M Y') ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500">{{ __('Orders') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $ordersCount }}</dd>
                                </div>
                            </dl>

                            <a href="{{ route('menu.index') }}" wire:navigate
                               class="mt-5 inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-amber-200 px-4 py-2 text-sm font-medium text-amber-700 transition-colors hover:bg-amber-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />
                                </svg>
                                {{ __('Browse the menu') }}
                            </a>
                        </div>
                    </div>
                </aside>

                {{-- Settings --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="p-4 sm:p-8 bg-white rounded-2xl border border-amber-100 shadow-sm">
                        <livewire:profile.update-profile-information-form />
                    </div>

                    <div class="p-4 sm:p-8 bg-white rounded-2xl border border-amber-100 shadow-sm">
                        <livewire:profile.update-password-form />
                    </div>

                    {{-- Danger zone --}}
                    <div class="p-4 sm:p-8 bg-red-50/40 rounded-2xl border border-red-200 shadow-sm">
                        <livewire:profile.delete-user-form />
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
