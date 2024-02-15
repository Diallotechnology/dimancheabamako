<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaysController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    Route::resource('client', ClientController::class)->except('index', 'create');
    Route::resource('user', UserController::class)->except('index', 'create');
    Route::resource('order', OrderController::class)->except('index', 'create');
    Route::resource('product', ProductController::class)->except('index', 'create');
    Route::resource('category', CategoryController::class)->except('index', 'create', 'show');
    Route::resource('image', ImageController::class)->except('index', 'create', 'show', 'store');
    Route::resource('promotion', PromotionController::class)->except('index', 'create');
    Route::resource('zone', ZoneController::class)->except('index', 'create');
    Route::resource('pays', PaysController::class)->except('index', 'create');
    Route::resource('transport', TransportController::class)->except('index', 'create');

    Route::controller(AdminController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('order', 'order')->name('order');
        Route::get('product', 'product')->name('product');
        Route::get('category', 'category')->name('category');
        Route::get('user', 'user')->name('user');
        Route::get('client', 'client')->name('client');
        Route::get('promotion', 'promotion')->name('promotion');
        Route::get('zone', 'zone')->name('zone');
        Route::get('pays', 'pays')->name('pays');
        Route::get('transport', 'transport')->name('transport');
    });
});

// Route::resource('cart', CartController::class)->except('index', 'create', 'store');
Route::get('cartt/{id}', [CartController::class, 'store'])->name('cart.store');
Route::controller(LinkController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('shop', 'shop')->name('shop');
    Route::get('livraison', 'livraison')->name('livraison');
    Route::get('shop/{product}', 'shopshow')->name('shop.show');
});
Route::inertia('contact', 'Contact')->name('contact');
Route::inertia('about', 'about')->name('about');
require __DIR__.'/auth.php';
