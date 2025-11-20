<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $data = $request->validate([
            'supplier_id'   => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference'     => 'nullable|string|max:255',
            'notes'         => 'nullable|string',

            'items'               => 'required|array|min:1',

            // Either existing product_id OR new name
            'items.*.product_id'   => 'nullable|exists:products,id',
            'items.*.name'         => 'required_without:items.*.product_id|string|max:255',
            'items.*.sku'          => 'nullable|string|max:255',

            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.cost_price'   => 'required|numeric|min:0',
            'items.*.selling_price'=> 'nullable|numeric|min:0',
            'items.*.expiry_date'  => 'nullable|date',

            'discount'      => 'nullable|numeric|min:0',
            'tax'           => 'nullable|numeric|min:0',
            'amount_paid'   => 'nullable|numeric|min:0',
        ]);

        $purchase = DB::transaction(function () use ($data) {
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
                    // Existing product
                    $product = Product::findOrFail($itemData['product_id']);
                } else {
                    // New product – minimal fields, can be edited later in Product screen
                    $product = Product::create([
                        'sku'            => $itemData['sku'] ?? null,
                        'name'           => $itemData['name'],   // required_without product_id
                        'description'    => null,
                        'category'       => null,
                        'brand'          => null,
                        'supplier'       => $supplier->name,
                        'barcode'        => null,
                        'expiry_date'    => $itemData['expiry_date'] ?? null,
                        'supply_date'    => $data['purchase_date'],
                        'purchase_price' => $itemData['cost_price'],
                        'selling_price'  => $itemData['selling_price'] ?? 0,
                        'quantity'       => 0,   // will increment below
                        'reorder_level'  => 0,
                        'is_suspended'   => false,
                        'status'         => 'active',
                    ]);
                }

                // 2) Line total for this item
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
                $product->purchase_price   = $itemData['cost_price'];           // last cost

                if (isset($itemData['selling_price']) && $itemData['selling_price'] !== null) {
                    $product->selling_price = $itemData['selling_price'];      // update selling
                }

                $product->supply_date      = $data['purchase_date'];
                $product->supplier         = $supplier->name;
                $product->expiry_date      = $itemData['expiry_date'] ?? $product->expiry_date;
                $product->status           = $product->quantity > 0 ? 'active' : 'out_of_stock';
                $product->save();
            }

            return $purchase;
        });

        // For AJAX (your form uses this)
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

        // Fallback for non-AJAX
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
