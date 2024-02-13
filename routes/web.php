<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin')->group(function () {

    Route::resource('client', ClientController::class)->except('index', 'create');
    Route::resource('user', UserController::class)->except('index', 'create');
    Route::resource('order', OrderController::class)->except('index', 'create');
    Route::resource('product', ProductController::class)->except('index', 'create');
    Route::resource('category', CategoryController::class)->except('index', 'create', 'show');
    Route::resource('image', ImageController::class)->except('index', 'create', 'show', 'store');

    Route::controller(AdminController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('order', 'order')->name('order');
        Route::get('product', 'product')->name('product');
        Route::get('category', 'category')->name('category');
        Route::get('user', 'user')->name('user');
        Route::get('client', 'client')->name('client');
    });
});

Route::get('welcome', function () {
    return view('welcome');
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);
});
// Route::resource('cart', CartController::class)->except('index', 'create', 'store');
Route::get('cartt/{id}', [CartController::class, 'store'])->name('cart.store');
Route::controller(LinkController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('shop', 'shop')->name('shop');
    Route::get('shop/{product}', 'shopshow')->name('shop.show');
});
Route::inertia('contact', 'Contact')->name('contact');
require __DIR__.'/auth.php';
