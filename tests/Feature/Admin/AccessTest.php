<?php

use App\Models\User;

test('guest is redirected to login from the admin panel', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('a non-staff user is forbidden from the admin panel', function () {
    $user = User::factory()->create(); // not staff

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

test('staff can access every admin section', function () {
    $staff = User::factory()->staff()->create();
    $this->actingAs($staff);

    foreach (['admin.dashboard', 'admin.orders', 'admin.menu', 'admin.categories', 'admin.animals'] as $route) {
        $this->get(route($route))->assertOk();
    }
});
