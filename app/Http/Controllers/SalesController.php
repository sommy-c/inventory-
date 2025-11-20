<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


class SalesController extends Controller
{
   public function pos()
    {
        $customers = Customer::orderBy('name')->get();
        return view('pos', compact('customers'));
    }

    public function addToCart(Request $request)
    {
        $request->validate(['search' => 'required|string']);

        $product = Product::where('barcode', $request->search)
            ->orWhere('sku', $request->search)
            ->orWhere('name', 'LIKE', "%{$request->search}%")
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        if ($product->quantity <= 0) {
            return response()->json(['error' => 'OUT OF STOCK'], 422);
        }

        return response()->json([
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => $product->selling_price,
            'barcode' => $product->barcode,
            'quantity' => $product->quantity,
        ]);
    }

    public function searchProducts(Request $request)
    {
        $q = $request->get('name');
        if (!$q) return response()->json([]);

        return Product::where('name', 'like', "%$q%")
            ->orWhere('sku', 'like', "%$q%")
            ->orWhere('barcode', 'like', "%$q%")
            ->limit(10)
            ->get(['id','name','sku','selling_price','quantity']);
    }

    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $customer = Customer::create($data);

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.sku' => 'required|string',
            'items.*.name' => 'required|string',
            'payment_method' => 'required|string',
            'amount_paid' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
        ]);

        // STOCK VALIDATION
        foreach ($request->items as $item) {
            $product = Product::find($item['id']);

            if ($item['qty'] > $product->quantity) {
                return response()->json([
                    'error' => "{$product->name} has only {$product->quantity} left."
                ], 422);
            }
        }

        $subtotal = collect($request->items)
            ->sum(fn($item) => $item['qty'] * $item['price']);

        $discount = $request->discount ?? 0;
        $fee = $request->fee ?? 0;
        $total = $subtotal - $discount + $fee;
        $change = $request->amount_paid - $total;

        $sale = Sale::create([
            'user_id' => Auth::id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'fee' => $fee,
            'total' => $total,
            'amount_paid' => $request->amount_paid,
            'change' => $change,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['id']);
            $product->quantity -= $item['qty'];
            $product->save();

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['id'],
                'sku' => $item['sku'],
                'name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['qty'] * $item['price'],
            ]);
        }

        return response()->json([
            'success' => 'Sale completed successfully',
            'sale_id' => $sale->id,
            'redirect_url' => route('admin.sales.print', $sale->id),
        ]);
    }

    public function pause(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.price'=> 'required|numeric|min:0',
            'items.*.sku' => 'required|string',
            'items.*.name' => 'required|string',
            'hold_number' => 'required|string|max:50',
        ]);

        $subtotal = collect($request->items)
            ->sum(fn($item) => $item['qty'] * $item['price']);

        $sale = Sale::create([
            'user_id' => Auth::id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'subtotal' => $subtotal,
            'discount' => 0,
            'fee' => 0,
            'total' => $subtotal,
            'payment_method' => 'cash',
            'status' => 'paused',
            'hold_number' => $request->hold_number,
        ]);

        foreach ($request->items as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['id'],
                'sku' => $item['sku'],
                'name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['qty'] * $item['price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id
        ]);
    }

    public function resume($sale)
    {
        return Sale::with('items')
            ->where('id', $sale)
            ->where('status', 'paused')
            ->firstOrFail();
    }

    public function heldSales()
    {
        return Sale::where('status', 'paused')
            ->orderByDesc('created_at')
            ->get(['id','hold_number','customer_name','total','created_at']);
    }

    public function destroyHeld(Sale $sale)
    {
        if ($sale->status !== 'paused') {
            return response()->json(['error' => 'Only held sales can be deleted'], 422);
        }

        $sale->items()->delete();
        $sale->delete();

        return response()->json(['success' => true]);
    }

    // public function print($id)
    // {
    //     $sale = Sale::with('items')->findOrFail($id);
    //     return view('sales.print', compact('sale'));
    // }

    // ============================================================
// SALES REPORT (LIST + FILTERS)
// ============================================================
   
// ... other uses

public function index(Request $request)
{
    // Raw filter inputs
    $fromDate       = $request->input('from_date');
    $toDate         = $request->input('to_date');
    $cashierInput   = $request->input('cashier');   // what user typed
    $productInput   = $request->input('product');   // product name / sku
    $paymentMethod  = $request->input('payment_method'); // exact value
    $customerInput  = $request->input('customer');  // name or phone

    $cashierFilter  = null;                         // what we actually use to filter
    $perPage        = 20;
    $errorMessage   = null;

    // -------------------------------------------------
    // VALIDATE CASHIER NAME (if provided)
    // -------------------------------------------------
    if (!empty($cashierInput)) {
        $cashierExists = User::where('name', $cashierInput)->exists();

        if ($cashierExists) {
            $cashierFilter = $cashierInput;
        } else {
            $errorMessage = "No cashier or manager found with the name \"{$cashierInput}\".";
        }
    }

    // -------------------------------------------------
    // MAIN LIST QUERY (DATE + CASHIER + OTHER FILTERS)
    //  -> exclude paused sales
    // -------------------------------------------------
    $query = Sale::with('items', 'user')
        ->where('status', '!=', 'paused')
        ->orderByDesc('created_at');

    // Date filters (still date-only)
    if (!empty($fromDate)) {
        $query->whereDate('created_at', '>=', $fromDate);
    }

    if (!empty($toDate)) {
        $query->whereDate('created_at', '<=', $toDate);
    }

    // Cashier / seller filter (only if we found a match)
    if (!empty($cashierFilter)) {
        $query->whereHas('user', function ($q) use ($cashierFilter) {
            $q->where('name', $cashierFilter);
        });
    }

    // Product filter (by sale items name / sku)
    if (!empty($productInput)) {
        $query->whereHas('items', function ($q) use ($productInput) {
            $q->where('name', 'like', '%' . $productInput . '%')
              ->orWhere('sku', 'like', '%' . $productInput . '%');
        });
    }

    // Payment method filter (exact match)
    if (!empty($paymentMethod)) {
        $query->where('payment_method', $paymentMethod);
    }

    // Customer filter (name or phone)
    if (!empty($customerInput)) {
        $query->where(function ($q) use ($customerInput) {
            $q->where('customer_name', 'like', '%' . $customerInput . '%')
              ->orWhere('customer_phone', 'like', '%' . $customerInput . '%');
        });
    }

    // -------------------------------------------------
    // SUMMARY FOR CURRENT FILTER (already based on $query)
    // -------------------------------------------------
    $summaryQuery  = clone $query;
    $totalSales    = $summaryQuery->sum('total');
    $totalDiscount = (clone $summaryQuery)->sum('discount');
    $totalFee      = (clone $summaryQuery)->sum('fee');
    $countSales    = (clone $summaryQuery)->count();

    // -------------------------------------------------
    // "ACTUAL" TOTALS FOR DATE RANGE (IGNORE OTHER FILTERS)
    //  -> exclude paused, but ignore cashier/product/payment/customer
    // -------------------------------------------------
    $overallQuery = Sale::query()
        ->where('status', '!=', 'paused');

    if (!empty($fromDate)) {
        $overallQuery->whereDate('created_at', '>=', $fromDate);
    }

    if (!empty($toDate)) {
        $overallQuery->whereDate('created_at', '<=', $toDate);
    }

    $overallTotal = $overallQuery->sum('total');
    $overallCount = (clone $overallQuery)->count();

    // -------------------------------------------------
    // CASHIER-SPECIFIC TOTAL (WITH DATE, IGNORE OTHER FILTERS)
    // -------------------------------------------------
    $cashierTotal = null;
    $cashierCount = null;

    if (!empty($cashierFilter)) {
        $cashierQuery = clone $overallQuery;

        $cashierQuery->whereHas('user', function ($q) use ($cashierFilter) {
            $q->where('name', $cashierFilter);
        });

        $cashierTotal = $cashierQuery->sum('total');
        $cashierCount = (clone $cashierQuery)->count();
    }

    // -------------------------------------------------
    // PAGINATED LIST (CURRENT FILTERS)
    // -------------------------------------------------
    $sales = $query->paginate($perPage)->appends($request->query());

    return view('sales.index', [
        'sales'         => $sales,
        'fromDate'      => $fromDate,
        'toDate'        => $toDate,

        // what is REALLY used to filter by cashier
        'cashier'       => $cashierFilter,

        // what user typed (for the input value)
        'cashierInput'  => $cashierInput,

        // new filters
        'productInput'  => $productInput,
        'paymentMethod' => $paymentMethod,
        'customerInput' => $customerInput,

        'totalSales'    => $totalSales,
        'totalDiscount' => $totalDiscount,
        'totalFee'      => $totalFee,
        'countSales'    => $countSales,
        'overallTotal'  => $overallTotal,
        'overallCount'  => $overallCount,
        'cashierTotal'  => $cashierTotal,
        'cashierCount'  => $cashierCount,
        'errorMessage'  => $errorMessage,
    ]);
}


public function exportPdf(Request $request)
{
    $fromDate      = $request->input('from_date');
    $toDate        = $request->input('to_date');
    $cashierInput  = $request->input('cashier');
    $customerInput = $request->input('customer');
    $productInput  = $request->input('product');
    $paymentMethod = $request->input('payment_method');

    $cashierFilter = null;

    if (!empty($cashierInput)) {
        // we assume by the time you export, cashier already exists
        $cashierFilter = $cashierInput;
    }

    $query = Sale::with('items', 'user')
        ->where('status', '!=', 'paused')
        ->orderByDesc('created_at');

    if (!empty($fromDate)) {
        $query->whereDate('created_at', '>=', $fromDate);
    }
    if (!empty($toDate)) {
        $query->whereDate('created_at', '<=', $toDate);
    }

    if (!empty($cashierFilter)) {
        $query->whereHas('user', function ($q) use ($cashierFilter) {
            $q->where('name', $cashierFilter);
        });
    }

    if (!empty($customerInput)) {
        $query->where(function ($q) use ($customerInput) {
            $q->where('customer_name', 'like', '%' . $customerInput . '%')
              ->orWhere('customer_phone', 'like', '%' . $customerInput . '%');
        });
    }

    if (!empty($productInput)) {
        $query->whereHas('items', function ($q) use ($productInput) {
            $q->where('name', 'like', '%' . $productInput . '%')
              ->orWhere('sku', 'like', '%' . $productInput . '%');
        });
    }

    if (!empty($paymentMethod)) {
        $query->where('payment_method', $paymentMethod);
    }

    $sales      = $query->get();
    $totalSales = $sales->sum('total');
    $countSales = $sales->count();

    $pdf = Pdf::loadView('sales.export-pdf', [
        'sales'         => $sales,
        'fromDate'      => $fromDate,
        'toDate'        => $toDate,
        'cashier'       => $cashierFilter,
        'customerInput' => $customerInput,
        'productInput'  => $productInput,
        'paymentMethod' => $paymentMethod,
        'totalSales'    => $totalSales,
        'countSales'    => $countSales,
    ]);

    $filename = 'sales-report-' . now()->format('Ymd_His') . '.pdf';

    return $pdf->download($filename);
}


// ============================================================
// PRINT (for POS redirect + AJAX from report page)
// ============================================================
public function print(Request $request, $id)
{
    $sale = Sale::with('items', 'user')->findOrFail($id);

    // AJAX call from Sales Report: /admin/sales/{id}/print?raw=1
    if ($request->has('raw')) {
        // you can use a dedicated partial if you want: 'sales._receipt'
        $html = view('sales.print', compact('sale'))->render();

        return response()->json([
            'html' => $html,
        ]);
    }

    // Direct browser visit (POS redirect): /admin/sales/{id}/print
    return view('sales.print', compact('sale'));
}

// ============================================================
// DETAILS (used by row click in the report to show modal)
// ============================================================
public function details($id)
{
    $sale = Sale::with('items', 'user')->findOrFail($id);

    return response()->json([
        'id'              => $sale->id,
        'date_time'       => $sale->created_at->format('Y-m-d H:i'),
        'status'          => $sale->status,
        'cashier'         => optional($sale->user)->name,
        'payment_method'  => $sale->payment_method,
        'customer_name'   => $sale->customer_name,
        'customer_phone'  => $sale->customer_phone,
        'customer_email'  => $sale->customer_email,
        'subtotal'        => $sale->subtotal,
        'discount'        => $sale->discount,
        'fee'             => $sale->fee,
        'total'           => $sale->total,
        'amount_paid'     => $sale->amount_paid,
        'change'          => $sale->change,

        'items' => $sale->items->map(function ($item) {
            return [
                'name'  => $item->name,
                'sku'   => $item->sku,
                'qty'   => $item->qty,
                'price' => $item->price,
                'total' => $item->subtotal,
            ];
        })->values()->toArray(),
    ]);

}

}

