<?php

namespace App\Http\Controllers;

use App\Mail\DailySummaryMail;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Setting; // 🔹 for VAT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\LowStockAlertMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB; // 🔹 added if you want transaction

class SalesController extends Controller
{
    public function pos()
    {
        $customers = Customer::orderBy('name')->get();
        return view('pos', compact('customers'));
    }

    public function addToCart(Request $request)
    {
        $search = $request->input('search');

        $product = Product::query()
            ->where(function ($q) use ($search) {
                $q->where('sku', $search)
                  ->orWhere('barcode', $search)
                  ->orWhere('name', 'like', "%{$search}%");
            })
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // 🔴 BLOCK suspended here
        if ($product->is_suspended || $product->status === 'suspended') {
            return response()->json([
                'error' => 'This product is suspended and cannot be sold.'
            ], 422);
        }

        if ($product->quantity <= 0) {
            return response()->json([
                'error' => 'Product is out of stock.'
            ], 422);
        }

        return response()->json($product);
    }

    public function searchProducts(Request $request)
    {
        $name = $request->query('name');

        $products = Product::query()
            ->where(function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%")
                  ->orWhere('sku', 'like', "%{$name}%")
                  ->orWhere('barcode', 'like', "%{$name}%");
            })
            ->where('is_suspended', false)          // 🔴 block suspended
            ->where('status', '!=', 'suspended')    // (extra safety)
            ->where('quantity', '>', 0)
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
        ]);

        $customer = Customer::create($data);

        return response()->json([
            'success'  => true,
            'customer' => $customer
        ]);
    }

    /**
     * Finalize sale (POS checkout) with VAT logic.
     */
    public function checkout(Request $request)
{
    $request->validate([
        'items'              => 'required|array|min:1',
        'items.*.id'         => 'required|integer|exists:products,id',
        'items.*.qty'        => 'required|numeric|min:1',
        'items.*.price'      => 'required|numeric|min:0',
        'items.*.sku'        => 'required|string',
        'items.*.name'       => 'required|string',
        'payment_method'     => 'required|string',
        'amount_paid'        => 'required|numeric|min:0',
        'customer_name'      => 'nullable|string|max:255',
        'customer_phone'     => 'nullable|string|max:50',
        'customer_email'     => 'nullable|email|max:255',
        'discount'           => 'nullable|numeric|min:0',
        'fee'                => 'nullable|numeric|min:0',
    ]);

    $items = $request->input('items', []);

    // VAT from settings
    $vatRate    = Setting::vatRate();    // e.g. 0.075
    $vatPercent = Setting::vatPercent(); // e.g. 7.5

    $subtotal        = 0; // all items
    $vatableSubtotal = 0; // only VATable items

    /**
     * First pass:
     * - Make sure all products exist
     * - Block suspended
     * - Basic stock validation
     * - Compute subtotals (no DB writes yet)
     */
    foreach ($items as $item) {
        $product = Product::find($item['id']);

        if (!$product) {
            return response()->json([
                'error' => 'Product not found.',
            ], 422);
        }

        // Block suspended here too
        if ($product->is_suspended || $product->status === 'suspended') {
            return response()->json([
                'error' => "Product {$product->name} is suspended and cannot be sold.",
            ], 422);
        }

        if ($item['qty'] > $product->quantity) {
            return response()->json([
                'error' => "{$product->name} has only {$product->quantity} left.",
            ], 422);
        }

        $lineNet = $item['qty'] * $item['price']; // price assumed pre-VAT
        $subtotal += $lineNet;

        if ($product->is_vatable) {
            $vatableSubtotal += $lineNet;
        }
    }

    $discount  = $request->discount ?? 0;
    $fee       = $request->fee ?? 0;
    $vatAmount = round($vatableSubtotal * $vatRate, 2);

    $total = $subtotal - $discount + $fee + $vatAmount;
    if ($total < 0) {
        $total = 0; // safety – don’t allow negative totals
    }

    $change = $request->amount_paid - $total;

    try {
        $sale = DB::transaction(function () use (
            $request,
            $subtotal,
            $vatAmount,
            $vatPercent,
            $discount,
            $fee,
            $total,
            $change,
            $items
        ) {
            // Create sale
            $sale = Sale::create([
                'user_id'        => Auth::id(),
                'customer_name'  => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'subtotal'       => $subtotal,
                'vat_amount'     => $vatAmount,
                'vat_rate'       => $vatPercent,  // store percentage used
                'discount'       => $discount,
                'fee'            => $fee,
                'total'          => $total,
                'amount_paid'    => $request->amount_paid,
                'change'         => $change,
                'payment_method' => $request->payment_method,
                'status'         => 'completed',
            ]);

            // Save sale items + update stock (with row lock)
            foreach ($items as $item) {
                /** @var Product|null $product */
                $product = Product::lockForUpdate()->find($item['id']);

                if (!$product) {
                    throw new \RuntimeException("Product ID {$item['id']} not found during checkout.");
                }

                // Re-check suspended status inside the lock
                if ($product->is_suspended || $product->status === 'suspended') {
                    throw new \RuntimeException("Product {$product->name} is suspended and cannot be sold.");
                }

                // Re-check stock with the locked row
                if ($item['qty'] > $product->quantity) {
                    throw new \RuntimeException("{$product->name} has only {$product->quantity} left.");
                }

                // Reduce stock
                $product->quantity -= $item['qty'];

                // Auto status update based on quantity (but don’t override suspended)
                if (!$product->is_suspended) {
                    $product->status = $product->quantity > 0 ? 'active' : 'out_of_stock';
                }

                $product->save();

                $lineNet = $item['qty'] * $item['price'];

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['id'],
                    'sku'        => $item['sku'],
                    'name'       => $item['name'],
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],      // per unit (pre-VAT)
                    'subtotal'   => $lineNet,            // line total (pre-VAT)
                ]);
            }

            return $sale;
        });
    } catch (\Throwable $e) {
        // If anything fails inside the transaction, return a clean JSON error
        return response()->json([
            'error' => $e->getMessage(),
        ], 422);
    }

    return response()->json([
        'success'      => 'Sale completed successfully',
        'sale_id'      => $sale->id,
        'redirect_url' => route('admin.sales.print', $sale->id),
    ]);
}

    /**
     * Pause a sale (hold) – still calculate VAT so total is accurate.
     * ⚠️ Notice: NO stock changes here.
     */
    public function pause(Request $request)
    {
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.id'      => 'required|integer|exists:products,id',
            'items.*.qty'     => 'required|numeric|min:1',
            'items.*.price'   => 'required|numeric|min:0',
            'items.*.sku'     => 'required|string',
            'items.*.name'    => 'required|string',
            'hold_number'     => 'required|string|max:50',
            'customer_name'   => 'nullable|string|max:255',
            'customer_phone'  => 'nullable|string|max:50',
            'customer_email'  => 'nullable|email|max:255',
        ]);

        $items = $request->items;

        $vatRate    = Setting::vatRate();
        $vatPercent = Setting::vatPercent();

        $subtotal        = 0;
        $vatableSubtotal = 0;
foreach ($items as $item) {
    $product = Product::find($item['id']);

    if (!$product) {
        return response()->json(['error' => 'Product not found'], 422);
    }

    if ($product->is_suspended || $product->status === 'suspended') {
        return response()->json([
            'error' => "Product {$product->name} is suspended and cannot be sold or held."
        ], 422);
    }

    if ($item['qty'] > $product->quantity) {
        return response()->json([
            'error' => "{$product->name} has only {$product->quantity} left."
        ], 422);
    }

    $lineNet = $item['qty'] * $item['price'];
    $subtotal += $lineNet;

    if ($product->is_vatable) {
        $vatableSubtotal += $lineNet;
    }
}


        $vatAmount = round($vatableSubtotal * $vatRate, 2);
        $total     = $subtotal + $vatAmount; // no discount/fee for paused

        $sale = Sale::create([
            'user_id'        => Auth::id(),
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'subtotal'       => $subtotal,
            'vat_amount'     => $vatAmount,
            'vat_rate'       => $vatPercent,
            'discount'       => 0,
            'fee'            => 0,
            'total'          => $total,
            'payment_method' => 'cash',
            'status'         => 'paused',
            'hold_number'    => $request->hold_number,
        ]);

        foreach ($items as $item) {
            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $item['id'],
                'sku'        => $item['sku'],
                'name'       => $item['name'],
                'qty'        => $item['qty'],
                'price'      => $item['price'],
                'subtotal'   => $item['qty'] * $item['price'],
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

    // ============================================================
    // SALES REPORT (LIST + FILTERS)
    // ============================================================
    public function index(Request $request)
    {
        $fromDate       = $request->input('from_date');
        $toDate         = $request->input('to_date');
        $cashierInput   = $request->input('cashier');
        $productInput   = $request->input('product');
        $paymentMethod  = $request->input('payment_method');
        $customerInput  = $request->input('customer');

        $cashierFilter  = null;
        $perPage        = 20;
        $errorMessage   = null;

        if (!empty($cashierInput)) {
            $cashierExists = User::where('name', $cashierInput)->exists();

            if ($cashierExists) {
                $cashierFilter = $cashierInput;
            } else {
                $errorMessage = "No cashier or manager found with the name \"{$cashierInput}\".";
            }
        }

        // ✅ load product + latest purchase cost
        $query = Sale::with([
            'items.product',
            'items.product.purchaseItems' => function($q) {
                $q->latest(); // gets most recent cost price
            },
            'user'
        ])
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

        if (!empty($productInput)) {
            $query->whereHas('items', function ($q) use ($productInput) {
                $q->where('name', 'like', '%' . $productInput . '%')
                  ->orWhere('sku', 'like', '%' . $productInput . '%');
            });
        }

        if (!empty($paymentMethod)) {
            $query->where('payment_method', $paymentMethod);
        }

        if (!empty($customerInput)) {
            $query->where(function ($q) use ($customerInput) {
                $q->where('customer_name', 'like', '%' . $customerInput . '%')
                  ->orWhere('customer_phone', 'like', '%' . $customerInput . '%');
            });
        }

        // SUMMARY
        $summaryQuery   = clone $query;
        $totalSales     = $summaryQuery->sum('total');
        $totalDiscount  = (clone $summaryQuery)->sum('discount');
        $totalFee       = (clone $summaryQuery)->sum('fee');
        $totalVat       = (clone $summaryQuery)->sum('vat_amount');
        $countSales     = (clone $summaryQuery)->count();

        // ✅ compute TOTAL PROFIT (filtered)
        $summarySales = (clone $summaryQuery)->get();

        $totalProfit = $summarySales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                // get latest cost price from PurchaseItem
                $cost = optional($item->product->purchaseItems->first())->cost_price ?? 0;
                return ($item->price - $cost) * $item->qty;
            });
        });

        // PAGINATION + profit per row
        $sales = $query->paginate($perPage)->appends($request->query());

        $sales->getCollection()->transform(function ($sale) {
            $sale->profit = $sale->items->sum(function ($item) {
                $cost = optional($item->product->purchaseItems->first())->cost_price ?? 0;
                return ($item->price - $cost) * $item->qty;
            });
            return $sale;
        });

        return view('sales.index', [
            'sales'         => $sales,
            'fromDate'      => $fromDate,
            'toDate'        => $toDate,
            'cashier'       => $cashierFilter,
            'cashierInput'  => $cashierInput,
            'productInput'  => $productInput,
            'paymentMethod' => $paymentMethod,
            'customerInput' => $customerInput,
            'totalSales'    => $totalSales,
            'totalDiscount' => $totalDiscount,
            'totalFee'      => $totalFee,
            'totalVat'      => $totalVat,
            'totalProfit'   => $totalProfit,   // ✅ NOW AVAILABLE
            'countSales'    => $countSales,
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
        $totalVat   = $sales->sum('vat_amount'); // 🔹 VAT in exported report
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
            'totalVat'      => $totalVat,
            'countSales'    => $countSales,
        ]);

        $filename = 'sales-report-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    // ============================================================
    // PRINT (POS receipt)
    // ============================================================
    public function print(Request $request, $id)
    {
        $sale = Sale::with('items', 'user')->findOrFail($id);

        if ($request->has('raw')) {
            $html = view('sales.print', compact('sale'))->render();

            return response()->json([
                'html' => $html,
            ]);
        }

        return view('sales.print', compact('sale'));
    }

    // ============================================================
    // DETAILS (used by modal)
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
            'vat_amount'      => $sale->vat_amount,   // 🔹 include VAT
            'vat_rate'        => $sale->vat_rate,     // 🔹 %
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

    public function sendDailySummary()
    {
        $today = now()->toDateString();

        $totalSales = Sale::whereDate('created_at', $today)->sum('total');
        $totalVat   = Sale::whereDate('created_at', $today)->sum('vat_amount');
        $count      = Sale::whereDate('created_at', $today)->count();

        $adminEmail = Setting::get('notify_admin_email');

        if ($adminEmail) {
            Mail::to($adminEmail)->send(
                new DailySummaryMail($totalSales, $totalVat, $count, $today)
            );
        }
    }
}
