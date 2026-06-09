<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->ensureIsNotRateLimited();

        // Count this submission BEFORE validating so malformed/spam attempts also count toward
        // the throttle.
        RateLimiter::hit($this->throttleKey(), 60);

        // Normalize the email (trim stray spaces + lowercase) so the lookup matches the value
        // stored at registration.
        $this->email = Str::lower(trim($this->email));

        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }

    /**
     * Block the form if this IP has requested too many reset links recently.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Rate-limit key for password-reset requests: one bucket per client IP.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate('password-reset:'.request()->ip());
    }
}; ?>

<div>
    <h1 class="text-xl font-bold text-gray-900 mb-2">Reset your password</h1>
    <p class="mb-6 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600">{{ session('status') }}</div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        <flux:field>
            <flux:label>{{ __('Email') }}</flux:label>
            <flux:input wire:model="email" type="email" required autofocus
                autocapitalize="none" autocorrect="off" inputmode="email" />
            <flux:error name="email" />
        </flux:field>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="sendPasswordResetLink"
            class="w-full bg-linear-to-r from-amber-500 to-orange-400
                   hover:from-amber-600 hover:to-orange-500
                   hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                   active:translate-y-0 active:shadow-xs active:scale-[0.97]
                   text-white text-sm font-semibold py-2.5 px-5 rounded-lg
                   transition-all duration-200
                   focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                   disabled:opacity-60 disabled:cursor-wait disabled:hover:translate-y-0 disabled:hover:shadow-none"
        >
            <span wire:loading.remove wire:target="sendPasswordResetLink">{{ __('Email Password Reset Link') }}</span>
            <span wire:loading wire:target="sendPasswordResetLink" class="flex items-center justify-center gap-1.5">
                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                {{ __('Sending link...') }}
            </span>
        </button>

        <p class="text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" wire:navigate class="font-medium text-amber-600 hover:text-amber-700 hover:underline">
                {{ __('Back to log in') }}
            </a>
        </p>
    </form>
</div>
