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

    // 🔹 Configurable low-stock threshold
    $lowThreshold = (int) Setting::get('low_stock_threshold', 10);

    // 🔹 Today's Total Sales (COMPLETED)
    $todaySales = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->whereDate('sales.created_at', today())
        ->where('sales.status', 'completed')
        ->select(DB::raw('SUM(sale_items.qty * sale_items.price) AS total'))
        ->value('total') ?? 0;

    // 🔹 Today's Profit (COMPLETED)
    $todayProfit = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->whereDate('sales.created_at', today())
        ->where('sales.status', 'completed')
        ->select(DB::raw('SUM((sale_items.price - products.purchase_price) * sale_items.qty) AS profit'))
        ->value('profit') ?? 0;

    // 🔹 Low Stock Count (for widget)
    $lowStockCount = Product::where('quantity', '<=', $lowThreshold)
        ->where('quantity', '>', 0)
        ->count();

    // 🔹 Held Sales Count (for cashier widget)
    $heldSalesCount = Sale::where('status', 'paused')->count();

    // 🔹 Top Selling Product (COMPLETED only)
    $topRow = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->where('sales.status', 'completed')
        ->select('sale_items.product_id', DB::raw('SUM(sale_items.qty) as soldQty'))
        ->groupBy('sale_items.product_id')
        ->orderByDesc('soldQty')
        ->first();

    $topProduct = null;
    if ($topRow) {
        $topProduct = Product::find($topRow->product_id);
        if ($topProduct) {
            $topProduct->soldQty = $topRow->soldQty;
        }
    }

    // 🔹 Recent Sales (last 5)
    $recentSales = Sale::with('user')
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();

    // 🔹 Sales for last 7 days chart (COMPLETED only)
    $rawChart = Sale::select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('SUM(total) as total')
        )
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    $chartDays   = collect();
    $chartTotals = collect();

    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->toDateString();
        $chartDays->push($date);

        $total = $rawChart->firstWhere('day', $date)->total ?? 0;
        $chartTotals->push($total);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW: STOCK STATUS PIE (Healthy / Low / Out / Suspended)
    |--------------------------------------------------------------------------
    */
    $stockHealthy = Product::where('is_suspended', false)
        ->where('quantity', '>', $lowThreshold)
        ->count();

    $stockLow = Product::where('is_suspended', false)
        ->where('quantity', '>', 0)
        ->where('quantity', '<=', $lowThreshold)
        ->count();

    $stockOut = Product::where('is_suspended', false)
        ->where('quantity', '<=', 0)
        ->count();

    $stockSuspended = Product::where('is_suspended', true)->count();

    $stockStatusLabels = ['Healthy', 'Low stock', 'Out of stock', 'Suspended'];
    $stockStatusData   = [
        $stockHealthy,
        $stockLow,
        $stockOut,
        $stockSuspended,
    ];

    /*
    |--------------------------------------------------------------------------
    | Sales by Payment Method (LAST 7 DAYS)
    |--------------------------------------------------------------------------
    */
    $paymentWeekRaw = Sale::select('payment_method', DB::raw('SUM(total) as total'))
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->where('status', 'completed')
        ->groupBy('payment_method')
        ->get();

    if ($paymentWeekRaw->isEmpty()) {
        $paymentWeekLabels = ['No data'];
        $paymentWeekData   = [1];
    } else {
        $paymentWeekLabels = $paymentWeekRaw->pluck('payment_method')->map(function ($m) {
            return $m ?: 'Unknown';
        })->toArray();
        $paymentWeekData   = $paymentWeekRaw->pluck('total')->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Sales by Category (LAST 30 DAYS)
    |--------------------------------------------------------------------------
    */
    $categoryRaw = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->where('sales.status', 'completed')
        ->where('sales.created_at', '>=', now()->subDays(29)->startOfDay())
        ->groupBy('products.category')
        ->select(
            'products.category as category',
            DB::raw('SUM(sale_items.qty * sale_items.price) as total')
        )
        ->get();

    if ($categoryRaw->isEmpty()) {
        $categoryLabels = ['No data'];
        $categoryData   = [1];
    } else {
        $categoryLabels = $categoryRaw->pluck('category')->map(function ($c) {
            return $c ?: 'Uncategorized';
        })->toArray();
        $categoryData   = $categoryRaw->pluck('total')->toArray();
    }

    return view('dashboard', [
        'todaySales'          => $todaySales,
        'todayProfit'         => $todayProfit,
        'lowStockCount'       => $lowStockCount,
        'heldSalesCount'      => $heldSalesCount,
        'topProduct'          => $topProduct,
        'recentSales'         => $recentSales,
        'chartDays'           => $chartDays,
        'chartTotals'         => $chartTotals,

        // stock chart
        'stockStatusLabels'   => $stockStatusLabels,
        'stockStatusData'     => $stockStatusData,

        // pies
        'paymentWeekLabels'   => $paymentWeekLabels,
        'paymentWeekData'     => $paymentWeekData,
        'categoryLabels'      => $categoryLabels,
        'categoryData'        => $categoryData,
    ]);
    [$categoryLabels, $categoryData] = $this->buildCategoryChartData();

    return view('admin.dashboard', [
        // existing data...
        'todaySales'        => $todaySales,
        'todayProfit'       => $todayProfit,
        'lowStockCount'     => $lowStockCount,
        'heldSalesCount'    => $heldSalesCount,
        'chartDays'         => $chartDays,
        'chartTotals'       => $chartTotals,
        'stockStatusLabels' => $stockStatusLabels,
        'stockStatusData'   => $stockStatusData,

        // 🔥 category chart data (all categories, even 0)
        'categoryLabels'    => $categoryLabels,
        'categoryData'      => $categoryData,

        // payment week etc...
        'paymentWeekLabels' => $paymentWeekLabels,
        'paymentWeekData'   => $paymentWeekData,
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


protected function buildCategoryChartData(): array
{
    // 1) Get ALL categories from products table
    $allCategories = Product::query()
        ->select('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category')
        ->map(function ($cat) {
            return $cat ?: 'Uncategorized';
        })
        ->unique()
        ->values();  // eg: ["Beverages", "Snacks", "Toiletries", "Uncategorized"]

    // 2) Get totals per category from sales (last 30 days)
    $rawTotals = SaleItem::query()
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->where('sales.status', 'completed')
        ->where('sales.created_at', '>=', now()->subDays(30))
        ->groupBy('products.category')
        ->selectRaw(
    'COALESCE(products.category, "Uncategorized") as category, ' .
    'SUM(sale_items.qty * sale_items.price) as total'
)

        ->pluck('total', 'category'); 
        // ex: ["Toiletries" => 20000, "Uncategorized" => 5000]

    // 3) Build labels + data, forcing 0 where no sales
    $labels = [];
    $data   = [];

    foreach ($allCategories as $category) {
        $labels[] = $category;
        $data[]   = (float) ($rawTotals[$category] ?? 0);  // default 0
    }

    // If you want to also include categories that only exist in old sales
    // but not in current products, you could merge $rawTotals keys too.

    return [$labels, $data];
}



  public function store(Request $request)
{
    // 1) Validate ONLY the fields we really accept
    $validated = $request->validate([
        'sku'            => 'required|string|unique:products,sku',
        'name'           => 'required|string|max:255',
        'description'    => 'nullable|string',
        'category'       => 'nullable|string|max:255',
        'brand'          => 'nullable|string|max:255',
        'supplier'       => 'nullable|string|max:255',
        'barcode'        => 'nullable|string|max:255|unique:products,barcode',
        'expiry_date'    => 'nullable|date',
        'supply_date'    => 'nullable|date',

        // 👇 purchase_price is nullable, but we will override it to 0
        'purchase_price' => 'nullable|numeric|min:0',

        // 👇 keep selling_price required – you still need a selling price!
        'selling_price'  => 'nullable|numeric|min:0',

        // 👇 stock & reorder can be null, we will set defaults to 0
        'quantity'       => 'nullable|integer|min:0',
        'reorder_level'  => 'nullable|integer|min:0',

        'is_suspended'   => 'nullable|boolean',
        'is_vatable'     => 'nullable|boolean',
    ]);

    // 2) Start with validated only (avoid _token, etc.)
    $data = $validated;

    // 3) Normalize checkboxes
   $data['is_suspended'] = $request->boolean('is_suspended');
    $data['is_vatable']   = $request->boolean('is_vatable');

    // 4) Force stock & cost to be controlled by purchases only
    $data['purchase_price'] = 0;                            // ✅ manual product has cost 0
    $data['quantity']       = 0;                            // ✅ no stock until purchase
    $data['reorder_level']  = $data['reorder_level'] ?? 0;  // default 0 if empty
    $data['selling_price'] = $validated['selling_price'] ?? 0;


    // 5) Supplier table sync
    if (!empty($data['supplier'])) {
        Supplier::firstOrCreate(['name' => $data['supplier']]);
    }

    // 6) Status logic
    if ($data['is_suspended']) {
        $data['status'] = 'suspended';
    } else {
        $data['status'] = 'active';
    }

    // 7) Create product
    $product = Product::create($data);

    // 8) AJAX response for your modal
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

    // 9) Normal form submit fallback
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
        'selling_price'  => 'nullable|numeric|min:0',
        'quantity' => 'nullable|integer|min:0',

        'reorder_level'  => 'nullable|integer|min:0',
        'is_suspended'   => 'nullable|boolean',
        'is_vatable'     => 'nullable|boolean',
    ]);

    $data = $request->all();
$data['is_suspended'] = $request->boolean('is_suspended'); // 👈 FIX
$data['is_vatable']   = $request->boolean('is_vatable');


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

public function json(Product $product)
{
    if ($product->is_suspended || $product->status === 'suspended') {
        return response()->json([
            'error' => 'This product is suspended and cannot be sold.',
        ], 422);
    }

    if ($product->quantity <= 0) {
        return response()->json([
            'error' => 'Product is out of stock.',
        ], 422);
    }

    return response()->json([
        'id'          => $product->id,
        'name'        => $product->name,
        'sku'         => $product->sku,
        'barcode'     => $product->barcode,
        'quantity'    => $product->quantity,
        'is_vatable'  => (bool) $product->is_vatable,
        'is_suspended'=> (bool) $product->is_suspended,
        'status'      => $product->status,
        'selling_price' => $product->selling_price,
    ]);
}

}
