<?php

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\SalesController;

// --------------------------
// Public Routes
// --------------------------
Route::get('/', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.submit');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// --------------------------
// Shared Authenticated Dashboard
// --------------------------
Route::get('/dashboard', [ProductController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');


// --------------------------
// Admin + Manager Routes
// --------------------------
// Admin: full CRUD
// Manager: view, edit, update, toggle suspend
Route::middleware(['auth', 'role:admin|manager'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('dashboard', fn() => view('dashboard'))->name('dashboard');

    // Users management - admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Products management (Admin & Manager)
    Route::get('products', [ProductController::class, 'index'])->name('products');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');

    // Admin-only actions
    Route::middleware('role:admin')->group(function () {
        Route::get('product/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
});

// --------------------------
// Cashier Routes
// --------------------------
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('products', [ProductController::class, 'index'])->name('products');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
});

// admin|manager|cashier roles
Route::middleware(['auth', 'role:admin|manager|cashier'])
      ->prefix('admin')
      ->name('admin.')
      ->group(function () {
      Route::get('/pos', [SalesController::class, 'pos'])->name('sales.pos');
Route::post('/pos/add-to-cart', [SalesController::class, 'addToCart'])->name('sales.add-to-cart');
Route::post('/pos/store-customer', [SalesController::class, 'storeCustomer'])->name('sales.store-customer');
Route::post('/pos/checkout', [SalesController::class, 'checkout'])->name('sales.checkout');
Route::post('/pos/pause', [SalesController::class, 'pause'])->name('sales.pause');
    Route::get('/pos/search-products', [SalesController::class, 'searchProducts'])
         ->name('sales.search-products');
    Route::get('/sales/{id}/print', [SalesController::class, 'print'])->name('sales.print');
        Route::get('/pos/held-sales', [SalesController::class, 'heldSales'])
        ->name('sales.held');
        Route::get('/sales/held/{sale}/resume', [SalesController::class, 'resume'])
    ->name('sales.resume');

    Route::delete('/pos/held-sales/{sale}', [SalesController::class, 'destroyHeld'])
        ->name('sales.held.destroy');

});

