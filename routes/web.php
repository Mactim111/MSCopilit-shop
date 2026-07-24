<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\CatalogController;

// Главная
Route::get('/', [HomeController::class, 'index'])->name('home');

// Каталог (список групп)
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

// Группа категорий
Route::get('/catalog/{group}', [CatalogController::class, 'group'])->name('catalog.group');

// Категория внутри группы
Route::get('/catalog/{group}/{category}', [CatalogController::class, 'category'])->name('catalog.category');

// Подкатегория
Route::get('/catalog/{group}/{category}/{subcategory}', [CatalogController::class, 'subcategory'])->name('catalog.subcategory');

// Та же подкатегория, но с фильтром бренда в URI
Route::get('/catalog/{group}/{category}/{subcategory}/brand={brands}', 
    [CatalogController::class, 'subcategory'])->name('catalog.subcategory.brand');

// Вариант товара
Route::get('/products/{variant}', [ProductVariantController::class, 'show'])->name('catalog.variant');


Route::middleware(['auth', 'verified'])->group(function () {

    // Корзина
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{variant}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');

// Заказы
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/thanks/{order}', [OrderController::class, 'thanks'])->name('orders.thanks');


    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/orders', [ProfileController::class, 'orders'])->name('profile.orders');

    Route::get('/profile/orders/{order}', [ProfileController::class, 'order'])->name('profile.order')->middleware('can:view,order');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::post('/profile/address', [AddressController::class, 'store'])->name('profile.address.store');
    Route::delete('/profile/address/{address}', [AddressController::class, 'destroy'])->name('profile.address.delete');
    Route::get('/profile/address/{address}/edit', [AddressController::class, 'edit'])->name('profile.address.edit');
    Route::put('/profile/address/{address}', [AddressController::class, 'update'])->name('profile.address.update');

});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Route::get('/', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductAdminController::class);
    Route::post('/products/{product:slug}/restore', [ProductAdminController::class, 'restore'])->name('products.restore');
    Route::delete('/products/{product:slug}/force-delete', [ProductAdminController::class, 'forceDelete'])->name('products.force-delete');
    //    Route::delete('/products/image/{image}', [ProductAdminController::class, 'deleteImage'])->name('products.image.delete');

    Route::resource('categories', CategoryAdminController::class);

    Route::resource('orders', OrderAdminController::class)->only(['index', 'show', 'update']);
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update');


Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/send', [EmailVerificationController::class, 'send'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');







