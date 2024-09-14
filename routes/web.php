<?php

use App\Enum\RoleEnum;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DeviseController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayLinkController;
use App\Http\Controllers\PoidsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use App\Livewire\Panier;
use App\Livewire\Produit;
use App\Mail\RegisterMail;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('auth', 'verified')->group(function () {
    Route::middleware('role:'.RoleEnum::ADMIN->value)->group(function () {
        Route::resource('client', ClientController::class)->except('index', 'create');
        Route::resource('user', UserController::class)->except('index', 'create');
        Route::resource('image', ImageController::class)->except('index', 'create', 'show', 'store');
        Route::resource('promotion', PromotionController::class)->except('index');
        Route::resource('transport', TransportController::class)->except('index', 'create');
        Route::resource('zone', ZoneController::class)->except('index', 'create');
        Route::resource('country', CountryController::class)->except('index', 'create');
        Route::resource('shipping', ShippingController::class)->except('index', 'create');
        Route::resource('poid', PoidsController::class)->except('index', 'create', 'show');
        Route::resource('paylink', PayLinkController::class)->except('index', 'create', 'show');
        Route::resource('devise', DeviseController::class)->except('index', 'create', 'show', 'store');
        Route::get('transport/{transport}', [TransportController::class, 'get_trans_zone'])->name('transport.zone');
        Route::get('paylink/{paylink}/regenerate', [PayLinkController::class, 'regenerate'])->name('paylink.regenerate');
        Route::resource('slide', SlideController::class)->except('index', 'create');

        Route::controller(AdminController::class)->group(function () {
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
            Route::get('paylink', 'paylink')->name('paylink');
        });
    });
    Route::middleware('role:'.RoleEnum::SECRTETAIRE->value)->group(function () {
        Route::resource('order', OrderController::class)->except('index', 'create');
        Route::get('product/favoris/{data}/{product_id}', [ProductController::class, 'favoris_update'])->name('product.favori');
        Route::resource('category', CategoryController::class)->except('index', 'create', 'show');
        Route::resource('product', ProductController::class)->except('index', 'create');
        Route::controller(AdminController::class)->group(function () {
            Route::get('dashboard', 'dashboard')->name('dashboard');
            Route::get('order', 'order')->name('order');
            Route::get('product', 'product')->name('product');
            Route::get('categor', 'category')->name('categor');
        });
    });

});

Route::resource('order', OrderController::class)->only('store');
Route::controller(LinkController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('getcategory', 'getCategory')->name('getCategory');
    Route::get('shop/show/{product}', 'shopshow')->name('shop.show');
});
Route::get('/shop/{category?}', Produit::class)->name('shop');
Route::get('/panier', Panier::class)->name('panier');
Route::post('contact/mail', [ContactController::class, 'sendEmail'])->name('contact.email');
// Route::get('refresh_captcha', [ContactController::class, 'refreshCaptcha'])->name('refresh_captcha');
Route::view('categorie', 'category')->name('category');
Route::view('contact', 'contact')->name('contact');
Route::view('about', 'about')->name('about');
Route::view('livraison', 'livraison')->name('livraison');

Route::controller(SitemapController::class)->group(function () {
    Route::get('sitemap/index', 'index')->name('sitemap.index');
    Route::get('sitemap/page', 'page')->name('sitemap.page');
    Route::get('sitemap/category', 'category')->name('sitemap.category');
    Route::get('sitemap/product', 'product')->name('sitemap.product');
});

Route::controller(OrderController::class)->group(function () {
    Route::get('order/invoice/{id}', 'invoice')->name('order.invoice');
    Route::get('order/validate', 'valid')->name('order.validate');
    Route::get('order/cancel', 'cancel')->name('order.cancel');
});

Route::get('lang/{lang}', function ($lang) {
    if (in_array($lang, ['en', 'fr'])) {
        App::setLocale($lang);
        Session::put('locale', $lang);
    }

    return back();
})->name('change_language');

Route::get('devise/{devise}', function ($devise) {
    if (in_array($devise, ['EUR', 'CFA'])) {
        Session::put('devise', $devise);
    }

    return back();
})->name('change_devise');

Route::get('test', function () {
    Mail::to('salediallo61@gmail.com')->send(new RegisterMail('test'));
    // Artisan::call('optimize:clear');
    // Artisan::call('migrate');

    return dd('ok');

});
require __DIR__.'/auth.php';
