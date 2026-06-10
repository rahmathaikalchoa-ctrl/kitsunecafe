<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        // Normalize BEFORE validating: the `email` rule rejects surrounding spaces, and trimming +
        // lowercasing here lets a space-padded / mixed-case email match the stored value.
        $this->form->email = Str::lower(trim($this->form->email));

        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Land on the dashboard by default. (redirectIntended still returns them to a page
        // they were bounced from, e.g. cart/checkout, if one was remembered.)
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Welcome back</h1>
        <p class="mt-1 text-sm text-gray-500">Log in to order and meet the foxes.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600">{{ session('status') }}</div>
    @endif

    <form wire:submit="login" class="space-y-6">
        <flux:field>
            <flux:label>{{ __('Email') }}</flux:label>
            <flux:input wire:model="form.email" type="email" required autofocus autocomplete="username"
                autocapitalize="none" autocorrect="off" inputmode="email" />
            <flux:error name="form.email" />
        </flux:field>

        <flux:field>
            <div class="flex justify-between items-center">
                <flux:label>{{ __('Password') }}</flux:label>
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-amber-600 hover:text-amber-700 hover:underline" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
            <flux:input wire:model="form.password" type="password" viewable required autocomplete="current-password" />
            <flux:error name="form.password" />
        </flux:field>

        <flux:checkbox wire:model="form.remember" label="{{ __('Remember me') }}" />

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="login"
            class="w-full bg-linear-to-r from-amber-500 to-orange-400
                   hover:from-amber-600 hover:to-orange-500
                   hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                   active:translate-y-0 active:shadow-xs active:scale-[0.97]
                   text-white text-sm font-semibold py-2.5 px-5 rounded-lg
                   transition-all duration-200
                   focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                   disabled:opacity-60 disabled:cursor-wait disabled:hover:translate-y-0 disabled:hover:shadow-none"
        >
            <span wire:loading.remove wire:target="login">{{ __('Log in') }}</span>
            <span wire:loading wire:target="login" class="flex items-center justify-center gap-1.5">
                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                {{ __('Logging in...') }}
            </span>
        </button>

        @if (Route::has('register'))
            <p class="text-center text-sm text-gray-500">
                {{ __('New to the cafe?') }}
                <a href="{{ route('register') }}" wire:navigate class="font-medium text-amber-600 hover:text-amber-700 hover:underline">
                    {{ __('Create an account') }}
                </a>
            </p>
        @endif
    </form>
</div>
