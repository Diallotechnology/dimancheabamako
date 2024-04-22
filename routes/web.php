<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DeviseController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PoidsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Artisan;
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
    Route::resource('transport', TransportController::class)->except('index', 'create');
    Route::resource('zone', ZoneController::class)->except('index', 'create');
    Route::resource('country', CountryController::class)->except('index', 'create');
    Route::resource('shipping', ShippingController::class)->except('index', 'create');
    Route::resource('poid', PoidsController::class)->except('index', 'create', 'show');
    Route::resource('devise', DeviseController::class)->except('index', 'create', 'show', 'store');
    Route::get('transport/{transport}', [TransportController::class, 'get_trans_zone'])->name('transport.zone');
    Route::resource('slide', SlideController::class)->except('index', 'create');

    Route::controller(AdminController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('order', 'order')->name('order');
        Route::get('product', 'product')->name('product');
        Route::get('category', 'category')->name('category');
        Route::get('user', 'user')->name('user');
        Route::get('customer', 'customer')->name('user.client');
        Route::get('client', 'client')->name('client');
        Route::get('promotion', 'promotion')->name('promotion');
        Route::get('zone', 'zone')->name('zone');
        Route::get('country', 'country')->name('pays');
        Route::get('transport', 'transport')->name('transport');
        Route::get('shipping', 'shipping')->name('shipping');
        Route::get('poids', 'poids')->name('poids');
        Route::get('devise', 'devise')->name('devise');
        Route::get('slide', 'slide')->name('slide');
        Route::get('maintenance', 'maintenance')->name('maintenance');
    });
});

Route::resource('order', OrderController::class)->only('store');
Route::controller(CartController::class)->group(function () {
    Route::get('panier', 'index')->name('cart.index');
    Route::get('count', 'GetCount')->name('cart.count');
    Route::get('trans/{id?}', 'GetTrans')->name('cart.trans');
    Route::get('cart/trans', 'GetTransport')->name('cart.transport');
    Route::get('cart/shipping/{country}/{transid}', 'GetShipping')->name('cart.shipping');
    Route::get('cart/{product}', 'store')->name('cart.store');
    Route::get('cart/update/{cart}/{qte}', 'update')->name('cart.update');
    Route::delete('cart/delete/{id}', 'destroy')->name('cart.destroy');
});
Route::controller(LinkController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('lang/{lang}', 'langchange')->name('language');
    Route::get('shop/{category?}', 'shop')->name('shop');
    Route::get('getcategory', 'getCategory')->name('getCategory');
    Route::get('shop/show/{product}', 'shopshow')->name('shop.show');
});
Route::post('contact/mail', ContactController::class)->name('contact.email');
Route::inertia('contact', 'Contact')->name('contact');
Route::inertia('about', 'About')->name('about');
Route::inertia('livraison', 'Livraison')->name('livraison');

Route::controller(SitemapController::class)->group(function () {
    Route::get('sitemap/index', 'index')->name('sitemap.index');
    Route::get('sitemap/page', 'page')->name('sitemap.page');
    Route::get('sitemap/category', 'category')->name('sitemap.category');
    Route::get('sitemap/product', 'product')->name('sitemap.product');

});
// Route::get('test', function () {
//     Artisan::call('optimize:clear');
//     Artisan::call('db:wipe');
//     Artisan::call('migrate --seed');
// });
require __DIR__.'/auth.php';
