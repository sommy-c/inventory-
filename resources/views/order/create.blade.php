@extends('admin.layout')

@section('title', 'New Purchase Order')

@section('content')
<div class="dashboard-page purchase-order-page">

    {{-- HEADER --}}
    <div class="page-header po-header">
        <h1>New Purchase Order</h1>

        <div class="header-actions">
            <a href="{{ route('admin.index') }}"
               class="btn-primary po-back-btn">
                ← Back to Orders
            </a>
            <button type="button" class="btn-primary po-print-btn" onclick="window.print()">
                🖨 Print
            </button>
        </div>
    </div>

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="po-alert po-alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Printable order document --}}
    <div id="orderDocument" class="card po-card">

        {{-- Header with logo + store info --}}
        <div class="po-doc-header">
            <div class="po-doc-left">
                <div class="po-logo-wrap">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Store Logo"
                         class="po-logo-img">
                </div>
                <div class="po-store-info">
                    {{-- Store name (optional) --}}
                    {{-- <h2 class="po-store-name">
                        {{ $storeName ?? config('app.name', 'My Store') }}
                    </h2> --}}
                    @if(!empty($storeAddress) || !empty($storePhone))
                        <p class="po-store-meta">
                            {{ $storeAddress ?? '' }}<br>
                            {{ $storePhone ?? '' }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="po-doc-right">
                <div><strong>Date:</strong> {{ now()->format('Y-m-d') }}</div>
                <div><strong>Created by:</strong> {{ auth()->user()->name ?? 'Manager' }}</div>
                <div><strong>Order No.:</strong> (auto after save)</div>
            </div>
        </div>

        {{-- Order form --}}
        <form method="POST" action="{{ route('admin.store') }}">
            @csrf

            {{-- Supplier + meta --}}
            <div class="po-meta-grid">
                <div class="form-group">
                    <label>Supplier Name</label>
                    <input type="text"
                           name="supplier_name"
                           value="{{ old('supplier_name') }}"
                           required>
                </div>

                <div class="form-group">
                    <label>Expected Delivery Date</label>
                    <input type="date"
                           name="expected_date"
                           value="{{ old('expected_date') }}">
                </div>

                <div class="form-group">
                    <label>Reference / Notes</label>
                    <input type="text"
                           name="reference"
                           value="{{ old('reference') }}"
                           placeholder="Optional memo">
                </div>
            </div>

            {{-- Items table --}}
            <div class="po-table-wrapper">
                <table class="po-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Cost</th>
                            <th class="text-right">Line Total</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="order-items-body">
                        {{-- Initial row --}}
                        <tr>
                            <td>
                                <input type="text"
                                       name="items[0][product_name]"
                                       placeholder="Product name / SKU"
                                       class="po-input po-input-text">
                            </td>
                            <td class="text-right">
                                <input type="number"
                                       name="items[0][qty]"
                                       value="1"
                                       min="1"
                                       class="po-input po-input-number qty-input text-right">
                            </td>
                            <td class="text-right">
                                <input type="number"
                                       step="0.01"
                                       name="items[0][price]"
                                       value="0.00"
                                       class="po-input po-input-number price-input text-right">
                            </td>
                            <td class="text-right">
                                <span class="line-total">0.00</span>
                            </td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn-row btn-delete-row"
                                        onclick="removeOrderRow(this)">
                                    ✕
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="po-add-row-cell">
                                <button type="button"
                                        class="btn-row btn-add-item"
                                        onclick="addOrderRow()">
                                    + Add Item
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right po-total-label">
                                Total:
                            </td>
                            <td class="text-right">
                                <span id="order-total">0.00</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Notes --}}
            <div class="po-notes-wrap">
                <label class="po-notes-label">Additional Notes</label>
                <textarea name="notes"
                          rows="3"
                          class="po-input po-notes-textarea">{{ old('notes') }}</textarea>
            </div>

            {{-- Signatures section --}}
            <div class="po-signatures-grid">
                <div class="po-sign-block">
                    <p class="po-sign-title">Manager</p>
                    <input type="text"
                           name="manager_name"
                           value="{{ auth()->user()->name ?? old('manager_name') }}"
                           placeholder="Manager Name"
                           class="po-input po-sign-input">
                    <div class="po-sign-line"></div>
                    <p class="po-sign-caption">
                        Signature &nbsp; | &nbsp; Date: ____________________
                    </p>
                </div>

                <div class="po-sign-block">
                    <p class="po-sign-title">Admin</p>
                    <input type="text"
                           name="admin_name"
                           value="{{ old('admin_name') }}"
                           placeholder="(To be filled by Admin on approval)"
                           class="po-input po-sign-input">
                    <div class="po-sign-line"></div>
                    <p class="po-sign-caption">
                        Signature &nbsp; | &nbsp; Date: ____________________
                    </p>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="po-actions">
                <button type="submit" class="btn-primary po-submit-btn">
                    Save Order (Status: Waiting)
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JS for dynamic rows + totals --}}
<script>
    let orderRowIndex = 1;

    function addOrderRow() {
        const tbody = document.getElementById('order-items-body');
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <input type="text"
                       name="items[${orderRowIndex}][product_name]"
                       placeholder="Product name / SKU"
                       class="po-input po-input-text">
            </td>
            <td class="text-right">
                <input type="number"
                       name="items[${orderRowIndex}][qty]"
                       value="1"
                       min="1"
                       class="po-input po-input-number qty-input text-right">
            </td>
            <td class="text-right">
                <input type="number"
                       step="0.01"
                       name="items[${orderRowIndex}][price]"
                       value="0.00"
                       class="po-input po-input-number price-input text-right">
            </td>
            <td class="text-right">
                <span class="line-total">0.00</span>
            </td>
            <td class="text-center">
                <button type="button"
                        class="btn-row btn-delete-row"
                        onclick="removeOrderRow(this)">
                    ✕
                </button>
            </td>
        `;
        tbody.appendChild(row);
        orderRowIndex++;
        recalcTotals();
    }

    function removeOrderRow(button) {
        const row = button.closest('tr');
        if (!row) return;
        row.remove();
        recalcTotals();
    }

    function recalcTotals() {
        let grandTotal = 0;

        document.querySelectorAll('#order-items-body tr').forEach(row => {
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const lineTotalSpan = row.querySelector('.line-total');

            const qty = parseFloat(qtyInput?.value || 0);
            const price = parseFloat(priceInput?.value || 0);
            const lineTotal = qty * price;

            if (lineTotalSpan) {
                lineTotalSpan.textContent = lineTotal.toFixed(2);
            }
            grandTotal += lineTotal;
        });

        const orderTotalSpan = document.getElementById('order-total');
        if (orderTotalSpan) {
            orderTotalSpan.textContent = grandTotal.toFixed(2);
        }
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            recalcTotals();
        }
    });

    // Initial total
    recalcTotals();
</script>

{{-- Theme-aware styles + print --}}
<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
    --muted-text: #7c2d12;
}

/* Container */
.purchase-order-page {
    padding: 20px;
}

/* Header */
.po-header h1 {
    font-size: 26px;
    font-weight: 600;
    margin: 0;
}
body.theme-dark .po-header h1 {
    color: #f9fafb;
}
body.theme-light .po-header h1 {
    color: var(--orange-strong);
}

/* Back & print buttons reuse .btn-primary; tweak back */
.po-back-btn {
    background: transparent !important;
    border: 1px solid rgba(148,163,184,0.6) !important;
    box-shadow: none !important;
}
body.theme-light .po-back-btn {
    border-color: rgba(209,213,219,0.95) !important;
    color: var(--orange-main) !important;
}

/* Print button stays primary color from global theme */
.po-print-btn {
    /* uses global btn-primary styles */
}

/* Validation alert */
.po-alert {
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 14px;
    font-size: 0.9rem;
}
.po-alert ul {
    margin: 0;
    padding-left: 20px;
}
.po-alert-error {
    border-width: 1px;
    border-style: solid;
}
body.theme-dark .po-alert-error {
    border-color: rgba(248,113,113,0.7);
    background: rgba(127,29,29,0.35);
    color: #fecaca;
}
body.theme-light .po-alert-error {
    border-color: rgba(248,113,113,0.7);
    background: rgba(248,113,113,0.08);
    color: #b91c1c;
}

/* Main card */
.po-card {
    max-width: 960px;
    margin: 0 auto;
}

/* Document header */
.po-doc-header {
    display:flex;
    justify-content:space-between;
    gap:16px;
    flex-wrap:wrap;
    border-bottom:1px solid rgba(148,163,184,0.5);
    padding-bottom:12px;
    margin-bottom:16px;
}
.po-doc-left {
    display:flex;
    gap:12px;
    align-items:center;
}
.po-logo-wrap {
    border-radius:8px;
    padding:4px;
}
.po-logo-img {
    width:60px;
    height:60px;
    object-fit:contain;
    display:block;
}

/* Logo background by theme */
body.theme-dark .po-logo-wrap {
    background:#0f172a;
}
body.theme-light .po-logo-wrap {
    background:#ffffff;
    border:1px solid rgba(209,213,219,0.9);
}

/* Store info */
.po-store-info {}
/* body.theme-dark .po-store-name { color:#f9fafb; }
   body.theme-light .po-store-name { color:var(--orange-strong); } */

.po-store-meta {
    margin:2px 0;
    font-size:13px;
}
body.theme-dark .po-store-meta {
    color:#9ca3af;
}
body.theme-light .po-store-meta {
    color:#7c2d12;
}

/* Right meta block */
.po-doc-right {
    text-align:right;
    font-size:13px;
}
body.theme-dark .po-doc-right {
    color:#d1d5db;
}
body.theme-light .po-doc-right {
    color:var(--orange-main);
}

/* Meta grid */
.po-meta-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:12px;
    margin-bottom:16px;
}

/* Form elements (theme-aware) */
.po-card .form-group {
    display:flex;
    flex-direction:column;
    gap:4px;
}
.po-card label {
    font-size:0.9rem;
    font-weight:500;
}

body.theme-dark .po-card label {
    color:#e5e7eb;
}
body.theme-light .po-card label {
    color:var(--orange-main);
}

.po-card input,
.po-card textarea,
.po-card select {
    border-radius:8px;
    padding:8px 10px;
    font-size:0.9rem;
}

/* Dark inputs */
body.theme-dark .po-card input,
body.theme-dark .po-card textarea,
body.theme-dark .po-card select {
    background:#020617;
    border:1px solid #4b5563;
    color:#f9fafb;
}

/* Light inputs */
body.theme-light .po-card input,
body.theme-light .po-card textarea,
body.theme-light .po-card select {
    background:#ffffff;
    border:1px solid rgba(209,213,219,0.9);
    color:var(--orange-main);
}

/* Specific input helpers */
.po-input {
    width:100%;
}
.po-input-number {
    max-width:120px;
}

/* Table */
.po-table-wrapper {
    width:100%;
    overflow-x:auto;
}
.po-items-table {
    width:100%;
    border-collapse:collapse;
    min-width:720px;
}
.po-items-table thead {
    background: linear-gradient(to right, #1d4ed8, #1e40af);
}
body.theme-light .po-items-table thead {
    background: linear-gradient(to right, #fed7aa, #fdba74);
}
.po-items-table thead th {
    padding:8px 10px;
    font-size:0.8rem;
    text-transform:uppercase;
    letter-spacing:0.06em;
}
body.theme-dark .po-items-table thead th {
    color:#e5e7eb;
}
body.theme-light .po-items-table thead th {
    color:var(--orange-strong);
}

.po-items-table tbody td,
.po-items-table tfoot td {
    padding:6px 8px;
    font-size:0.9rem;
}

/* Rows striping + hover */
body.theme-dark .po-items-table tbody tr {
    border-bottom:1px solid rgba(55,65,81,0.7);
}
body.theme-dark .po-items-table tbody tr:hover {
    background:rgba(30,64,175,0.25);
}

body.theme-light .po-items-table tbody tr {
    border-bottom:1px solid rgba(229,231,235,0.9);
}
body.theme-light .po-items-table tbody tr:nth-child(even) {
    background:rgba(255,255,255,0.98);
}
body.theme-light .po-items-table tbody tr:nth-child(odd) {
    background:rgba(255,255,255,0.94);
}
body.theme-light .po-items-table tbody tr:hover {
    background:rgba(254,243,199,0.9);
}

/* Alignment helpers */
.text-right { text-align:right; }
.text-center { text-align:center; }

/* "Add item" row cell */
.po-add-row-cell {
    padding-top:10px;
}

/* Total label */
.po-total-label {
    font-weight:600;
}

/* Row buttons */
.btn-row {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:4px 10px;
    font-size:0.8rem;
    border-radius:999px;
    border:1px solid transparent;
    font-weight:600;
    cursor:pointer;
    transition:background 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
}

/* Add item button – text dark orange */
.btn-add-item {
    background:transparent;
    border-style:dashed;
}

/* Same look on dark & light, just adjust background a bit */
body.theme-dark .btn-add-item {
    border-color:rgba(249,115,22,0.6);
    color:var(--orange-light);
    background:rgba(15,23,42,0.8);
}
body.theme-dark .btn-add-item:hover {
    background:rgba(30,64,175,0.85);
    transform:translateY(-1px);
}

body.theme-light .btn-add-item {
    border-color:var(--orange-strong);
    color:var(--orange-strong);   /* requested: dark orange text */
    background:#fff7ed;
}
body.theme-light .btn-add-item:hover {
    background:#ffedd5;
    transform:translateY(-1px);
}

/* Delete row button */
.btn-delete-row {
    background:rgba(239,68,68,0.9);
    border-color:transparent;
    color:#fee2e2;
    padding-inline:8px;
}
.btn-delete-row:hover {
    background:rgba(220,38,38,1);
    transform:translateY(-1px);
}

/* Notes */
.po-notes-wrap {
    margin-top:16px;
}
.po-notes-label {
    font-size:13px;
    font-weight:600;
    display:block;
    margin-bottom:4px;
}
.po-notes-textarea {
    resize:vertical;
}

/* Notes label colors */
body.theme-dark .po-notes-label {
    color:#e5e7eb;
}
body.theme-light .po-notes-label {
    color:var(--orange-main);
}

/* Signatures */
.po-signatures-grid {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(220px,1fr));
    gap:24px;
}
.po-sign-title {
    font-weight:600;
    margin-bottom:4px;
}
.po-sign-input {}
.po-sign-line {
    border-top:1px solid #9ca3af;
    margin-top:28px;
}
.po-sign-caption {
    font-size:12px;
    margin-top:4px;
}
body.theme-dark .po-sign-caption {
    color:#9ca3af;
}
body.theme-light .po-sign-caption {
    color:#7c2d12;
}

/* Actions */
.po-actions {
    margin-top:24px;
    display:flex;
    justify-content:flex-end;
    gap:8px;
    flex-wrap:wrap;
}

/* Submit button can inherit global .btn-primary, but tweak for light */
body.theme-light .po-submit-btn {
    background:var(--orange-light);
}
body.theme-light .po-submit-btn:hover {
    background:var(--orange-light-hover);
}

/* Responsive */
@media (max-width:768px) {
    .po-card {
        padding:14px;
    }
}

/* Print styles */
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

    #orderDocument,
    #orderDocument * {
        visibility: visible !important;
    }

    #orderDocument {
        margin: 0;
        padding: 0;
        box-shadow: none !important;
        border:none !important;
    }

    .po-header,
    .header-actions,
    .page-header .btn-primary,
    button,
    a {
        display: none !important;
    }
}
</style>
@endsection
