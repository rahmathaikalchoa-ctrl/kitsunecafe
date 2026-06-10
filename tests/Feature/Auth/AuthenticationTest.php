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
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('login normalizes a mixed-case, space-padded email', function () {
    // Emails are stored lowercased at registration; logging in with a mixed-case, space-padded
    // version (common from mobile keyboards) must still work — it failed on case-sensitive SQLite
    // before the email was trimmed + lowercased.
    $user = User::factory()->create(['email' => 'fox@example.com']);

    Volt::test('pages.auth.login')
        ->set('form.email', '  FOX@Example.com  ')
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

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

test('authenticated users visiting guest pages are redirected to the menu', function () {
    // Guest-only pages bounce logged-in users; that redirect must match where login/register
    // send customers (the menu), not the framework-default dashboard.
    $user = User::factory()->create();

    $this->actingAs($user)->get('/login')->assertRedirect(route('menu.index'));
    $this->actingAs($user)->get('/register')->assertRedirect(route('menu.index'));
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
