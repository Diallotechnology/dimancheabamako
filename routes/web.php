<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ShippingPaysController;
use App\Http\Controllers\ShippingZoneController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VilleController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('auth')->group(function () {

    Route::resource('client', ClientController::class)->except('index', 'create');
    Route::resource('user', UserController::class)->except('index', 'create');
    Route::resource('order', OrderController::class)->except('index', 'create');
    Route::resource('product', ProductController::class)->except('index', 'create');
    Route::get('product/{product}/{data}', [ProductController::class, 'favoris'])->name('product.favoris');
    Route::resource('category', CategoryController::class)->except('index', 'create', 'show');
    Route::resource('image', ImageController::class)->except('index', 'create', 'show', 'store');
    Route::resource('promotion', PromotionController::class)->except('index');
    // Route::resource('shippingzone', ShippingZoneController::class)->except('index', 'create');
    // Route::resource('shippingpays', ShippingPaysController::class)->except('index', 'create');
    Route::resource('transport', TransportController::class)->except('index', 'create');
    Route::resource('zone', ZoneController::class)->except('index', 'create');
    Route::resource('country', CountryController::class)->except('index', 'create');
    Route::resource('ville', VilleController::class)->except('index', 'create');

    Route::controller(AdminController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('order', 'order')->name('order');
        Route::get('product', 'product')->name('product');
        Route::get('category', 'category')->name('category');
        Route::get('user', 'user')->name('user');
        Route::get('client', 'client')->name('client');
        Route::get('promotion', 'promotion')->name('promotion');
        Route::get('zone', 'zone')->name('zone');
        Route::get('country', 'country')->name('pays');
        Route::get('ville', 'ville')->name('ville');
        Route::get('transport', 'transport')->name('transport');
    });
});

Route::controller(CartController::class)->group(function () {
    Route::get('panier', 'index')->name('cart.index');
    Route::get('count', 'GetCount')->name('cart.count');
    Route::get('country/{id}', 'GetCountry')->name('cart.country');
    Route::get('cart/{product}', 'store')->name('cart.store');
});
Route::controller(LinkController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('category/{category}', 'shop')->name('shop');
    Route::get('livraison', 'livraison')->name('livraison');
    Route::get('getcategory', 'getCategory')->name('getCategory');
    Route::get('shop/{product}', 'shopshow')->name('shop.show');
});

Route::inertia('contact', 'Contact')->name('contact');
Route::inertia('about', 'about')->name('about');
require __DIR__.'/auth.php';
