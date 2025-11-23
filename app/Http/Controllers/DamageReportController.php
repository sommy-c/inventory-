<?php

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DamageReportController extends Controller
{
   
    public function index(Request $request)
    {
        $type      = $request->query('type');       // damaged | expired
        $productId = $request->query('product_id');
        $supplier  = $request->query('supplier');
        $from      = $request->query('from');
        $to        = $request->query('to');

        $query = DamageReport::with(['product', 'user'])
            ->orderByDesc('created_at');

        if ($type) {
            $query->where('type', $type);
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($supplier) {
            $query->whereHas('product', function ($q) use ($supplier) {
                $q->where('supplier', 'like', "%{$supplier}%");
            });
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $damages = $query->paginate(15);

        // Dropdown data
        $products  = Product::orderBy('name')->get();
        $suppliers = Product::whereNotNull('supplier')
            ->distinct()
            ->orderBy('supplier')
            ->pluck('supplier');

        // Stats
        $totalDamaged = DamageReport::where('type', 'damaged')->sum('quantity');
        $totalExpired = DamageReport::where('type', 'expired')->sum('quantity');
        $openCount    = DamageReport::where('status', 'open')->count();

        // Trend for chart: last 30 days
        $trend = DamageReport::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('SUM(quantity) as total_qty')
            )
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $trendDays   = [];
        $trendTotals = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trendDays[] = $date;
            $row = $trend->firstWhere('day', $date);
            $trendTotals[] = $row ? (int)$row->total_qty : 0;
        }

        return view('damages.index', [
            'damages'      => $damages,
            'products'     => $products,
            'suppliers'    => $suppliers,
            'filterType'   => $type,
            'filterProduct'=> $productId,
            'filterSupplier'=> $supplier,
            'filterFrom'   => $from,
            'filterTo'     => $to,
            'totalDamaged' => $totalDamaged,
            'totalExpired' => $totalExpired,
            'openCount'    => $openCount,
            'trendDays'    => $trendDays,
            'trendTotals'  => $trendTotals,
        ]);
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('damages.create', compact('products'));
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'product_id'   => 'required|exists:products,id',
        'type'         => 'required|in:damaged,expired',
        'quantity'     => 'required|integer|min:1',
        'expiry_date'  => 'nullable|date|required_if:type,expired',
        'note'         => 'nullable|string',
    ]);

    $damage = new DamageReport();
    $damage->product_id   = $data['product_id'];
    $damage->type         = $data['type'];
    $damage->quantity     = $data['quantity'];
    $damage->remaining    = $data['quantity'];         // full amount initially
    $damage->expiry_date  = $data['expiry_date'] ?? null;
    $damage->note         = $data['note'] ?? null;
    $damage->status       = 'pending';                 // 🔥 WAITING FOR ADMIN APPROVAL
    $damage->user_id      = Auth::id();
    $damage->save();

    // 🔴 STILL NO STOCK CHANGE HERE

    return redirect()
        ->route('admin.damages.index')
        ->with('success', 'Damage / expired stock logged and pending admin approval.');
}

public function resolve(Request $request, DamageReport $damage)
{
    $request->validate([
        'resolved_quantity' => 'required|integer|min:1|max:' . $damage->remaining,
    ]);

    if ($damage->status !== 'open') {
        return back()->with('error', 'Only open entries can be resolved.');
    }

    DB::transaction(function () use ($damage, $request) {
        $qty      = (int) $request->resolved_quantity;
        $product  = Product::lockForUpdate()->find($damage->product_id);

        if ($product) {
            // 🔥 Add back stock on resolve
            $product->quantity += $qty;
            $product->status    = $product->quantity > 0 ? 'active' : 'out_of_stock';
            $product->save();
        }

        $damage->remaining -= $qty;

        if ($damage->remaining <= 0) {
            $damage->remaining = 0;
            $damage->status    = 'resolved';
        }

        $damage->save();
    });

    return back()->with('success', 'Damage entry resolved and stock updated.');
}



public function approve(DamageReport $damage)
{
    if (!Auth::user()->hasRole('admin')) {
        abort(403);
    }

    if ($damage->status !== 'pending') {
        return back()->with('error', 'Only pending records can be approved.');
    }

    DB::transaction(function () use ($damage) {
        $product = Product::lockForUpdate()->find($damage->product_id);

        if (!$product) {
            throw new \RuntimeException('Product not found for this damage entry.');
        }

        if ($product->quantity < $damage->quantity) {
            throw new \RuntimeException('Insufficient stock to approve this damage entry.');
        }

        // 🔥 Only now do we reduce stock
        $product->quantity -= $damage->quantity;
        $product->status    = $product->quantity > 0 ? 'active' : 'out_of_stock';
        $product->save();

        // After approval, entry becomes "open" (can be partially resolved)
        $damage->status = 'open';
        // keep $damage->remaining as full quantity for later resolve()
        $damage->save();
    });

    return back()->with('success', 'Damage entry approved and stock updated.');
}


public function reject(DamageReport $damage)
{
    if (!Auth::user()->hasRole('admin')) {
        abort(403);
    }

    if ($damage->status !== 'pending') {
        return back()->with('error', 'Only pending records can be rejected.');
    }

    $damage->status    = 'rejected';
    $damage->remaining = 0;      // nothing to resolve
    $damage->save();

    // 🔴 Still no stock change

    return back()->with('success', 'Damage entry rejected.');
}


    // ---- Export stubs (you can hook Excel/PDF libs here) ----

    public function exportExcel(Request $request)
    {
        // TODO: Implement with maatwebsite/excel if you install it.
        // For now, just redirect back with message:
        return back()->with('success', 'Excel export not implemented yet.');
    }

    public function exportPdf(Request $request)
    {
        // TODO: Implement with dompdf / snappy.
        return back()->with('success', 'PDF export not implemented yet.');
    }

}
