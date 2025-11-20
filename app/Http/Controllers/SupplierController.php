<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
{
    $search         = $request->get('q');
    $paymentStatus  = $request->get('payment_status'); // paid / unpaid / partial
    $productFilter  = $request->get('product');        // product name / sku

    $suppliers = Supplier::query()
        // text search on supplier
        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        })
        // filter by payment status via purchases
        ->when($paymentStatus, function ($q) use ($paymentStatus) {
            $q->whereHas('purchases', function ($sub) use ($paymentStatus) {
                $sub->where('payment_status', $paymentStatus);
            });
        })
        // filter by product (name or sku) via purchases -> items -> product
        ->when($productFilter, function ($q) use ($productFilter) {
            $q->whereHas('purchases.items.product', function ($sub) use ($productFilter) {
                $sub->where('name', 'like', "%{$productFilter}%")
                    ->orWhere('sku', 'like', "%{$productFilter}%");
            });
        })
        ->orderBy('name')
        ->paginate(20)
        ->appends($request->query()); // keep filters on pagination links

    return view('suppliers.index', [
        'suppliers'      => $suppliers,
        'search'         => $search,
        'paymentStatus'  => $paymentStatus,
        'productFilter'  => $productFilter,
    ]);
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'nullable|email',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        Supplier::create($data);

        return back()->with('success', 'Supplier added successfully.');
    }

    // For details modal (products, last supply, payment)
    public function details(Supplier $supplier)
    {
        $supplier->load([
            'purchases.items.product' => function ($q) {
                $q->select('products.id', 'products.name', 'products.sku');
            },
        ]);

        $lastPurchase = $supplier->purchases()->latest()->first();

        // collect unique products from all purchase items
        $products = $supplier->purchases
            ->flatMap->items
            ->pluck('product')
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($p) {
                return [
                    'id'   => $p->id,
                    'name' => $p->name,
                    'sku'  => $p->sku,
                ];
            });

        return response()->json([
            'id'             => $supplier->id,
            'name'           => $supplier->name,
            'email'          => $supplier->email,
            'phone'          => $supplier->phone,
            'address'        => $supplier->address,
            'created_at'     => optional($supplier->created_at)->format('Y-m-d H:i'),
            'last_supply'    => optional(optional($lastPurchase)->created_at)->format('Y-m-d H:i'),
            'payment_status' => $lastPurchase?->payment_status,
            'products'       => $products,
        ]);
    }


    // delete
    public function destroy(Supplier $supplier)
{
    // Optional: Prevent deleting if supplier has purchases
    if ($supplier->purchases()->exists()) {
        return back()->with('error', 'Cannot delete supplier with purchase history.');
    }

    $supplier->delete();

    return back()->with('success', 'Supplier deleted successfully.');
}

}
