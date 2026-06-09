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
        ->assertRedirect(route('menu.index', absolute: false));

    // The mixed-case input must be stored normalized as lowercase.
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('new users can register', function () {
    Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertRedirect(route('menu.index', absolute: false));

    $this->assertAuthenticated();
});
