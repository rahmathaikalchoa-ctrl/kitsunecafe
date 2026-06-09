<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="text-xl font-bold text-gray-900 mb-2">Confirm your password</h1>
    <p class="mb-6 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    <form wire:submit="confirmPassword" class="space-y-6">
        <flux:field>
            <flux:label>{{ __('Password') }}</flux:label>
            <flux:input wire:model="password" type="password" viewable required autocomplete="current-password" />
            <flux:error name="password" />
        </flux:field>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="confirmPassword"
            class="w-full bg-linear-to-r from-amber-500 to-orange-400
                   hover:from-amber-600 hover:to-orange-500
                   hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                   active:translate-y-0 active:shadow-xs active:scale-[0.97]
                   text-white text-sm font-semibold py-2.5 px-5 rounded-lg
                   transition-all duration-200
                   focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                   disabled:opacity-60 disabled:cursor-wait disabled:hover:translate-y-0 disabled:hover:shadow-none"
        >
            <span wire:loading.remove wire:target="confirmPassword">{{ __('Confirm') }}</span>
            <span wire:loading wire:target="confirmPassword" class="flex items-center justify-center gap-1.5">
                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                {{ __('Confirming...') }}
            </span>
        </button>
    </form>
</div>
