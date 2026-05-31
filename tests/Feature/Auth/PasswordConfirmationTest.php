<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/confirm-password')
        ->assertSeeVolt('pages.auth.confirm-password')
        ->assertStatus(200);
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('pages.auth.confirm-password')
        ->set('password', 'password')
        ->call('confirmPassword')
        ->assertRedirect('/dashboard')
        ->assertHasNoErrors();
});

test('password is not confirmed with invalid password', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('pages.auth.confirm-password')
        ->set('password', 'wrong-password')
        ->call('confirmPassword')
        ->assertNoRedirect()
        ->assertHasErrors('password');
});
