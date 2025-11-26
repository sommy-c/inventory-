<?php

use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DamageReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SupplierController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.submit');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| Admin + Manager Routes
|--------------------------------------------------------------------------
|
| These cover management stuff: products, users (index/create/edit),
| sales reports, suppliers, purchases, damages, etc.
| Admin gets extra power in a nested subgroup.
*/
Route::middleware(['auth', 'role:admin|manager'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    /*
    |----------------------------------------------------------------------
    | PRODUCTS (Admin + Manager)
    |----------------------------------------------------------------------
    */
   Route::get('products', [ProductController::class, 'index'])->name('products');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
     // 👇 NOW BOTH admin & manager CAN CREATE VIA store()
    Route::post('products', [ProductController::class, 'store'])->name('products.store');


    /*
    |----------------------------------------------------------------------
    | SALES REPORT (Admin + Manager)
    |----------------------------------------------------------------------
    */
    Route::get('sales',                  [SalesController::class, 'index'])->name('sales.index');
   
    Route::get('sales/{id}/details',     [SalesController::class, 'details'])->name('sales.details');

    // Export filtered sales as PDF
    Route::get('sales/export/pdf',       [SalesController::class, 'exportPdf'])->name('sales.export.pdf');

    /*
    |----------------------------------------------------------------------
    | SUPPLIERS (Admin + Manager)
    |----------------------------------------------------------------------
    */
    Route::get('suppliers',                 [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('suppliers',                [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('suppliers/{supplier}/details', [SupplierController::class, 'details'])->name('suppliers.details');

    /*
    |----------------------------------------------------------------------
    | PURCHASES (Admin + Manager)
    |----------------------------------------------------------------------
    */
    Route::get('purchases',              [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/create',       [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('purchases',             [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('purchases/{purchase}',   [PurchaseController::class, 'show'])->name('purchases.show');
     Route::post('/purchases/{purchase}/add-payment', [PurchaseController::class, 'addPayment'])
            ->name('purchases.addPayment');
   

    /*
    |----------------------------------------------------------------------
    | DAMAGES (Admin + Manager)
    |----------------------------------------------------------------------
    */
    Route::get('damages',                [DamageReportController::class, 'index'])->name('damages.index');
    Route::get('damages/create',         [DamageReportController::class, 'create'])->name('damages.create');
    Route::post('damages',               [DamageReportController::class, 'store'])->name('damages.store');

    // status actions (only admin)
    Route::post('damages/{damage}/approve', [DamageReportController::class, 'approve'])
        ->name('damages.approve')
        ->middleware('role:admin');

    Route::post('damages/{damage}/reject', [DamageReportController::class, 'reject'])
        ->name('damages.reject')
        ->middleware('role:admin');

    Route::post('damages/{damage}/resolve', [DamageReportController::class, 'resolve'])
        ->name('damages.resolve');

    // exports
    Route::get('damages/export/excel',   [DamageReportController::class, 'exportExcel'])->name('damages.export.excel');
    Route::get('damages/export/pdf',     [DamageReportController::class, 'exportPdf'])->name('damages.export.pdf');

    /*
    |----------------------------------------------------------------------
    | USERS (Admin + Manager)
    |----------------------------------------------------------------------
    | index/create/store/edit/update are visible.
    | Controller logic will:
    |  - hide admin users from managers (in index)
    |  - block managers from editing admins
    |  - prevent managers from assigning 'admin' role
    |  - prevent admin from changing own role
    */
    Route::get('users',              [UserController::class, 'index'])->name('users.index');
    Route::get('users/create',       [UserController::class, 'create'])->name('users.create');
    Route::post('users',             [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit',  [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}',       [UserController::class, 'update'])->name('users.update');




// ORDER
        Route::get('orders',               [OrderController::class, 'index'])->name('index');
        Route::get('orders/create',        [OrderController::class, 'create'])->name('create');
        Route::post('orders',              [OrderController::class, 'store'])->name('store');
        Route::get('orders/{order}',       [OrderController::class, 'show'])->name('show');
        Route::delete('orders/{order}',    [OrderController::class, 'destroy'])->name('destroy');

        // extra actions
       
        Route::post('orders/{order}/supplied',     [OrderController::class, 'markSupplied'])->name('supplied');

    /*
    |----------------------------------------------------------------------
    | ADMIN-ONLY SUBGROUP
    |----------------------------------------------------------------------
    | Only admins can do these: delete users, settings, some product actions,
    | customers management, messaging, etc.
    */
    Route::middleware('role:admin')->group(function () {

        // Products create/store/destroy
       
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Customers CRUD (admin only here as per your routes)
        Route::get('customers',      [CustomerController::class, 'index'])->name('customers.index');
        Route::post('customers',     [CustomerController::class, 'store'])->name('customers.store');

        // Messaging
        Route::post('messages/email', [MessageController::class, 'sendEmail'])->name('messages.email');
        Route::post('messages/sms',   [MessageController::class, 'sendSms'])->name('messages.sms');

        // Suppliers delete
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        // Users delete
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        /*
        |---------------- SETTINGS (Admin Only) ----------------
        */
        // General
        Route::get('settings/general',  [SettingController::class, 'general'])->name('settings.general');
        Route::post('settings/general', [SettingController::class, 'updateGeneral']);

        // Branding
        Route::get('settings/branding',  [SettingController::class, 'branding'])->name('settings.branding');
        Route::post('settings/branding', [SettingController::class, 'updateBranding']);

        // Appearance
        Route::get('settings/appearance',  [SettingController::class, 'appearance'])->name('settings.appearance');
        Route::post('settings/appearance', [SettingController::class, 'updateAppearance'])
            ->name('settings.appearance.update');

        // VAT / Tax
        Route::get('settings/vat',  [SettingController::class, 'vat'])->name('settings.vat');
        Route::post('settings/vat', [SettingController::class, 'updateVat']);

        // Currency & Exchange
        Route::get('settings/currency-exchange',  [SettingController::class, 'currencyExchange'])->name('settings.currency');
        Route::post('settings/currency-exchange', [SettingController::class, 'updateCurrencyExchange']);

        // Receipt / POS
        Route::get('settings/receipt',  [SettingController::class, 'receipt'])->name('settings.receipt');
        Route::post('settings/receipt', [SettingController::class, 'updateReceipt']);

        // Notifications
        Route::get('settings/notifications',  [SettingController::class, 'notifications'])->name('settings.notifications');
        Route::post('settings/notifications', [SettingController::class, 'updateNotifications']);


        // Order
         Route::post('orders/{order}/approve',      [OrderController::class, 'approve'])->name('approve');
    });
});


/*
|--------------------------------------------------------------------------
| Cashier Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:cashier'])
    ->prefix('cashier')
    ->name('cashier.')
    ->group(function () {
        Route::get('dashboard', fn () => view('dashboard'))->name('dashboard');
        Route::get('products',        [ProductController::class, 'index'])->name('products');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    });


/*
|--------------------------------------------------------------------------
| POS + ADMIN DASHBOARD (Admin + Manager + Cashier)
|--------------------------------------------------------------------------
|
| All roles that can sell go through here.
| Note: dashboard route uses admin prefix so your sidebar route('admin.dashboard')
| works for all roles (admin, manager, cashier).
*/
Route::middleware(['auth', 'role:admin|manager|cashier'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // POS
        Route::get('pos',              [SalesController::class, 'pos'])->name('sales.pos');
        Route::post('pos/add-to-cart', [SalesController::class, 'addToCart'])->name('sales.add-to-cart');
        Route::post('pos/store-customer', [SalesController::class, 'storeCustomer'])->name('sales.store-customer');
        Route::post('pos/checkout',    [SalesController::class, 'checkout'])->name('sales.checkout');
        Route::post('pos/pause',       [SalesController::class, 'pause'])->name('sales.pause');

        Route::get('pos/search-products', [SalesController::class, 'searchProducts'])->name('sales.search-products');

        // Held sales
        Route::get('pos/held-sales',           [SalesController::class, 'heldSales'])->name('sales.held');
        Route::get('sales/held/{sale}/resume', [SalesController::class, 'resume'])->name('sales.resume');
        Route::delete('pos/held-sales/{sale}', [SalesController::class, 'destroyHeld'])->name('sales.held.destroy');

        // ✅ MOVE PRINT ROUTE HERE
        Route::get('sales/{sale}/print', [SalesController::class, 'print'])->name('sales.print');

        // Dashboard + alerts
        Route::get('dashboard', [ProductController::class, 'dashboard'])->name('dashboard');
        Route::get('/products/json/{product}', [ProductController::class, 'json'])
    ->name('admin.products.json');
        Route::post('alerts/mark-read', [ProductController::class, 'markRead'])->name('alerts.markRead');
    });


