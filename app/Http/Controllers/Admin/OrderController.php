<?php

// app/Http/Controllers/Admin/OrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->query('search');
        $statusFilt = $request->query('status'); // waiting, pending, supplied

        $orders = Order::query()
            ->with('supplier')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('order_number', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");
                });
            })
            ->when($statusFilt, function ($q) use ($statusFilt) {
                $q->where('status', $statusFilt);
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('order.index', [
            'orders'     => $orders,
            'statusFilt' => $statusFilt,
        ]);
    }

    public function create()
    {
        // Store info – you can pull from settings table if you like
        $storeName    = config('app.name', 'My Store');
        $storeAddress = null; // e.g. Setting::get('store_address');
        $storePhone   = null; // e.g. Setting::get('store_phone');

        return view('order.create', compact('storeName', 'storeAddress', 'storePhone'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name'      => 'required|string|max:255',
            'expected_date'      => 'nullable|date',
            'reference'          => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'manager_name'       => 'nullable|string|max:255',
            'items'              => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $user = Auth::user();

            // Compute total
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['qty'] * $item['price'];
            }

            $order = Order::create([
                'supplier_name'     => $request->supplier_name,
                'expected_date'     => $request->expected_date,
                'reference'         => $request->reference,
                'notes'             => $request->notes,
                'status'            => 'waiting', // Manager created, waiting for admin approval
                'total'             => $total,
                'manager_name'      => $request->manager_name ?: ($user?->name),
                'manager_signed_at' => now(),
                'created_by'        => $user?->id,
            ]);

            // Simple order number: ORD-00001, etc.
            $order->order_number = 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();

            foreach ($request->items as $item) {
                $lineTotal = $item['qty'] * $item['price'];

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item['product_id'] ?? null, // if you wire product select later
                    'product_name' => $item['product_name'],
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                    'line_total'   => $lineTotal,
                ]);
            }
        });

        return redirect()
            ->route('admin.index')
            ->with('success', 'Order created successfully and is now waiting for admin approval.');
    }

    public function show(Order $order)
{
    $order->load('items');

    return view('order.show', compact('order')); // 👈 plural
}


    public function destroy(Order $order)
    {
        $user    = Auth::user();
        $isAdmin = $user && ($user->is_admin ?? false);

        // Manager can delete only if status is 'waiting'
        if (!$isAdmin && !$order->isWaiting()) {
            return redirect()
                ->route('admin.index')
                ->with('error', 'You can only delete orders that are still waiting for approval.');
        }

        $order->delete();

        return redirect()
            ->route('admin.index')
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Admin approval: waiting -> pending
     */
    public function approve(Order $order)
{
    // ✅ At this point, middleware('role:admin') has already ensured user is admin
    $user = Auth::user();

    if (!$order->isWaiting()) {
        return redirect()
            ->route('admin.show', $order) // we'll fix route name below
            ->with('error', 'Only waiting orders can be approved.');
    }

    $order->update([
        'status'            => 'pending',
        'admin_name'        => $user->name,
        'admin_approved_at' => now(),
    ]);

    return redirect()
        ->route('admin.show', $order)
        ->with('success', 'Order approved and now pending supply.');
}

    /**
     * Mark as supplied (after goods received)
     */
    public function markSupplied(Order $order)
{
    // ✅ Already checked by middleware('role:admin')
    if ($order->isSupplied()) {
        return redirect()
            ->route('admin.orders.show', $order)
            ->with('info', 'Order already marked as supplied.');
    }

    $order->update([
        'status' => 'supplied',
    ]);

    return redirect()
        ->route('admin.show', $order)
        ->with('success', 'Order marked as supplied.');
}

}
