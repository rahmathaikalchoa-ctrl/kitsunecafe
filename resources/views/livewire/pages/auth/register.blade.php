<?php

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $this->ensureIsNotRateLimited();

        // Normalize the email so "John@Gmail.com" is accepted and stored as "john@gmail.com"
        // (the old `lowercase` rule rejected mixed case instead of fixing it).
        $this->email = Str::lower($this->email);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        RateLimiter::hit($this->throttleKey(), 60);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        // A new customer is logged in immediately, so land them on the menu (same as login),
        // not the dashboard.
        $this->redirect(route('menu.index', absolute: false), navigate: true);
    }

    /**
     * Block the form if this IP has tried to register too many times recently.
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
     * Rate-limit key for registration: one bucket per client IP.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate('register:'.request()->ip());
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Join the cafe</h1>
        <p class="mt-1 text-sm text-gray-500">Create an account to start ordering.</p>
    </div>

    <form wire:submit="register" class="space-y-6"
          x-data="{
              pw: '',
              pwc: '',
              get score() {
                  let s = 0;
                  if (this.pw.length >= 8) s++;
                  if (/[a-z]/.test(this.pw) && /[A-Z]/.test(this.pw)) s++;
                  if (/[0-9]/.test(this.pw)) s++;
                  if (/[^A-Za-z0-9]/.test(this.pw)) s++;
                  return s;
              },
              get label() { return ['Too short', 'Weak', 'Fair', 'Good', 'Strong'][this.score]; },
              get barColor() { return ['bg-gray-200', 'bg-red-400', 'bg-amber-400', 'bg-amber-500', 'bg-green-500'][this.score]; },
              get textColor() { return ['text-gray-400', 'text-red-500', 'text-amber-600', 'text-amber-600', 'text-green-600'][this.score]; },
          }">
        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" type="text" required autofocus autocomplete="name" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Email') }}</flux:label>
            <flux:input wire:model="email" type="email" required autocomplete="username" />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Password') }}</flux:label>
            <flux:input wire:model="password" type="password" viewable required autocomplete="new-password"
                x-on:input="pw = $event.target.value" />
            <flux:error name="password" />

            {{-- Live strength hint (client-side only; server still enforces the real rules) --}}
            <div class="mt-2" x-show="pw.length > 0" x-cloak>
                <div class="flex gap-1">
                    <template x-for="i in 4" :key="i">
                        <div class="h-1.5 flex-1 rounded-full transition-colors" :class="i <= score ? barColor : 'bg-gray-200'"></div>
                    </template>
                </div>
                <p class="mt-1 text-xs" :class="textColor" x-text="label"></p>
            </div>
            <p class="mt-1 text-xs text-gray-400" x-show="pw.length === 0">{{ __('Use at least 8 characters.') }}</p>
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Confirm Password') }}</flux:label>
            <flux:input wire:model="password_confirmation" type="password" viewable required autocomplete="new-password"
                x-on:input="pwc = $event.target.value" />
            <flux:error name="password_confirmation" />

            {{-- Live "do they match?" hint --}}
            <p class="mt-1 text-xs" x-show="pwc.length > 0" x-cloak
               :class="pw === pwc ? 'text-green-600' : 'text-gray-500'"
               x-text="pw === pwc ? 'Passwords match' : 'Passwords do not match yet'"></p>
        </flux:field>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="register"
            class="w-full bg-gradient-to-r from-amber-500 to-orange-400
                   hover:from-amber-600 hover:to-orange-500
                   hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                   active:translate-y-0 active:shadow-sm active:scale-[0.97]
                   text-white text-sm font-semibold py-2.5 px-5 rounded-lg
                   transition-all duration-200
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                   disabled:opacity-60 disabled:cursor-wait disabled:hover:translate-y-0 disabled:hover:shadow-none"
        >
            <span wire:loading.remove wire:target="register">{{ __('Create account') }}</span>
            <span wire:loading wire:target="register" class="flex items-center justify-center gap-1.5">
                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                {{ __('Creating account...') }}
            </span>
        </button>

        <p class="text-center text-sm text-gray-500">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" wire:navigate class="font-medium text-amber-600 hover:text-amber-700 hover:underline">
                {{ __('Log in') }}
            </a>
        </p>
    </form>
</div>
