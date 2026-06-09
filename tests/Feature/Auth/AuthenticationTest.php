<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSeeVolt('pages.auth.login');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('menu.index', absolute: false));

    $this->assertAuthenticated();
});

test('users can authenticate with a differently-cased email', function () {
    // Emails are stored lowercased at registration; logging in with a mixed-case version
    // must still work (it failed on case-sensitive SQLite before the email was normalized).
    $user = User::factory()->create(['email' => 'fox@example.com']);

    Volt::test('pages.auth.login')
        ->set('form.email', 'FOX@Example.com')
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('menu.index', absolute: false));

    $this->assertAuthenticated();
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create();

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('navigation menu can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSeeVolt('layout.navigation');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('layout.navigation')
        ->call('logout')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
