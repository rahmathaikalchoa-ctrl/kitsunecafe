<?php

use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSeeVolt('pages.auth.register');
});

test('registration normalizes a mixed-case email to lowercase', function () {
    Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'Test@Example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    // The mixed-case input must be stored normalized as lowercase.
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('registration is rate limited and counts invalid attempts', function () {
    // Five invalid submissions (too-short password) — each must still count toward the limit
    // because we hit() the limiter before validating.
    for ($i = 0; $i < 5; $i++) {
        Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', "user{$i}@example.com")
            ->set('password', 'x')
            ->set('password_confirmation', 'x')
            ->call('register');
    }

    // A sixth, fully valid submission must be blocked by the throttle, not create an account.
    Volt::test('pages.auth.register')
        ->set('name', 'Valid User')
        ->set('email', 'valid@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'valid@example.com']);
});

test('new users can register', function () {
    Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
