@extends('admin.layout')

@section('title', 'Product Details')

@section('content')
<div class="product-show-container">
    <div class="page-header">
        <h1>{{ $product->name }}</h1>
        <p class="subtitle">SKU: {{ $product->sku }} @if($product->barcode) | Barcode: {{ $product->barcode }} @endif</p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top info + stats --}}
    <div class="grid-layout">
        {{-- Left: basic details --}}
        <div class="card">
            <h2 class="card-title">Basic Information</h2>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Category</span>
                    <span class="detail-value">{{ $product->category ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Brand</span>
                    <span class="detail-value">{{ $product->brand ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Current Supplier</span>
                    <span class="detail-value">{{ $product->supplier ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Purchase Price</span>
                    <span class="detail-value">₦{{ number_format($product->purchase_price, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Selling Price</span>
                    <span class="detail-value">₦{{ number_format($product->selling_price, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $product->status }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Reorder Level</span>
                    <span class="detail-value">{{ $product->reorder_level ?? 0 }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Expiry Date</span>
                    <span class="detail-value">
                        {{ $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '—' }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Last Supplied</span>
                    <span class="detail-value">
                        {{ $product->supply_date ? $product->supply_date->format('Y-m-d') : '—' }}
                    </span>
                </div>
            </div>

            <div class="card-actions">
                @if(auth()->user()->hasAnyRole(['admin','manager']))
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-primary">Edit Product</a>
                @endif

                <a href="{{ route('admin.products') }}" class="btn-secondary">Back to Products</a>
            </div>
        </div>

        {{-- Right: stats --}}
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Purchased</span>
                <span class="stat-value">{{ $totalPurchased }}</span>
                <span class="stat-sub">All time</span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Total Sold</span>
                <span class="stat-value">{{ $totalSold }}</span>
                <span class="stat-sub">All time</span>
            </div>

            <div class="stat-card">
                <span class="stat-label">In Stock</span>
                <span class="stat-value">{{ $currentStock }}</span>
                <span class="stat-sub">
                    @if($currentStock <= ($product->reorder_level ?? 0))
                        <span class="badge badge-warning">Below / near reorder level</span>
                    @else
                        <span class="badge badge-success">OK</span>
                    @endif
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Computed (Purchased - Sold)</span>
                <span class="stat-value">{{ $expectedStock }}</span>
                <span class="stat-sub">
                    @if($expectedStock != $currentStock)
                        <span class="badge badge-danger">
                            Mismatch with current stock ({{ $currentStock }})
                        </span>
                    @else
                        <span class="badge badge-neutral">Matches current stock</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Supply history --}}
    <div class="card" style="margin-top: 20px;">
        <h2 class="card-title">Supply History</h2>
        @if($supplyHistory->isEmpty())
            <p class="empty-text">No purchase records found for this product.</p>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Reference</th>
                            <th>Qty Supplied</th>
                            <th>Cost Price</th>
                            <th>Line Total</th>
                            <th>Expiry (this batch)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplyHistory as $item)
                            @php
                                $purchase = $item->purchase;
                                $supplierName = $purchase && $purchase->supplier
                                    ? $purchase->supplier->name
                                    : '—';
                                $purchaseDate = $purchase && $purchase->purchase_date
                                    ? \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d')
                                    : '—';
                                $ref = $purchase ? $purchase->reference : null;
                            @endphp
                            <tr>
                                <td data-label="Date">{{ $purchaseDate }}</td>
                                <td data-label="Supplier">{{ $supplierName }}</td>
                                <td data-label="Reference">{{ $ref ?? '—' }}</td>
                                <td data-label="Qty Supplied">{{ $item->quantity }}</td>
                                <td data-label="Cost Price">
                                    ₦{{ number_format($item->cost_price, 2) }}
                                </td>
                                <td data-label="Line Total">
                                    ₦{{ number_format($item->line_total, 2) }}
                                </td>
                                <td data-label="Expiry">
                                    {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<style>
.product-show-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px 16px 32px;
    color: #f9fafb;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.page-header h1 {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 4px;
}

.page-header .subtitle {
    font-size: 0.9rem;
    color: #9ca3af;
}

.alert {
    padding: 10px 12px;
    border-radius: 8px;
    margin: 10px 0;
    font-size: 0.9rem;
}
.alert-success {
    background: rgba(22, 163, 74, 0.15);
    border: 1px solid rgba(22, 163, 74, 0.6);
    color: #bbf7d0;
}
.alert-error {
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(248, 113, 113, 0.7);
    color: #fecaca;
}

/* Layout */
.grid-layout {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1.4fr);
    gap: 16px;
    margin-top: 16px;
}

/* Cards */
.card {
    background: rgba(15, 23, 42, 0.95);
    border-radius: 12px;
    padding: 16px 18px;
    border: 1px solid rgba(31, 41, 55, 0.9);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #e5e7eb;
}

/* Basic info details */
.details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
    margin-top: 4px;
}

.detail-row {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.detail-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #9ca3af;
}
.detail-value {
    font-size: 0.9rem;
    color: #e5e7eb;
}

/* Status badge */
.status-badge {
    display: inline-flex;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-active {
    background: rgba(22, 163, 74, 0.18);
    color: #86efac;
}
.status-out_of_stock {
    background: rgba(239, 68, 68, 0.18);
    color: #fecaca;
}
.status-suspended {
    background: rgba(148, 163, 184, 0.18);
    color: #e5e7eb;
}
.status-inactive {
    background: rgba(107, 114, 128, 0.2);
    color: #d1d5db;
}

.card-actions {
    margin-top: 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

/* Buttons */
.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    border-radius: 999px;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease, transform 0.15s ease;
}

.btn-primary {
    background: rgba(37, 99, 235, 1);
    color: #f9fafb;
}
.btn-primary:hover {
    background: rgba(37, 99, 235, 0.9);
    transform: translateY(-1px);
}

.btn-secondary {
    background: rgba(31, 41, 55, 0.95);
    color: #e5e7eb;
    border: 1px solid rgba(55, 65, 81, 0.9);
}
.btn-secondary:hover {
    background: rgba(55, 65, 81, 0.95);
    transform: translateY(-1px);
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.stat-card {
    background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.4), rgba(15, 23, 42, 0.95));
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid rgba(30, 64, 175, 0.9);
}

.stat-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #cbd5f5;
}
.stat-value {
    display: block;
    font-size: 1.2rem;
    font-weight: 700;
    margin-top: 4px;
    color: #eff6ff;
}
.stat-sub {
    display: block;
    margin-top: 4px;
    font-size: 0.78rem;
    color: #cbd5f5;
}

/* small badges */
.badge {
    display: inline-flex;
    padding: 2px 7px;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 600;
}
.badge-warning {
    background: rgba(234, 179, 8, 0.15);
    color: #facc15;
}
.badge-success {
    background: rgba(22, 163, 74, 0.18);
    color: #86efac;
}
.badge-danger {
    background: rgba(239, 68, 68, 0.18);
    color: #fecaca;
}
.badge-neutral {
    background: rgba(148, 163, 184, 0.2);
    color: #e5e7eb;
}

/* Supply history */
.empty-text {
    font-size: 0.9rem;
    color: #9ca3af;
}

.table-wrapper {
    overflow-x: auto;
    margin-top: 8px;
    border-radius: 8px;
    border: 1px solid rgba(31, 41, 55, 0.9);
}

.table-wrapper table {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
}

.table-wrapper thead {
    background: rgba(30, 64, 175, 0.95);
}

.table-wrapper thead th {
    padding: 9px 10px;
    font-size: 0.8rem;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #e5e7eb;
}

.table-wrapper tbody tr {
    border-bottom: 1px solid rgba(31, 41, 55, 1);
}

.table-wrapper tbody tr:nth-child(even) {
    background: rgba(15, 23, 42, 0.96);
}
.table-wrapper tbody tr:nth-child(odd) {
    background: rgba(15, 23, 42, 0.9);
}

.table-wrapper tbody tr:hover {
    background: rgba(30, 64, 175, 0.35);
}

.table-wrapper tbody td {
    padding: 8px 10px;
    font-size: 0.82rem;
    color: #e5e7eb;
}

/* Responsive */
@media (max-width: 900px) {
    .grid-layout {
        grid-template-columns: minmax(0, 1fr);
    }

    .details-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 640px) {
    .product-show-container {
        padding: 16px 10px 28px;
    }

    .page-header h1 {
        font-size: 22px;
    }

    .table-wrapper table {
        min-width: 600px;
    }
}
</style>
@endsection
