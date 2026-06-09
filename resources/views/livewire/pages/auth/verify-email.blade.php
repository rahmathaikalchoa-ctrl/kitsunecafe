<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <h1 class="text-xl font-bold text-gray-900 mb-2">Verify your email</h1>
    <p class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 space-y-4">
        <button
            wire:click="sendVerification"
            wire:loading.attr="disabled"
            wire:target="sendVerification"
            type="button"
            class="w-full bg-linear-to-r from-amber-500 to-orange-400
                   hover:from-amber-600 hover:to-orange-500
                   hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                   active:translate-y-0 active:shadow-xs active:scale-[0.97]
                   text-white text-sm font-semibold py-2.5 px-5 rounded-lg
                   transition-all duration-200
                   focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                   disabled:opacity-60 disabled:cursor-wait disabled:hover:translate-y-0 disabled:hover:shadow-none"
        >
            <span wire:loading.remove wire:target="sendVerification">{{ __('Resend Verification Email') }}</span>
            <span wire:loading wire:target="sendVerification" class="flex items-center justify-center gap-1.5">
                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                {{ __('Sending...') }}
            </span>
        </button>

        <div class="text-center">
            <button wire:click="logout" type="button" class="text-sm font-medium text-gray-500 hover:text-amber-600 transition-colors">
                {{ __('Log Out') }}
            </button>
        </div>
    </div>
</div>
