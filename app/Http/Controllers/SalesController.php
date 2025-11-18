<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    // ============================================================
    // LOAD POS SCREEN
    // ============================================================
    public function pos()
    {
        $customers = Customer::orderBy('name')->get();
        return view('pos', compact('customers'));
    }

    // ============================================================
    // ADD TO CART (barcode / sku / name)
    // ============================================================
    public function addToCart(Request $request)
    {
        $request->validate([
            'search' => 'required|string'
        ]);

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
            'id'       => $product->id,
            'sku'      => $product->sku,
            'name'     => $product->name,
            'price'    => $product->selling_price,
            'barcode'  => $product->barcode,
            'quantity' => $product->quantity,   // ⬅ REQUIRED FOR JS STOCK LIMITS
        ]);
    }

    // ============================================================
    // PRODUCT SEARCH FOR SUGGESTIONS (right side)
    // ============================================================
    public function searchProducts(Request $request)
    {
        $q = $request->get('name');
        if (!$q) return response()->json([]);

        $products = Product::query()
            ->where('name', 'like', '%' . $q . '%')
            ->orWhere('sku', 'like', '%' . $q . '%')
            ->orWhere('barcode', 'like', '%' . $q . '%')
            ->limit(10)
            ->get(['id', 'name', 'sku', 'selling_price', 'quantity']); // ⬅ include stock

        return response()->json($products);
    }

    // ============================================================
    // CREATE CUSTOMER
    // ============================================================
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
            'customer' => $customer,
        ]);
    }

    // ============================================================
    // CHECKOUT (with FULL stock validation)
    // ============================================================
    public function checkout(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|integer|exists:products,id',
            'items.*.qty'    => 'required|numeric|min:1',
            'items.*.price'  => 'required|numeric|min:0',
            'items.*.sku'    => 'required|string',
            'items.*.name'   => 'required|string',
            'payment_method' => 'required|string',
            'amount_paid'    => 'required|numeric|min:0',
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
        ]);

        // --------------------------------------------------------
        // STOCK VALIDATION BEFORE SALE
        // --------------------------------------------------------
        foreach ($request->items as $item) {
            $product = Product::find($item['id']);
            if (!$product) continue;

            if ($item['qty'] > $product->quantity) {
                return response()->json([
                    'error' => "{$product->name} has only {$product->quantity} left."
                ], 422);
            }
        }

        // --------------------------------------------------------
        // CALCULATE TOTALS
        // --------------------------------------------------------
        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += ($item['qty'] * $item['price']);
        }

        $discount = $request->discount ?? 0;
        $fee      = $request->fee ?? 0;
        $total    = ($subtotal - $discount + $fee);
        $change   = $request->amount_paid - $total;

        // --------------------------------------------------------
        // CREATE SALE
        // --------------------------------------------------------
        $sale = Sale::create([
            'user_id'        => Auth::id(),
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'fee'            => $fee,
            'total'          => $total,
            'amount_paid'    => $request->amount_paid,
            'change'         => $change,
            'payment_method' => $request->payment_method,
            'status'         => 'completed',
        ]);

        // --------------------------------------------------------
        // SAVE ITEMS + UPDATE STOCK
        // --------------------------------------------------------
        foreach ($request->items as $item) {
            $product = Product::find($item['id']);

            if ($product) {
                $product->quantity = max(0, $product->quantity - $item['qty']);

                $product->status = $product->is_suspended
                    ? 'suspended'
                    : ($product->quantity > 0 ? 'active' : 'out_of_stock');

                $product->save();
            }

            SaleItem::create([
                'sale_id'     => $sale->id,
                'product_id'  => $item['id'],
                'sku'         => $item['sku'],
                'name'        => $item['name'],
                'qty'         => $item['qty'],
                'price'       => $item['price'],
                'subtotal'    => ($item['qty'] * $item['price']),
            ]);
        }

        return response()->json([
            'success'      => 'Sale completed successfully',
            'sale_id'      => $sale->id,
            'redirect_url' => route('admin.sales.print', $sale->id),
        ]);
    }

    // ============================================================
    // PAUSE (HOLD) SALE
    // ============================================================
    public function pause(Request $request)
    {
        $request->validate([
            'items'        => 'required|array|min:1',
            'items.*.id'   => 'required|integer|exists:products,id',
            'items.*.qty'  => 'required|numeric|min:1',
            'items.*.price'=> 'required|numeric|min:0',
            'items.*.sku'  => 'required|string',
            'items.*.name' => 'required|string',
            'hold_number'  => 'required|string|max:50',
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }

        $sale = Sale::create([
            'user_id'        => Auth::id(),
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'subtotal'       => $subtotal,
            'discount'       => 0,
            'fee'            => 0,
            'total'          => $subtotal,
            'payment_method' => 'cash',
            'status'         => 'paused',
            'hold_number'    => $request->hold_number,
        ]);

        foreach ($request->items as $item) {
            SaleItem::create([
                'sale_id'     => $sale->id,
                'product_id'  => $item['id'],
                'sku'         => $item['sku'],
                'name'        => $item['name'],
                'qty'         => $item['qty'],
                'price'       => $item['price'],
                'subtotal'    => $item['qty'] * $item['price'],
            ]);
        }

        return response()->json([
            'success' => 'Sale paused',
            'sale_id' => $sale->id
        ]);
    }

    // ============================================================
    // RESUME HELD SALE
    // ============================================================
    public function resume($id)
    {
        $sale = Sale::with('items')
            ->where('id', $id)
            ->where('status', 'paused')
            ->firstOrFail();

        return response()->json($sale);
    }

    // ============================================================
    // LIST HELD SALES
    // ============================================================
    public function heldSales()
    {
        return response()->json(
            Sale::where('status', 'paused')
                ->orderByDesc('created_at')
                ->get(['id','hold_number','customer_name','total','created_at'])
        );
    }

    // ============================================================
    // DELETE HELD SALE
    // ============================================================
    public function destroyHeld(Sale $sale)
    {
        if ($sale->status !== 'paused') {
            return response()->json(['error' => 'Only held sales can be deleted.'], 422);
        }

        $sale->items()->delete();
        $sale->delete();

        return response()->json(['success' => true]);
    }

    // ============================================================
    // PRINT RECEIPT
    // ============================================================
    public function print($id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        return view('sales.print', compact('sale'));
    }
}
