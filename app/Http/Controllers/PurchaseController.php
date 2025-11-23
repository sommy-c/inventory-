<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $supplierFilter = $request->query('supplier');
        $statusFilter   = $request->query('payment_status');

        $purchases = Purchase::with('supplier')
            ->when($supplierFilter, function ($q) use ($supplierFilter) {
                $q->whereHas('supplier', function ($sq) use ($supplierFilter) {
                    $sq->where('name', 'like', "%{$supplierFilter}%");
                });
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('payment_status', $statusFilter);
            })
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('purchases.index', compact('purchases', 'supplierFilter', 'statusFilter'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::orderBy('name')->get(); // for dropdown/autocomplete

        return view('purchases.create', compact('suppliers', 'products'));
    }

   public function store(Request $request)
{
    // 1) Base validation
    $data = $request->validate([
        'supplier_id'   => 'required|exists:suppliers,id',
        'purchase_date' => 'required|date',
        'reference'     => 'nullable|string|max:255',
        'notes'         => 'nullable|string',

        'items'               => 'required|array|min:1',

        // Either existing product_id OR new name/SKU
        'items.*.product_id'    => 'nullable|exists:products,id',
        'items.*.name'          => 'nullable|string|max:255', // we’ll enforce "either/or" manually
        'items.*.sku'           => 'nullable|string|max:255',

        'items.*.quantity'      => 'required|integer|min:1',
        'items.*.cost_price'    => 'required|numeric|min:0',
        'items.*.selling_price' => 'nullable|numeric|min:0',
        'items.*.expiry_date'   => 'nullable|date',

        'discount'    => 'nullable|numeric|min:0',
        'tax'         => 'nullable|numeric|min:0',
        'amount_paid' => 'nullable|numeric|min:0',
    ]);

    // 2) Extra per-row validation: either existing product OR new name/SKU
    $rowErrors = [];

    foreach ($data['items'] as $index => $item) {
        $hasExisting = !empty($item['product_id']);
        $hasNew      = !empty($item['name']) || !empty($item['sku']);

        if (!$hasExisting && !$hasNew) {
            $rowErrors["items.$index"] = "Row ".($index + 1).": select an existing product or enter a new product name/SKU.";
        }
    }

    if (!empty($rowErrors)) {
        if ($request->ajax()) {
            return response()->json(['errors' => $rowErrors], 422);
        }

        return back()->withErrors($rowErrors)->withInput();
    }

    // 🔔 3) Read notification settings ONCE
    $lowStockThreshold = (int) Setting::get('low_stock_threshold', 5);
    $notifyOnLowStock  = Setting::get('notify_on_low_stock', '1') === '1';
    $notifyEmail       = Setting::get('notify_admin_email');

    // We'll collect products that end up low-stock AFTER this purchase
    $lowStockProducts = [];

    // 4) Main transaction logic
    $purchase = DB::transaction(function () use ($data, $lowStockThreshold, $notifyOnLowStock, $notifyEmail, &$lowStockProducts) {
        $supplier = Supplier::findOrFail($data['supplier_id']);

        // Calculate subtotal from items
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $item['quantity'] * $item['cost_price'];
        }

        $discount = $data['discount'] ?? 0;
        $tax      = $data['tax'] ?? 0;
        $total    = $subtotal - $discount + $tax;
        $paid     = $data['amount_paid'] ?? 0;

        if ($total <= 0) {
            $total = 0;
        }

        // Determine payment status
        if ($paid >= $total && $total > 0) {
            $paymentStatus = 'paid';
        } elseif ($paid > 0 && $paid < $total) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        // Create purchase record
        $purchase = Purchase::create([
            'supplier_id'    => $supplier->id,
            'user_id'        => Auth::id(),
            'reference'      => $data['reference'] ?? null,
            'purchase_date'  => $data['purchase_date'],
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'tax'            => $tax,
            'total'          => $total,
            'amount_paid'    => $paid,
            'payment_status' => $paymentStatus,
            'notes'          => $data['notes'] ?? null,
        ]);

        // Create items + create/update product stock
        foreach ($data['items'] as $itemData) {

            // 1) Find existing product OR create a new one
            if (!empty($itemData['product_id'])) {
                $product = Product::findOrFail($itemData['product_id']);
            } else {
                $product = Product::create([
                    'sku'            => $itemData['sku'] ?? null,
                    'name'           => $itemData['name'],
                    'description'    => null,
                    'category'       => null,
                    'brand'          => null,
                    'supplier'       => $supplier->name,
                    'barcode'        => null,
                    'expiry_date'    => $itemData['expiry_date'] ?? null,
                    'supply_date'    => $data['purchase_date'],
                    'purchase_price' => $itemData['cost_price'],
                    'selling_price'  => $itemData['selling_price'] ?? 0,
                    'quantity'       => 0,
                    'reorder_level'  => 0,
                    'is_suspended'   => false,
                    'status'         => 'active',
                ]);
            }

            // 2) Line total
            $lineTotal = $itemData['quantity'] * $itemData['cost_price'];

            // 3) Create PurchaseItem
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id'  => $product->id,
                'quantity'    => $itemData['quantity'],
                'cost_price'  => $itemData['cost_price'],
                'line_total'  => $lineTotal,
                'expiry_date' => $itemData['expiry_date'] ?? null,
            ]);

            // 4) Update product stock & purchasing info
            $product->quantity        += $itemData['quantity'];
            $product->purchase_price   = $itemData['cost_price']; // last cost

            if (isset($itemData['selling_price']) && $itemData['selling_price'] !== null) {
                $product->selling_price = $itemData['selling_price'];
            }

            $product->supply_date = $data['purchase_date'];
            $product->supplier    = $supplier->name;
            $product->expiry_date = $itemData['expiry_date'] ?? $product->expiry_date;
            $product->status      = $product->quantity > 0 ? 'active' : 'out_of_stock';
            $product->save();

            // 🔔 Check if this product should trigger a low-stock notification
            if (
                $notifyOnLowStock &&
                $notifyEmail &&
                $lowStockThreshold > 0 &&
                $product->quantity <= $lowStockThreshold
            ) {
                // collect it (fresh copy)
                $lowStockProducts[] = $product->fresh();
            }
        }

        return $purchase;
    });

    // 🔔 5) Send low-stock email AFTER transaction
    if (!empty($lowStockProducts) && $notifyOnLowStock && $notifyEmail) {
        $lines = [];
        foreach ($lowStockProducts as $prod) {
            $lines[] = sprintf(
                '%s (SKU: %s) — Qty: %d',
                $prod->name,
                $prod->sku ?? 'N/A',
                $prod->quantity
            );
        }

        $body  = "The following products are at or below the low stock threshold ({$lowStockThreshold}):\n\n";
        $body .= implode("\n", $lines);
        $body .= "\n\nThis alert was generated automatically by the inventory system.";

        Mail::raw($body, function ($message) use ($notifyEmail) {
            $message->to($notifyEmail)
                    ->subject('Low Stock Alert');
        });
    }

    // 6) AJAX response
    if ($request->ajax()) {
        $purchase->load('supplier');

        $balance = $purchase->total - $purchase->amount_paid;

        return response()->json([
            'message' => 'Purchase recorded successfully.',
            'purchase' => [
                'id'            => $purchase->id,
                'supplier_name' => $purchase->supplier->name,
                'purchase_date' => $purchase->purchase_date->format('Y-m-d'),
                'reference'     => $purchase->reference,
                'subtotal'      => $purchase->subtotal,
                'discount'      => $purchase->discount,
                'tax'           => $purchase->tax,
                'total'         => $purchase->total,
                'amount_paid'   => $purchase->amount_paid,
                'balance'       => $balance,
                'payment_status'=> $purchase->payment_status,
            ],
        ]);
    }

    // 7) Non-AJAX fallback
    return redirect()
        ->route('admin.purchases.index')
        ->with('success', 'Purchase recorded successfully.');
}

    public function show(Request $request, Purchase $purchase)
    {
        $purchase->load('supplier', 'items.product', 'user');

        if ($request->ajax()) {
            $balance = $purchase->total - $purchase->amount_paid;

            return response()->json([
                'purchase' => [
                    'id'             => $purchase->id,
                    'supplier_name'  => $purchase->supplier->name,
                    'purchase_date'  => optional($purchase->purchase_date)->format('Y-m-d'),
                    'reference'      => $purchase->reference,
                    'subtotal'       => $purchase->subtotal,
                    'discount'       => $purchase->discount,
                    'tax'            => $purchase->tax,
                    'total'          => $purchase->total,
                    'amount_paid'    => $purchase->amount_paid,
                    'balance'        => $balance,
                    'payment_status' => $purchase->payment_status,
                    'notes'          => $purchase->notes,
                    'created_by'     => optional($purchase->user)->name,
                    'items'          => $purchase->items->map(function ($item) {
                        return [
                            'product_name' => $item->product->name ?? 'Deleted product',
                            'sku'          => $item->product->sku ?? null,
                            'quantity'     => $item->quantity,
                            'cost_price'   => $item->cost_price,
                            'line_total'   => $item->line_total,
                            'expiry_date'  => $item->expiry_date
                                ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d')
                                : null,
                        ];
                    })->toArray(),
                ],
            ]);
        }

        // Not AJAX? Just go back to the index page.
        return redirect()->route('admin.purchases.index');
    }
}
