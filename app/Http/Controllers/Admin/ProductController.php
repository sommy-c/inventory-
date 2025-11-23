<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAlertRead;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category; // 🔹 use Category model
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function dashboard()
{
    $currencySymbol   = Setting::get('currency_symbol', '₦');
    $currencyPosition = Setting::get('currency_position', 'left');
    // ------------------------------------
    // 🔹 Today's Total Sales
    // ------------------------------------
    $todaySales = Sale::whereDate('created_at', today())
        ->sum('total') ?? 0;

    // ------------------------------------
    // 🔹 Today's Profit (selling - cost)
    // ------------------------------------
    $todayProfit = SaleItem::join('products', 'sale_items.product_id', '=', 'products.id')
        ->whereDate('sale_items.created_at', today())
        ->select(DB::raw('SUM((sale_items.price - products.purchase_price) * sale_items.qty) as profit'))
        ->value('profit') ?? 0;

    // ------------------------------------
    // 🔹 Low Stock Count (≤10 but >0)
    // ------------------------------------
    $lowStockCount = Product::where('quantity', '<=', 10)
        ->where('quantity', '>', 0)
        ->count();

    // ------------------------------------
    // 🔹 Top Selling Product
    // ------------------------------------
    $topRow = SaleItem::select('product_id', DB::raw('SUM(qty) as soldQty'))
        ->groupBy('product_id')
        ->orderByDesc('soldQty')
        ->first();

    $topProduct = null;
    if ($topRow) {
        $topProduct = Product::find($topRow->product_id);
        if ($topProduct) {
            $topProduct->soldQty = $topRow->soldQty;
        }
    }

    // ------------------------------------
    // 🔹 Recent Sales (latest 5)
    // ------------------------------------
    $recentSales = Sale::with('user')
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();

    // ------------------------------------
    // 🔹 Sales for Last 7 Days (Chart)
    // ------------------------------------
    $rawChart = Sale::select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('SUM(total) as total')
        )
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    // Ensure chart always returns 7 days even when empty
    $chartDays = collect();
    $chartTotals = collect();

    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->toDateString();
        $chartDays->push($date);

        $total = $rawChart->firstWhere('day', $date)->total ?? 0;
        $chartTotals->push($total);
    }

    // ------------------------------------
    // 🔹 Return View
    // ------------------------------------
    return view('dashboard', [
        'todaySales'    => $todaySales,
        'todayProfit'   => $todayProfit,
        'lowStockCount' => $lowStockCount,
        'topProduct'    => $topProduct,
        'recentSales'   => $recentSales,
        'chartDays'     => $chartDays,
        'chartTotals'   => $chartTotals,
    ]);
}


public function markRead()
{
   $user = Auth::user();
;

    // Mark all alerts as read for this user
    foreach (['low', 'out', 'expiring', 'expired'] as $type) {
        StockAlertRead::create([
            'user_id' => $user->id,
            'alert_type' => $type,
        ]);
    }

    return response()->json(['status' => 'ok']);
}


 public function index(Request $request)
{
    $search       = $request->query('search');
    $categoryFilt = $request->query('category');
    $supplierFilt = $request->query('supplier');
    $statusFilt   = $request->query('status'); // 'active', 'out_of_stock', 'suspended'
    $expiryFilt   = $request->query('expiry'); // 'expired', 'expiring'

    $products = Product::query()
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        })
        ->when($categoryFilt, fn($q) => $q->where('category', $categoryFilt))
        ->when($supplierFilt, fn($q) => $q->where('supplier', $supplierFilt))

        // ✅ match your getStatusAttribute() logic
        ->when($statusFilt === 'suspended', function ($q) {
            $q->where('is_suspended', true);
        })
        ->when($statusFilt === 'out_of_stock', function ($q) {
            $q->where('is_suspended', false)
              ->where('quantity', '<=', 0);
        })
        ->when($statusFilt === 'active', function ($q) {
            $q->where('is_suspended', false)
              ->where('quantity', '>', 0);
        })

        // ✅ Expired filter
        ->when($expiryFilt === 'expired', function ($q) {
            $q->whereNotNull('expiry_date')
              ->where('expiry_date', '<', today());
        })

        // ✅ Expiring soon filter (next 30 days)
        ->when($expiryFilt === 'expiring', function ($q) {
            $q->whereNotNull('expiry_date')
              ->whereBetween('expiry_date', [
                  today(),
                  today()->addDays(30),
              ]);
        })

        ->orderBy('created_at', 'desc')
        ->paginate(10);

    // dropdowns
    $categories = Product::whereNotNull('category')
        ->where('category', '!=', '')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    $suppliers = Product::whereNotNull('supplier')
        ->where('supplier', '!=', '')
        ->distinct()
        ->orderBy('supplier')
        ->pluck('supplier');

    // ✅ don't read statuses from DB; define what you support
    $statuses = ['active', 'out_of_stock', 'suspended'];

    return view('product.products', [
        'products'     => $products,
        'search'       => $search,
        'categories'   => $categories,
        'suppliers'    => $suppliers,
        'statuses'     => $statuses,
        'categoryFilt' => $categoryFilt,
        'supplierFilt' => $supplierFilt,
        'statusFilt'   => $statusFilt,
        'expiryFilt'   => $expiryFilt,
    ]);
}



  public function store(Request $request)
{
    $request->validate([
        'sku'            => 'required|string|unique:products,sku',
        'name'           => 'required|string|max:255',
        'description'    => 'nullable|string',
        'category'       => 'nullable|string|max:255',
        'brand'          => 'nullable|string|max:255',
        'supplier'       => 'nullable|string|max:255',
        'barcode'        => 'nullable|string|max:255|unique:products,barcode',
        'expiry_date'    => 'nullable|date',
        'supply_date'    => 'nullable|date',
        'purchase_price' => 'required|numeric|min:0',
        'selling_price'  => 'required|numeric|min:0',
        'quantity'       => 'required|integer|min:0',
        'reorder_level'  => 'nullable|integer|min:0',
        'is_suspended'   => 'nullable|boolean',
        'is_vatable'     => 'nullable|boolean',
    ]);

    $data = $request->all();

    // normalize checkboxes
    $data['is_suspended'] = $request->has('is_suspended');
    $data['is_vatable'] = $request->boolean('is_vatable');


    // create Supplier record if new name
    if (!empty($data['supplier'])) {
        Supplier::firstOrCreate(['name' => $data['supplier']]);
    }

    // status logic
    // NEW – status no longer depends on quantity here
if ($data['is_suspended']) {
    $data['status'] = 'suspended';
} else {
    $data['status'] = 'active';
}


    $product = Product::create($data);

    if ($request->ajax()) {
        return response()->json([
            'message' => 'Product created successfully.',
            'product' => [
                'id'             => $product->id,
                'name'           => $product->name,
                'sku'            => $product->sku,
                'barcode'        => $product->barcode,
                'purchase_price' => $product->purchase_price,
                'selling_price'  => $product->selling_price,
                'label'          => $product->name . ' (' . $product->sku . ')',
            ],
        ]);
    }

    return redirect()
        ->route('admin.products.create')
        ->with('success', 'Product added successfully.');
}



   public function show(Product $product)
{
    // Eager load relations to avoid N+1
    $product->load([
        'saleItems',
        'purchaseItems.purchase.supplier',
    ]);

    // How many units have ever been purchased for this product
    $totalPurchased = $product->purchaseItems()->sum('quantity');

    // How many units have been sold
    $totalSold = $product->saleItems()->sum('qty');

    // Current stock from products table
    $currentStock = $product->quantity;

    // Optional: computed expected stock if you want (debug / consistency)
    $expectedStock = $totalPurchased - $totalSold;

    // Supply history (each line from purchase_items)
    $supplyHistory = $product->purchaseItems()
        ->with(['purchase.supplier'])
        ->orderByDesc('created_at')   // or orderByDesc on purchase_date if you prefer
        ->get();

    return view('product.show', [
        'product'        => $product,
        'totalPurchased' => $totalPurchased,
        'totalSold'      => $totalSold,
        'currentStock'   => $currentStock,
        'expectedStock'  => $expectedStock,
        'supplyHistory'  => $supplyHistory,
    ]);
}


  public function edit(Product $product)
{
    $categories = Product::distinct()->pluck('category')->filter()->toArray();
    
    // 🔹 suppliers from Supplier table
    $suppliers = Supplier::orderBy('name')->pluck('name')->toArray();

    return view('product.edit', compact('product', 'categories', 'suppliers'));
}

    public function update(Request $request, Product $product)
{
    $request->validate([
        'sku'            => 'required|string|unique:products,sku,' . $product->id,
        'name'           => 'required|string|max:255',
        'description'    => 'nullable|string',
        'category'       => 'nullable|string|max:255',
        'brand'          => 'nullable|string|max:255',
        'supplier'       => 'nullable|string|max:255',
        'barcode'        => 'nullable|string|max:255|unique:products,barcode,' . $product->id . ',id',
        'expiry_date'    => 'nullable|date',
        'supply_date'    => 'nullable|date',
        'purchase_price' => 'required|numeric|min:0',
        'selling_price'  => 'required|numeric|min:0',
        'quantity'       => 'required|integer|min:0',
        'reorder_level'  => 'nullable|integer|min:0',
        'is_suspended'   => 'nullable|boolean',
        'is_vatable'     => 'nullable|boolean',
    ]);

    $data = $request->all();
    $data['is_suspended'] = $request->has('is_suspended');
    $data['is_vatable']   = $request->has('is_vatable');

    if (!empty($data['supplier'])) {
        Supplier::firstOrCreate(['name' => $data['supplier']]);
    }

    // status logic
   // NEW – status no longer depends on quantity here
if ($data['is_suspended']) {
    $data['status'] = 'suspended';
} else {
    $data['status'] = 'active';
}


    $product->update($data);

    return redirect()
        ->route('admin.products')
        ->with('success', 'Product updated successfully.');
}

// public function refreshStatus()
// {
//     if ($this->is_suspended) {
//         $this->status = 'suspended';
//     } elseif ($this->quantity <= 0) {
//         $this->status = 'out_of_stock';
//     } else {
//         $this->status = 'active';
//     }
// }

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
