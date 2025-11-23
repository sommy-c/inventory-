<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share data only to the topbar (efficient & clean)
        View::composer('admin.partials.topbar', function ($view) {

            // 🔸 Low stock
            $lowStock = Product::select('id', 'name', 'quantity', 'sku')
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', 10)
                ->orderBy('quantity', 'asc')
                ->get();

            // 🔸 Out of stock
            $outOfStock = Product::select('id', 'name', 'quantity', 'sku')
                ->where('quantity', '<=', 0)
                ->orderBy('name', 'asc')
                ->get();

            // 🔸 Expiring soon (next 7 days, still in stock)
            $expiringSoon = Product::select('id', 'name', 'quantity', 'sku', 'expiry_date')
                ->whereNotNull('expiry_date')
                ->where('quantity', '>', 0)
                ->whereBetween('expiry_date', [now(), now()->addDays(7)])
                ->orderBy('expiry_date')
                ->get();

            // 🔸 Expired (expiry_date < today, still in stock)
            $expiredProducts = Product::select('id', 'name', 'quantity', 'sku', 'expiry_date')
                ->whereNotNull('expiry_date')
                ->where('quantity', '>', 0)
                ->where('expiry_date', '<', now())
                ->orderBy('expiry_date')
                ->get();

            // 🔔 Total alerts for the bell
            $totalAlerts =
                $lowStock->count()
                + $outOfStock->count()
                + $expiringSoon->count()
                + $expiredProducts->count();

            $view->with([
                'lowStock'        => $lowStock,
                'outOfStock'      => $outOfStock,
                'lowStockCount'   => $lowStock->count(),
                'outOfStockCount' => $outOfStock->count(),
                'expiringSoon'    => $expiringSoon,
                'expiredProducts' => $expiredProducts,
                'totalAlerts'     => $totalAlerts,
            ]);
        });
    }
}
