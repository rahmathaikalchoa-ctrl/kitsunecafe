<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header class="flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </span>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Update Password') }}</h2>
            <p class="mt-0.5 text-sm text-gray-500">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-6"
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
            <flux:label>{{ __('Current Password') }}</flux:label>
            <flux:input wire:model="current_password" type="password" viewable autocomplete="current-password" />
            <flux:error name="current_password" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('New Password') }}</flux:label>
            <flux:input wire:model="password" type="password" viewable autocomplete="new-password"
                x-on:input="pw = $event.target.value" />
            <flux:error name="password" />

            {{-- Live strength hint (client-side only; server still enforces the real rules) --}}
            <div class="mt-2" x-show="pw.length > 0" x-cloak>
                <div class="flex gap-1" aria-hidden="true">
                    <template x-for="i in 4" :key="i">
                        <div class="h-1.5 flex-1 rounded-full transition-colors" :class="i <= score ? barColor : 'bg-gray-200'"></div>
                    </template>
                </div>
                <p class="mt-1 text-xs" aria-live="polite" :class="textColor" x-text="label"></p>
            </div>
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Confirm Password') }}</flux:label>
            <flux:input wire:model="password_confirmation" type="password" viewable autocomplete="new-password"
                x-on:input="pwc = $event.target.value" />
            <flux:error name="password_confirmation" />

            {{-- Live "do they match?" hint --}}
            <p class="mt-1 text-xs" x-show="pwc.length > 0" x-cloak aria-live="polite"
               :class="pw === pwc ? 'text-green-600' : 'text-gray-500'"
               x-text="pw === pwc ? 'Passwords match' : 'Passwords do not match yet'"></p>
        </flux:field>

        <div class="flex items-center gap-4">
            <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>

            <span
                x-data="{ show: false }"
                x-on:password-updated.window="show = true; setTimeout(() => show = false, 2000)"
                x-show="show"
                x-transition
                class="text-sm text-gray-600"
            >{{ __('Saved.') }}</span>
        </div>
    </form>
</section>
