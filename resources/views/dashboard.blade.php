<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('order_placed'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm font-medium">
                    ✓ {{ session('order_placed') }} Thank you — pay at the counter when it's ready.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    Welcome back, {{ auth()->user()->name }}! Ready to order?
                    <a href="{{ route('menu.index') }}" wire:navigate class="ml-2 text-amber-600 underline font-medium">Browse the menu →</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
