@extends('admin.layout')

@section('title', 'Order '.$order->order_number)

@section('content')
<div class="dashboard-page">
    <img src="{{ asset('images/logo.png') }}"
         alt="Logo"
         class="print-logo"
         style="width:60px; height:auto;">

    {{-- HEADER --}}
    <div class="page-header">
        <h1>Order {{ $order->order_number ?? ('ORD-'.$order->id) }}</h1>

        <div class="header-actions">
            <a href="{{ route('admin.index') }}"
               class="btn-primary"
               style="background: transparent; border: 1px solid #4b5563; box-shadow:none;">
                ← Back to Orders
            </a>
            <button type="button" class="btn-primary" onclick="window.print()">
                🖨 Print
            </button>
        </div>
    </div>

    {{-- TOP SUMMARY CARD --}}
    <div class="card order-summary-card">
        <div class="order-summary-inner">
            <div>
                <h3 class="order-summary-heading">Supplier</h3>
                <p class="order-summary-supplier">
                    {{ $order->supplier_name ?? '-' }}
                </p>

                <p class="order-summary-meta">
                    Order created: {{ $order->created_at?->format('Y-m-d H:i') }}<br>
                    Expected date: {{ $order->expected_date?->format('Y-m-d') ?? '—' }}
                </p>
            </div>

            <div class="order-summary-right">
                @php
                    $statusClass = match($order->status) {
                        'waiting'  => 'badge-status-pending',
                        'pending'  => 'badge-status-open',
                        'supplied' => 'badge-status-resolved',
                        default    => 'badge-status-open',
                    };
                @endphp

                <p class="order-summary-status-label">Status</p>
                <span class="badge {{ $statusClass }}">
                    {{ ucfirst($order->status) }}
                </span>

                <p class="order-summary-total">
                    Total: {{ number_format($order->total ?? 0, 2) }}
                </p>
            </div>
        </div>
    </div>

    {{-- ITEMS TABLE --}}
    <div class="card">
        <h3 class="order-items-title">Items</h3>

        <div class="table-wrapper" style="margin-top:0;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Cost</th>
                        <th style="text-align:right;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td style="text-align:right;">{{ $item->qty }}</td>
                            <td style="text-align:right;">{{ number_format($item->price, 2) }}</td>
                            <td style="text-align:right;">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="order-empty-row">
                                No items for this order.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- NOTES + SIGNATURES --}}
    <div class="card signatures-card-simple">
        @if(!empty($order->notes))
            <div class="notes-section">
                <h3 class="notes-heading">Notes</h3>
                <p class="notes-text">
                    {{ $order->notes }}
                </p>
            </div>
            <hr class="sig-divider">
        @endif

        <div class="signatures-wrapper">
            <div class="signature-block">
                <p class="sig-label">Manager</p>
                <p class="sig-name">{{ $order->manager_name ?? '—' }}</p>
                @if($order->manager_signed_at)
                    <p class="sig-meta">
                        Signed at: {{ $order->manager_signed_at->format('Y-m-d H:i') }}
                    </p>
                @endif
                <div class="sig-line"></div>
                <p class="sig-caption">Signature &nbsp; | &nbsp; Date</p>
            </div>

            <div class="signature-block">
                <p class="sig-label">Chairman</p>
                <p class="sig-name">{{ $order->admin_name ?? '(Pending approval)' }}</p>
                @if($order->admin_approved_at)
                    <p class="sig-meta">
                        Approved at: {{ $order->admin_approved_at->format('Y-m-d H:i') }}
                    </p>
                @endif
                <div class="sig-line"></div>
                <p class="sig-caption">Signature &nbsp; | &nbsp; Date</p>
            </div>
        </div>
    </div>

    {{-- ACTION BUTTONS (ADMIN) --}}
    @php
        $user    = auth()->user();
        $isAdmin = $user && $user->hasRole('admin');
    @endphp

    @if($isAdmin)
        <div class="card" style="display:flex; justify-content:flex-end; gap:10px;">
            @if($order->status === 'waiting')
                <form action="{{ route('admin.approve', $order) }}"
                      method="POST"
                      onsubmit="return confirm('Approve this order?');">
                    @csrf
                    <button type="submit" class="btn-approve">
                        Approve (to Pending)
                    </button>
                </form>
            @endif

            @if($order->status === 'pending')
                <form action="{{ route('admin.supplied', $order) }}"
                      method="POST"
                      onsubmit="return confirm('Mark this order as supplied?');">
                    @csrf
                    <button type="submit" class="btn-resolve">
                        Mark as Supplied
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>

<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
}

/* Hide logo on screen by default (only for print) */
.print-logo {
    display: none !important;
}

/* ====== SUMMARY CARD (theme-aware text) ====== */
.order-summary-card {
    /* card base already from layout */
}

.order-summary-inner {
    display:flex;
    flex-wrap:wrap;
    gap:20px;
    justify-content:space-between;
}

.order-summary-heading {
    margin:0 0 8px 0;
    font-size:1rem;
}

/* supplier */
.order-summary-supplier {
    margin:0;
}

/* meta text (created, expected) */
.order-summary-meta {
    margin:8px 0 0 0;
    font-size:0.9rem;
}

/* right side */
.order-summary-right {
    text-align:right;
}

.order-summary-status-label {
    margin:0 0 6px 0;
    font-size:0.9rem;
}

/* total line */
.order-summary-total {
    margin:12px 0 0 0;
    font-size:1.4rem;
    font-weight:700;
}

/* Dark theme colors */
body.theme-dark .order-summary-heading {
    color:#f9fafb;
}
body.theme-dark .order-summary-supplier {
    color:#e5e7eb;
}
body.theme-dark .order-summary-meta,
body.theme-dark .order-summary-status-label {
    color:#9ca3af;
}
body.theme-dark .order-summary-total {
    color:#f9fafb;
}

/* Light theme colors */
body.theme-light .order-summary-heading {
    color:var(--orange-strong);
}
body.theme-light .order-summary-supplier {
    color:var(--orange-main);
}
body.theme-light .order-summary-meta,
body.theme-light .order-summary-status-label {
    color:#a16207; /* warm muted */
}
body.theme-light .order-summary-total {
    color:var(--orange-strong);
}

/* empty row text */
.order-empty-row {
    text-align:center;
    padding:12px;
}
body.theme-dark .order-empty-row {
    color:#9ca3af;
}
body.theme-light .order-empty-row {
    color:#a16207;
}

/* ITEMS title */
.order-items-title {
    margin-top:0;
    margin-bottom:10px;
}
body.theme-dark .order-items-title {
    color:#f9fafb;
}
body.theme-light .order-items-title {
    color:var(--orange-strong);
}

/* ---------- APPROVE / SUPPLIED BUTTON STYLES ---------- */
.btn-approve,
.btn-resolve {
    padding: 8px 16px;
    border-radius: 999px;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.18s ease, transform 0.12s ease, box-shadow 0.18s ease;
    white-space: nowrap;
}

/* Dark theme styles */
body.theme-dark .btn-approve {
    background: rgba(34, 197, 94, 0.92);
    color: #ecfdf5;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}
body.theme-dark .btn-approve:hover {
    background: rgba(22, 163, 74, 1);
    transform: translateY(-1px);
}
body.theme-dark .btn-resolve {
    background: rgba(14, 165, 233, 0.95);
    color: #e0f2fe;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
}
body.theme-dark .btn-resolve:hover {
    background: rgba(2, 132, 199, 1);
    transform: translateY(-1px);
}

/* Light theme styles */
body.theme-light .btn-approve {
    background: rgba(22,163,74,0.08);
    color: #166534;
    border: 1px solid rgba(22,163,74,0.8);
    box-shadow: 0 3px 8px rgba(22,163,74,0.3);
}
body.theme-light .btn-approve:hover {
    background: rgba(22,163,74,0.16);
    transform: translateY(-1px);
}
body.theme-light .btn-resolve {
    background: rgba(59,130,246,0.08);
    color: #1d4ed8;
    border: 1px solid rgba(59,130,246,0.8);
    box-shadow: 0 3px 8px rgba(59,130,246,0.3);
}
body.theme-light .btn-resolve:hover {
    background: rgba(59,130,246,0.16);
    transform: translateY(-1px);
}

/* ---------- SIGNATURES & NOTES (theme-aware) ---------- */
.signatures-card-simple {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Notes heading */
.notes-heading {
    margin-top:0;
    margin-bottom:8px;
}
body.theme-dark .notes-heading {
    color:#f9fafb;
}
body.theme-light .notes-heading {
    color:var(--orange-strong);
}

/* notes background */
.notes-section .notes-text {
    margin: 0;
    white-space: pre-wrap;
    font-size: 0.95rem;
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid;
}

/* dark */
body.theme-dark .notes-section .notes-text {
    color: #e5e7eb;
    background: rgba(15,23,42,0.9);
    border-color: rgba(55,65,81,0.8);
}

/* light */
body.theme-light .notes-section .notes-text {
    color: var(--orange-main);
    background: #fff7ed;
    border-color: var(--border-soft);
}

.sig-divider {
    border: 0;
    border-top: 1px solid rgba(55,65,81,0.7);
    margin: 0;
}

.signatures-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.signature-block {
    flex: 1 1 240px;
    min-width: 0;
}

.sig-label {
    margin: 0 0 4px 0;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.sig-name {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.sig-meta {
    margin: 4px 0 0 0;
    font-size: 0.8rem;
}

.sig-line {
    margin-top: 18px;
    border-bottom: 1px dashed rgba(148,163,184,0.9);
}

.sig-caption {
    margin-top: 4px;
    font-size: 0.75rem;
}

/* dark sig text */
body.theme-dark .sig-label {
    color: #9ca3af;
}
body.theme-dark .sig-name {
    color: #e5e7eb;
}
body.theme-dark .sig-meta,
body.theme-dark .sig-caption {
    color: #9ca3af;
}

/* light sig text */
body.theme-light .sig-label {
    color: var(--orange-strong);
}
body.theme-light .sig-name {
    color: var(--orange-main);
}
body.theme-light .sig-meta,
body.theme-light .sig-caption {
    color: #a16207;
}

@media (max-width: 640px) {
    .signatures-wrapper {
        flex-direction: column;
    }
}

/* ---------- PRINT STYLES ---------- */
@media print {

    @page {
        margin: 8mm;
    }

    body, html {
        margin: 0 !important;
        padding: 0 !important;
    }

    body * {
        visibility: hidden;
    }

    .dashboard-page * {
        color: #000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .dashboard-page,
    .dashboard-page * {
        visibility: visible !important;
    }

    .dashboard-page {
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
    }

    /* Show logo when printing */
    .print-logo {
        display: block !important;
    }

    img {
        max-height: 60px !important;
        object-fit: contain;
    }

    /* Hide interactive UI */
    .header-actions,
    button,
    .btn,
    .btn-approve,
    .btn-resolve,
    a[href],
    nav,
    .sidebar {
        display: none !important;
    }

    .card {
        background: none !important;
        padding: 6px 0 !important;
        margin: 0 0 4px 0 !important;
        border: none !important;
        box-shadow: none !important;
    }

    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }

    table th,
    table td {
        padding: 3px 4px !important;
        font-size: 11px !important;
        border-bottom: 1px solid #000 !important;
    }

    .badge {
        background: none !important;
        border: 1px solid #000 !important;
        color: #000 !important;
    }

    .signatures-wrapper {
        gap: 4px !important;
    }

    .sig-line {
        margin-top: 6px !important;
    }
}
</style>
@endsection
