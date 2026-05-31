<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

// Public pages
Volt::route('/menu', 'pages.menu.index')->name('menu.index');
Volt::route('/animals', 'pages.animals.index')->name('animals.index');
Volt::route('/animals/{animal}', 'pages.animals.show')->name('animals.show');

// Authenticated pages
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Volt::route('/cart', 'pages.cart.index')->name('cart.index');
    Volt::route('/checkout', 'pages.checkout.index')->name('checkout.index');
});

require __DIR__.'/auth.php';
