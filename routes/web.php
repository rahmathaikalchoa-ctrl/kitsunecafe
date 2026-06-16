<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Guests see the landing page; logged-in users go straight to their dashboard.
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
})->name('home');

// Public pages
Volt::route('/menu', 'pages.menu.index')->name('menu.index');
Volt::route('/animals', 'pages.animals.index')->name('animals.index');
Volt::route('/animals/{animal}', 'pages.animals.show')->name('animals.show');

// Authenticated pages
Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('dashboard', 'pages.dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Volt::route('/cart', 'pages.cart.index')->name('cart.index');
    Volt::route('/checkout', 'pages.checkout.index')->name('checkout.index');
    Volt::route('/orders', 'pages.orders.index')->name('orders.index');
    Volt::route('/orders/{order}', 'pages.orders.show')->name('orders.show');

    // Staff-only order management (gated inside the component via the OrderPolicy).
    Volt::route('/staff/orders', 'pages.staff.orders')->name('staff.orders');
});

require __DIR__.'/auth.php';
