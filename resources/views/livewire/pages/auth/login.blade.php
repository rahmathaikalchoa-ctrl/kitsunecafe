<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600">{{ session('status') }}</div>
    @endif

    <form wire:submit="login" class="space-y-6">
        <flux:field>
            <flux:label>{{ __('Email') }}</flux:label>
            <flux:input wire:model="form.email" type="email" required autofocus autocomplete="username" />
            <flux:error name="form.email" />
        </flux:field>

        <flux:field>
            <div class="flex justify-between items-center">
                <flux:label>{{ __('Password') }}</flux:label>
                @if (Route::has('password.request'))
                    <a class="text-sm text-gray-600 hover:text-gray-900 underline" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
            <flux:input wire:model="form.password" type="password" required autocomplete="current-password" />
            <flux:error name="form.password" />
        </flux:field>

        <flux:checkbox wire:model="form.remember" label="{{ __('Remember me') }}" />

        <flux:button type="submit" variant="primary" class="w-full">
            {{ __('Log in') }}
        </flux:button>
    </form>
</div>
