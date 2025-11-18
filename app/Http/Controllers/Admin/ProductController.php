<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{


   public function dashboard()
{
    // 🔹 Today's total sales
    $todaySales = Sale::whereDate('created_at', today())
        ->sum('total');

    // 🔹 Today's profit (selling - purchase)
    $todayProfit = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
        ->whereDate('sale_items.created_at', today())
        ->select(DB::raw('SUM((sale_items.price - products.purchase_price) * sale_items.qty) as profit'))
        ->value('profit') ?? 0;

    // 🔹 Low stock count
    $lowStockCount = Product::where('quantity', '<=', 10)
        ->where('quantity', '>', 0)
        ->count();

    // 🔹 Top selling product (fixed)
    $topRow = SaleItem::select('product_id', DB::raw('SUM(qty) as soldQty'))
        ->groupBy('product_id')
        ->orderByDesc('soldQty')
        ->first();

    $topProduct = null;
    if ($topRow) {
        $topProduct = Product::find($topRow->product_id);
        if ($topProduct) {
            $topProduct->soldQty = $topRow->soldQty; // so you can show it
        }
    }

    // 🔹 Recent sales (last 5)
    $recentSales = Sale::with('user')
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();

    // 🔹 Sales for last 7 days (chart)
    $chartData = Sale::select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('SUM(total) as total')
        )
        ->where('created_at', '>=', now()->subDays(6))
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    return view('dashboard', [
        'todaySales'    => $todaySales ?? 0,
        'todayProfit'   => $todayProfit ?? 0,
        'lowStockCount' => $lowStockCount ?? 0,
        'topProduct'    => $topProduct,
        'recentSales'   => $recentSales,
        'chartDays'     => $chartData->pluck('day'),
        'chartTotals'   => $chartData->pluck('total'),
    ]);
}

    public function index(Request $request)
    {
        $search = $request->query('search');

        $products = Product::query()
            ->when($search, fn($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('product.products', compact('products', 'search'));
    }

    public function create()
    {
        $categories = Product::distinct()->pluck('category')->filter()->toArray();
        $suppliers = Product::distinct()->pluck('supplier')->filter()->toArray();

        return view('product.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'expiry_date' => 'nullable|date',
            'supply_date' => 'nullable|date',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'is_suspended' => 'nullable|boolean',
        ]);

        Product::create(array_merge($request->all(), [
            'status' => $request->quantity > 0 ? 'active' : 'out_of_stock'
        ]));

        // ✅ Redirect instead of returning the view directly
        return redirect()->route('admin.products.create')
                         ->with('success', 'Product added successfully.');
    }

    public function show(Product $product)
    {
        return view('product.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Product::distinct()->pluck('category')->filter()->toArray();
        $suppliers = Product::distinct()->pluck('supplier')->filter()->toArray();

        return view('product.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id . ',id',
            'expiry_date' => 'nullable|date',
            'supply_date' => 'nullable|date',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'is_suspended' => 'nullable|boolean',
        ]);

        $product->update(array_merge($request->all(), [
            'status' => $product->is_suspended ? 'suspended' : ($request->quantity > 0 ? 'active' : 'out_of_stock')
        ]));

        return redirect()->route('admin.products')
                         ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products')
                         ->with('success', 'Product deleted successfully.');
    }

    public function toggle(Product $product)
    {
        $product->is_suspended = !$product->is_suspended;
        $product->save();

        return redirect()->route('admin.products')
                         ->with('success', 'Product status updated successfully.');
    }



    



    
}
