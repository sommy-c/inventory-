<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share data only to the topbar (efficient & clean)
        View::composer('admin.partials.topbar', function ($view) {

            // Fetch LOW STOCK (≤ 10 but > 0)
            $lowStock = Product::select('id', 'name', 'quantity')
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', 10)
                ->orderBy('quantity', 'asc')
                ->get();

            // Fetch OUT OF STOCK
            $outOfStock = Product::select('id', 'name', 'quantity')
                ->where('quantity', '<=', 0)
                ->orderBy('name', 'asc')
                ->get();

            // Share with the view
            $view->with([
                'lowStock'        => $lowStock,
                'outOfStock'      => $outOfStock,
                'lowStockCount'   => $lowStock->count(),
                'outOfStockCount' => $outOfStock->count(),
            ]);
        });
    }
}
