@extends('admin.layout')

@section('title', 'New Purchase')

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h1>New Purchase</h1>
    </div>

    {{-- Redirect / non-AJAX errors --}}
    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- AJAX validation errors --}}
    <div class="alert alert-error" id="ajaxErrorBox" style="display:none; margin-top:10px;">
        <ul id="ajaxErrorList"></ul>
    </div>

    <div class="card">
        <form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm">
            @csrf

            {{-- Top form --}}
            <div class="grid-layout">
                <div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <select name="supplier_id" required>
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Purchase Date</label>
                        <input type="date" name="purchase_date"
                               value="{{ old('purchase_date', now()->toDateString()) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Reference (Invoice #)</label>
                        <input type="text" name="reference" value="{{ old('reference') }}">
                    </div>

                </div>

                <div>
                    <div class="form-group">
                        <label>Discount</label>
                        <input type="number" step="0.01" name="discount"
                               value="{{ old('discount', 0) }}">
                    </div>
                    <div class="form-group">
                        <label>Tax</label>
                        <input type="number" step="0.01" name="tax"
                               value="{{ old('tax', 0) }}">
                    </div>
                    <div class="form-group">
                        <label>Amount Paid</label>
                        <input type="number" step="0.01" name="amount_paid"
                               value="{{ old('amount_paid', 0) }}">
                    </div>
                </div>
            </div>

            <hr>

            {{-- New Product quick-add --}}
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="checkbox" id="newProductCheckbox">
                    <span>Add new product</span>
                </label>

                <span style="font-size:12px; color:#9ca3af;">
                    Check to add a new product without leaving this page.
                </span>
            </div>

            {{-- AJAX success for new product --}}
            <div class="alert alert-success" id="newProductSuccess" style="display:none; margin-bottom:10px;">
            </div>

            {{-- Items table --}}
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 26%">Product</th>
                            <th style="width: 12%">Barcode</th>
                            <th style="width: 8%">Qty</th>
                            <th style="width: 12%">Cost Price</th>
                            <th style="width: 12%">Selling Price</th>
                            <th style="width: 12%">Expiry</th>
                            <th style="width: 12%">Line Total</th>
                            <th style="width: 6%"></th>
                        </tr>
                    </thead>

                    <tbody id="itemsTableBody">
                        {{-- initial row --}}
                        <tr>
                            <td>
                                {{-- Combined existing/new product input --}}
                                <input type="text"
                                       name="items[0][product]"
                                       class="product-input"
                                       list="productList"
                                       placeholder="Select or type new product">

                                <datalist id="productList">
                                    @foreach($products as $p)
                                        <option value="{{ $p->name }} ({{ $p->sku }})"
                                                data-id="{{ $p->id }}"
                                                data-name="{{ $p->name }}"
                                                data-sku="{{ $p->sku }}"
                                                data-barcode="{{ $p->barcode }}"
                                                data-purchase-price="{{ $p->purchase_price }}"
                                                data-selling-price="{{ $p->selling_price }}"></option>
                                    @endforeach
                                </datalist>

                                {{-- These are what the controller actually uses --}}
                                <input type="hidden" name="items[0][product_id]" class="product-id-field">
                                <input type="hidden" name="items[0][name]"       class="product-name-field">
                                <input type="hidden" name="items[0][sku]"        class="product-sku-field">
                                <input type="hidden" name="items[0][barcode]"    class="product-barcode-hidden">
                            </td>

                            {{-- BARCODE CELL --}}
                            <td>
                                <input type="text"
                                       name="items[0][barcode_input]"
                                       class="product-barcode-input"
                                       list="barcodeList"
                                       placeholder="Scan / enter barcode">
                            </td>

                            {{-- BARCODE DATALIST --}}
                            <datalist id="barcodeList">
                                @foreach($products as $p)
                                    @if($p->barcode)
                                        <option value="{{ $p->barcode }}"
                                                data-id="{{ $p->id }}"
                                                data-name="{{ $p->name }}"
                                                data-sku="{{ $p->sku }}"
                                                data-purchase-price="{{ $p->purchase_price }}"
                                                data-selling-price="{{ $p->selling_price }}"></option>
                                    @endif
                                @endforeach
                            </datalist>

                            <td>
                                <input type="number" name="items[0][quantity]" class="item-qty" min="1" value="1">
                            </td>

                            <td>
                                <input type="number" name="items[0][cost_price]" class="item-cost" min="0" step="0.01" value="0">
                            </td>

                            <td>
                                <input type="number" name="items[0][selling_price]" class="item-selling" min="0" step="0.01" value="0">
                            </td>

                            <td>
                                <input type="date" name="items[0][expiry_date]" class="item-expiry">
                            </td>

                            <td>
                                <span class="item-line-total">0.00</span>
                            </td>

                            <td>
                                <button type="button" class="btn-small btn-delete remove-item-btn">🗑</button>
                            </td>
                        </tr>
                    </tbody>

                </table>

                <div style="margin-top: 8px;">
                    <button type="button" class="btn-secondary" id="addItemBtn">+ Add Item</button>
                </div>
            </div>

            {{-- Summary --}}
            <div style="margin-top: 24px;">
                <h3>Items Summary</h3>
                <div id="itemsSummary">
                    <em>No items yet.</em>
                </div>
            </div>

            <div style="margin-top: 16px; text-align:right;">
                <button type="submit" class="btn-primary">Save Purchase</button>
            </div>
        </form>
    </div>
</div>

{{-- Summary modal --}}
<div class="modal-overlay hidden" id="purchaseSummaryModal">
    <div class="modal-card">
        <div class="modal-header">
            <h2 id="purchaseSummaryTitle">Purchase saved</h2>
            <button type="button" class="modal-close" id="purchaseSummaryClose">&times;</button>
        </div>

        <div class="modal-body" id="purchaseSummaryBody">
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="purchaseSummaryCloseFooter">
                Close
            </button>
        </div>
    </div>
</div>

{{-- New Product Modal --}}
<div class="modal-overlay hidden" id="newProductModal">
    <div class="modal-card" style="max-width: 520px;">
        <div class="modal-header">
            <h2>New Product</h2>
            <button type="button" class="modal-close" id="newProductClose">&times;</button>
        </div>

        <div class="modal-body">
            <form id="newProductForm">
                @csrf

                <div class="form-group">
                    <label>Product Name <span style="color:#f97373">*</span></label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>SKU <span style="color:#f97373">*</span></label>
                    <input type="text" name="sku" required>
                </div>

                <div class="form-group">
                    <label>Barcode</label>
                    <input type="text" name="barcode" autofocus>
                </div>

                <div class="form-group">
                    <label>Purchase Price <span style="color:#f97373">*</span></label>
                    <input type="number" step="0.01" name="purchase_price" value="0" min="0" required>
                </div>

                <div class="form-group">
                    <label>Selling Price <span style="color:#f97373">*</span></label>
                    <input type="number" step="0.01" name="selling_price" value="0" min="0" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category">
                </div>

                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" name="brand">
                </div>

                <div class="form-group">
                    <label>Supplier (name)</label>
                    <input type="text" name="supplier">
                </div>

                <div class="form-group">
                    <input type="hidden" name="is_vatable" value="0">
                    <label>
                        <input type="checkbox" name="is_vatable" value="1"
                            {{ old('is_vatable', true) ? 'checked' : '' }}>
                        VATable Product
                    </label>
                    <small>Uncheck for VAT-exempt items (e.g. some basic foods).</small>
                </div>

                {{-- hidden defaults so stock comes from purchase, not here --}}
                <input type="hidden" name="quantity" value="0">
                <input type="hidden" name="reorder_level" value="10">
                <input type="hidden" name="is_suspended" value="0">

                <div id="newProductErrorBox" class="alert alert-error" style="display:none; margin-top:10px;">
                    <ul id="newProductErrorList"></ul>
                </div>

                <div class="modal-footer" style="margin-top: 16px; justify-content:flex-end;">
                    <button type="button" class="btn-secondary" id="newProductCancel">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        Save Product
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Full Page Loading Overlay -->
<div id="loading-overlay">
    <div class="spinner"></div>
</div>

{{-- THEME-AWARE STYLES FOR NEW PURCHASE --}}
<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
    --muted-text: #7c2d12;
}

/* ---------- TEXT & FORM COLORS ---------- */

/* Dark theme text */
body.theme-dark .customers-page,
body.theme-dark .customers-page h1,
body.theme-dark .customers-page h2,
body.theme-dark .customers-page h3,
body.theme-dark .customers-page label,
body.theme-dark .customers-page td,
body.theme-dark .customers-page th {
    color: #f9fafb;
}

/* Light theme text */
body.theme-light .customers-page,
body.theme-light .customers-page h1,
body.theme-light .customers-page h2,
body.theme-light .customers-page h3,
body.theme-light .customers-page label,
body.theme-light .customers-page td,
body.theme-light .customers-page th {
    color: var(--orange-main);
}

/* Inputs, selects, textarea */
body.theme-dark .customers-page input,
body.theme-dark .customers-page select,
body.theme-dark .customers-page textarea {
    color: #ffffff;
    background-color: #020617;
    border: 1px solid #4b5563;
}

body.theme-light .customers-page input,
body.theme-light .customers-page select,
body.theme-light .customers-page textarea {
    color: var(--orange-main);
    background-color: rgba(255,255,255,0.98);
    border: 1px solid rgba(209,213,219,0.9);
}

.customers-page input::placeholder,
.customers-page textarea::placeholder {
    color: #9ca3af;
}

/* Delete button in items table (keep strong red) */
.btn-small.btn-delete {
    background: rgba(239,68,68,0.9);
    color: #fee2e2;
}
.btn-small.btn-delete:hover {
    background: rgba(239,68,68,1);
}

/* ---------- ITEMS SUMMARY TABLE ---------- */
#itemsSummary table {
    width: 100%;
    border-collapse: collapse;
}

/* dark */
body.theme-dark #itemsSummary table {
    background-color: #020617;
}
body.theme-dark #itemsSummary th,
body.theme-dark #itemsSummary td {
    color: #f9fafb;
    border-bottom: 1px solid rgba(148, 163, 184, 0.4);
}
body.theme-dark #itemsSummary tbody tr:nth-child(even) {
    background-color: rgba(15, 23, 42, 0.7);
}
body.theme-dark #itemsSummary tbody tr:nth-child(odd) {
    background-color: rgba(15, 23, 42, 0.4);
}

/* light */
body.theme-light #itemsSummary table {
    background-color: rgba(255,255,255,0.96);
}
body.theme-light #itemsSummary th,
body.theme-light #itemsSummary td {
    color: var(--orange-main);
    border-bottom: 1px solid rgba(229,231,235,0.9);
}
body.theme-light #itemsSummary tbody tr:nth-child(even) {
    background-color: rgba(255,255,255,0.98);
}
body.theme-light #itemsSummary tbody tr:nth-child(odd) {
    background-color: rgba(255,247,237,0.96);
}

/* Shared cell padding */
#itemsSummary th,
#itemsSummary td {
    padding: 6px 10px;
    font-size: 13px;
}

/* ---------- ITEMS TABLE WRAPPER (keep it inside screen) ---------- */
.table-wrapper {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    margin-top: 10px;
}

.table-wrapper table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
}

.table-wrapper th,
.table-wrapper td {
    padding: 6px 8px;
    font-size: 13px;
    white-space: nowrap;
}

.table-wrapper input {
    width: 100%;
    padding: 4px 6px;
    font-size: 13px;
}

/* Make the trash icon button small inside table */
.table-wrapper .btn-delete {
    padding: 2px 6px;
    font-size: 12px;
}

/* ---------- ALERTS (error + success) ---------- */
.alert {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

/* Dark theme alerts */
body.theme-dark .alert-success {
    background: rgba(22, 163, 74, 0.15);
    border: 1px solid rgba(22, 163, 74, 0.6);
    color: #bbf7d0;
}
body.theme-dark .alert-error {
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(248, 113, 113, 0.7);
    color: #fecaca;
}

/* Light theme alerts */
body.theme-light .alert-success {
    background: rgba(22,163,74,0.08);
    border: 1px solid rgba(22,163,74,0.6);
    color: #166534;
}
body.theme-light .alert-error {
    background: rgba(248,113,113,0.08);
    border: 1px solid rgba(248,113,113,0.7);
    color: #b91c1c;
}

/* ---------- LOADING OVERLAY (theme aware) ---------- */
#loading-overlay {
    position: fixed;
    inset: 0;
    display: none;
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

/* dark overlay */
body.theme-dark #loading-overlay {
    background: rgba(0,0,0,0.55);
}

/* light overlay – soft, not harsh white */
body.theme-light #loading-overlay {
    background: rgba(255,255,255,0.07);
    backdrop-filter: none;
}

#loading-overlay .spinner {
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

/* dark spinner */
body.theme-dark #loading-overlay .spinner {
    border: 6px solid rgba(255,255,255,0.3);
    border-top: 6px solid #2563eb;
}

/* light spinner */
body.theme-light #loading-overlay .spinner {
    border: 6px solid rgba(255,255,255,0.5);
    border-top: 6px solid #ea580c;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
body.theme-light .pos-logo {
    background: transparent;
    padding: 0;
}

</style>
@endsection






@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addItemBtn      = document.getElementById('addItemBtn');
    const itemsTableBody  = document.getElementById('itemsTableBody');
    const itemsSummary    = document.getElementById('itemsSummary');

    const discountInput   = document.querySelector('input[name="discount"]');
    const taxInput        = document.querySelector('input[name="tax"]');
    const paidInput       = document.querySelector('input[name="amount_paid"]');
    const purchaseForm    = document.getElementById('purchaseForm');

    const ajaxErrorBox  = document.getElementById('ajaxErrorBox');
    const ajaxErrorList = document.getElementById('ajaxErrorList');

    const summaryModal        = document.getElementById('purchaseSummaryModal');
    const summaryModalBody    = document.getElementById('purchaseSummaryBody');
    const summaryModalTitle   = document.getElementById('purchaseSummaryTitle');
    const summaryCloseBtn     = document.getElementById('purchaseSummaryClose');
    const summaryCloseFooter  = document.getElementById('purchaseSummaryCloseFooter');

    const productListEl  = document.getElementById('productList');
    const barcodeListEl  = document.getElementById('barcodeList');

    let rowIndex = 1; // row[0] already in HTML
    let lastFocusedProductInput = null;

    function formatMoney(value) {
        const num = Number(value || 0);
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function openSummaryModal() {
        if (summaryModal) summaryModal.classList.remove('hidden');
    }
    function closeSummaryModal() {
        if (summaryModal) summaryModal.classList.add('hidden');
    }
    if (summaryCloseBtn) summaryCloseBtn.addEventListener('click', closeSummaryModal);
    if (summaryCloseFooter) summaryCloseFooter.addEventListener('click', closeSummaryModal);
    if (summaryModal) {
        summaryModal.addEventListener('click', function (e) {
            if (e.target === summaryModal) closeSummaryModal();
        });
    }

    // Track which product input was last focused (for new product auto-fill)
    itemsTableBody.addEventListener('focusin', function (e) {
        if (e.target.classList.contains('product-input')) {
            lastFocusedProductInput = e.target;
        }
    });

    // ---------- Helpers to sync existing product from name/sku or barcode ----------

    function syncProductFromNameInput(input) {
        if (!productListEl) return;

        const row        = input.closest('tr');
        if (!row) return;

        const hiddenId   = row.querySelector('.product-id-field');
        const hiddenName = row.querySelector('.product-name-field');
        const hiddenSku  = row.querySelector('.product-sku-field');
        const barcodeInp = row.querySelector('.product-barcode-input');
        const costInput  = row.querySelector('.item-cost');
        const sellInput  = row.querySelector('.item-selling');

        const value = (input.value || '').trim().toLowerCase();
        let match = null;

        productListEl.querySelectorAll('option').forEach(opt => {
            if (opt.value.trim().toLowerCase() === value) {
                match = opt;
            }
        });

        if (match) {
            const id      = match.dataset.id || '';
            const name    = match.dataset.name || '';
            const sku     = match.dataset.sku || '';
            const barcode = match.dataset.barcode || '';
            const cost    = match.dataset.purchasePrice || '';
            const sell    = match.dataset.sellingPrice || '';

            if (hiddenId)   hiddenId.value   = id;
            if (hiddenName) hiddenName.value = name;
            if (hiddenSku)  hiddenSku.value  = sku;
            if (barcodeInp && barcode) barcodeInp.value = barcode;
            if (costInput && cost !== '')    costInput.value = cost;
            if (sellInput && sell !== '')    sellInput.value = sell;
        } else {
            // Treat as new product: fill hidden name/sku from typed value
            if (hiddenId)   hiddenId.value   = '';
            if (hiddenName) hiddenName.value = input.value;

            if (hiddenSku) {
                // Try to grab sku from pattern "Name (SKU)"
                const m = input.value.match(/\(([^)]+)\)$/);
                hiddenSku.value = m ? m[1] : '';
            }
        }
    }

    function syncProductFromBarcodeInput(input) {
        if (!barcodeListEl) return;

        const row = input.closest('tr');
        if (!row) return;

        const productInput = row.querySelector('.product-input');
        const hiddenId     = row.querySelector('.product-id-field');
        const hiddenName   = row.querySelector('.product-name-field');
        const hiddenSku    = row.querySelector('.product-sku-field');
        const costInput    = row.querySelector('.item-cost');
        const sellInput    = row.querySelector('.item-selling');

        const value = (input.value || '').trim().toLowerCase();
        let match = null;

        barcodeListEl.querySelectorAll('option').forEach(opt => {
            if (opt.value.trim().toLowerCase() === value) {
                match = opt;
            }
        });

        if (!match) {
            // Unknown barcode → clear link to any existing product
            if (hiddenId)   hiddenId.value   = '';
            if (hiddenName) hiddenName.value = '';
            if (hiddenSku)  hiddenSku.value  = '';
            return;
        }

        const id   = match.dataset.id || '';
        const name = match.dataset.name || '';
        const sku  = match.dataset.sku || '';
        const cost = match.dataset.purchasePrice || '';
        const sell = match.dataset.sellingPrice || '';

        if (productInput) productInput.value = name + (sku ? ' (' + sku + ')' : '');
        if (hiddenId)     hiddenId.value     = id;
        if (hiddenName)   hiddenName.value   = name;
        if (hiddenSku)    hiddenSku.value    = sku;
        if (costInput && cost !== '')        costInput.value = cost;
        if (sellInput && sell !== '')        sellInput.value = sell;

        const evt = new Event('input', { bubbles: true });
        if (productInput) productInput.dispatchEvent(evt);
        if (costInput)    costInput.dispatchEvent(evt);
        if (sellInput)    sellInput.dispatchEvent(evt);
    }

    // ---------- Rows ----------

    // Clone the first row to create new rows
    function addItemRow() {
        const firstRow = itemsTableBody.querySelector('tr');
        if (!firstRow) return;

        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input').forEach(function (el) {
            if (el.name) {
                el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            }

            if (el.classList.contains('product-input')) {
                el.value = '';
            } else if (el.classList.contains('product-barcode-input')) {
                el.value = '';
            } else if (el.classList.contains('product-id-field')) {
                el.value = '';
            } else if (el.classList.contains('product-name-field')) {
                el.value = '';
            } else if (el.classList.contains('product-sku-field')) {
                el.value = '';
            } else if (el.classList.contains('item-qty')) {
                el.value = 1;
            } else if (el.classList.contains('item-cost') || el.classList.contains('item-selling')) {
                el.value = 0;
            } else if (el.classList.contains('item-expiry')) {
                el.value = '';
            }
        });

        const lineSpan = newRow.querySelector('.item-line-total');
        if (lineSpan) lineSpan.textContent = '0.00';

        rowIndex++;
        itemsTableBody.appendChild(newRow);
        updateSummary();
    }

    function updateRowLineTotal(row) {
        const qtyInput  = row.querySelector('.item-qty');
        const costInput = row.querySelector('.item-cost');
        const lineSpan  = row.querySelector('.item-line-total');
        if (!qtyInput || !costInput || !lineSpan) return;

        const qty  = parseFloat(qtyInput.value || 0);
        const cost = parseFloat(costInput.value || 0);
        const line = qty * cost;

        lineSpan.textContent = formatMoney(line);
    }

    function updateSummary() {
        const rows = itemsTableBody.querySelectorAll('tr');
        const summaryData = [];
        let subtotal = 0;

        rows.forEach(function (row) {
            const productInput = row.querySelector('.product-input');
            const qtyInput     = row.querySelector('.item-qty');
            const costInput    = row.querySelector('.item-cost');
            const sellInput    = row.querySelector('.item-selling');
            const expiryInput  = row.querySelector('.item-expiry');

            if (!productInput || !qtyInput || !costInput || !sellInput || !expiryInput) {
                return;
            }

            const productName = (productInput.value || '').trim();
            const qty         = qtyInput.value;
            const cost        = costInput.value;
            const sell        = sellInput.value;
            const expiry      = expiryInput.value;

            const hasAnyData = productName || qty || cost || sell || expiry;
            if (!hasAnyData) return;

            const qtyNum  = parseFloat(qty) || 0;
            const costNum = parseFloat(cost) || 0;

            if (productName && qty && cost) {
                subtotal += qtyNum * costNum;
            }

            summaryData.push({
                product: productName || '(no product name)',
                qty: qtyNum,
                cost: costNum,
                sell: parseFloat(sell) || 0,
                expiry: expiry,
            });

            updateRowLineTotal(row);
        });

        if (summaryData.length === 0) {
            itemsSummary.innerHTML = '<em>No items yet.</em>';
            return;
        }

        const discount = parseFloat(discountInput?.value || 0);
        const tax      = parseFloat(taxInput?.value || 0);
        const paid     = parseFloat(paidInput?.value || 0);

        const total   = subtotal - discount + tax;
        const balance = total - paid;

        let html = '<table style="width:100%; margin-top:8px;">';
        html += '<thead><tr><th>Product</th><th>Qty</th><th>Cost</th><th>Line Total</th><th>Expiry</th></tr></thead><tbody>';

        summaryData.forEach(function (item) {
            const line = item.qty * item.cost;
            html += '<tr>' +
                '<td>' + item.product + '</td>' +
                '<td>' + (item.qty || '') + '</td>' +
                '<td>' + (item.cost ? formatMoney(item.cost) : '') + '</td>' +
                '<td>' + (line ? formatMoney(line) : '') + '</td>' +
                '<td>' + (item.expiry || '-') + '</td>' +
                '</tr>';
        });

        html += '</tbody></table>';

        html += '<div style="margin-top: 12px; max-width: 320px; margin-left:auto;">';
        html += '<table style="width:100%; font-size:13px;"><tbody>';
        html += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Subtotal</td>' +
                '<td style="padding:4px 8px; text-align:right;">' + formatMoney(subtotal) + '</td></tr>';
        html += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Discount</td>' +
                '<td style="padding:4px 8px; text-align:right;">- ' + formatMoney(discount) + '</td></tr>';
        html += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Tax</td>' +
                '<td style="padding:4px 8px; text-align:right;">+ ' + formatMoney(tax) + '</td></tr>';
        html += '<tr><td style="padding:4px 8px; text-align:right; font-weight:600;">Total</td>' +
                '<td style="padding:4px 8px; text-align:right; font-weight:600;">' + formatMoney(total) + '</td></tr>';
        html += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Amount Paid</td>' +
                '<td style="padding:4px 8px; text-align:right;">' + formatMoney(paid) + '</td></tr>';
        html += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Balance</td>' +
                '<td style="padding:4px 8px; text-align:right;">' + formatMoney(balance) + '</td></tr>';
        html += '</tbody></table></div>';

        itemsSummary.innerHTML = html;
    }

    // Add row button
    if (addItemBtn) addItemBtn.addEventListener('click', addItemRow);

    // Live summary updates + product / barcode syncing
    itemsTableBody.addEventListener('input', function (e) {
        if (e.target.classList.contains('product-input')) {
            syncProductFromNameInput(e.target);
            updateSummary();
            return;
        }

        if (e.target.classList.contains('product-barcode-input')) {
            syncProductFromBarcodeInput(e.target);
            updateSummary();
            return;
        }

        if (
            e.target.classList.contains('item-qty') ||
            e.target.classList.contains('item-cost') ||
            e.target.classList.contains('item-selling') ||
            e.target.classList.contains('item-expiry')
        ) {
            updateSummary();
        }
    });

    itemsTableBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item-btn')) {
            const row = e.target.closest('tr');
            if (row) row.remove();
            updateSummary();
        }
    });

    [discountInput, taxInput, paidInput].forEach(function (input) {
        if (!input) return;
        input.addEventListener('input', updateSummary);
    });

    // initial summary
    updateSummary();

    // ---------- PURCHASE AJAX SUBMIT ----------
    purchaseForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const url    = purchaseForm.action;
        const method = purchaseForm.method || 'POST';
        const formData = new FormData(purchaseForm);

        const tokenInput = document.querySelector('input[name="_token"]');
        const token = tokenInput ? tokenInput.value : '';

        if (ajaxErrorList && ajaxErrorBox) {
            ajaxErrorList.innerHTML = '';
            ajaxErrorBox.style.display = 'none';
        }

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(async (response) => {
            if (response.status === 422) {
                const data = await response.json();
                if (data.errors && ajaxErrorList && ajaxErrorBox) {
                    Object.values(data.errors).forEach(function (msgs) {
                        msgs.forEach(function (msg) {
                            const li = document.createElement('li');
                            li.textContent = msg;
                            ajaxErrorList.appendChild(li);
                        });
                    });
                    ajaxErrorBox.style.display = 'block';
                }
                throw new Error('Validation error');
            }

            if (!response.ok) {
                window.location.href = "{{ route('admin.purchases.index') }}";
                return;
            }

            return response.json();
        })
        .then((data) => {
            if (!data) return;
            const p = data.purchase;

            summaryModalTitle.textContent = 'Purchase #' + p.id + ' saved';

            let modalHtml = '';
            modalHtml += '<div class="customer-details-body">';
            modalHtml += '  <div class="detail-row"><div class="detail-label">Supplier</div>' +
                         '  <div class="detail-value">' + p.supplier_name + '</div></div>';
            modalHtml += '  <div class="detail-row"><div class="detail-label">Date</div>' +
                         '  <div class="detail-value">' + p.purchase_date + '</div></div>';
            modalHtml += '  <div class="detail-row"><div class="detail-label">Reference</div>' +
                         '  <div class="detail-value">' + (p.reference ?? '—') + '</div></div>';
            modalHtml += '  <div class="detail-row"><div class="detail-label">Status</div>' +
                         '  <div class="detail-value"><span class="badge">' +
                         p.payment_status.charAt(0).toUpperCase() + p.payment_status.slice(1) +
                         '</span></div></div>';
            modalHtml += '</div>';

            modalHtml += '<hr style="margin:14px 0;">';

            modalHtml += '<div style="max-width:320px; margin-left:auto;">';
            modalHtml += '<table style="width:100%; font-size:13px;"><tbody>';
            modalHtml += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Subtotal</td>' +
                         '<td style="padding:4px 8px; text-align:right;">' + formatMoney(p.subtotal) + '</td></tr>';
            modalHtml += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Discount</td>' +
                         '<td style="padding:4px 8px; text-align:right;">- ' + formatMoney(p.discount) + '</td></tr>';
            modalHtml += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Tax</td>' +
                         '<td style="padding:4px 8px; text-align:right;">+ ' + formatMoney(p.tax) + '</td></tr>';
            modalHtml += '<tr><td style="padding:4px 8px; text-align:right; font-weight:600;">Total</td>' +
                         '<td style="padding:4px 8px; text-align:right; font-weight:600;">' + formatMoney(p.total) + '</td></tr>';
            modalHtml += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Amount Paid</td>' +
                         '<td style="padding:4px 8px; text-align:right;">' + formatMoney(p.amount_paid) + '</td></tr>';
            modalHtml += '<tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Balance</td>' +
                         '<td style="padding:4px 8px; text-align:right;">' + formatMoney(p.balance) + '</td></tr>';
            modalHtml += '</tbody></table></div>';

            modalHtml += '<hr style="margin:14px 0;">';
            modalHtml += '<h3 style="font-size:15px; margin-bottom:6px;">Items & Totals</h3>';
            modalHtml += itemsSummary.innerHTML;

            summaryModalBody.innerHTML = modalHtml;
            openSummaryModal();

            // after save, go back to index
            window.location.href = "{{ route('admin.purchases.index') }}";
        })
        .catch((err) => {
            console.error(err);
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const newProductCheckbox  = document.getElementById('newProductCheckbox');
    const newProductModal     = document.getElementById('newProductModal');
    const newProductForm      = document.getElementById('newProductForm');
    const newProductClose     = document.getElementById('newProductClose');
    const newProductCancel    = document.getElementById('newProductCancel');
    const newProductSuccess   = document.getElementById('newProductSuccess');
    const newProductErrorBox  = document.getElementById('newProductErrorBox');
    const newProductErrorList = document.getElementById('newProductErrorList');
    const productDataList     = document.getElementById('productList');
    const barcodeListEl       = document.getElementById('barcodeList');
    const itemsTableBody      = document.getElementById('itemsTableBody');

    // Reuse: which product input was last focused (so we know which row to fill)
    let lastFocusedProductInput = null;
    if (itemsTableBody) {
        itemsTableBody.addEventListener('focusin', function(e) {
            if (e.target.classList.contains('product-input')) {
                lastFocusedProductInput = e.target;
            }
        });
    }

    if (!newProductCheckbox || !newProductModal || !newProductForm) {
        return;
    }

    function openNewProductModal() {
        newProductModal.classList.remove('hidden');
    }

    function closeNewProductModal() {
        newProductModal.classList.add('hidden');
        newProductCheckbox.checked = false;
    }

    newProductCheckbox.addEventListener('change', function () {
        if (this.checked) {
            openNewProductModal();
        } else {
            closeNewProductModal();
        }
    });

    if (newProductClose) {
        newProductClose.addEventListener('click', closeNewProductModal);
    }

    if (newProductCancel) {
        newProductCancel.addEventListener('click', function (e) {
            e.preventDefault();
            closeNewProductModal();
        });
    }

    newProductModal.addEventListener('click', function (e) {
        if (e.target === newProductModal) {
            closeNewProductModal();
        }
    });

    newProductForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // clear previous errors
        if (newProductErrorList && newProductErrorBox) {
            newProductErrorList.innerHTML = '';
            newProductErrorBox.style.display = 'none';
        }

        const url       = "{{ route('admin.products.store') }}";
        const formData  = new FormData(newProductForm);
        const tokenInput= document.querySelector('input[name="_token"]');
        const token     = tokenInput ? tokenInput.value : '';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(async (response) => {
            if (response.status === 422) {
                const data = await response.json();
                if (data.errors && newProductErrorList && newProductErrorBox) {
                    Object.values(data.errors).forEach(function (msgs) {
                        msgs.forEach(function (msg) {
                            const li = document.createElement('li');
                            li.textContent = msg;
                            newProductErrorList.appendChild(li);
                        });
                    });
                    newProductErrorBox.style.display = 'block';
                }
                throw new Error('Validation error');
            }

            if (!response.ok) {
                throw new Error('Failed to create product');
            }

            return response.json();
        })
        .then((data) => {
            if (!data || !data.product) return;

            const p = data.product;

            // 1) Add to name/sku datalist so it's available immediately for all rows
            if (productDataList) {
                const opt = document.createElement('option');
                opt.value                 = p.name + ' (' + p.sku + ')';
                opt.dataset.id            = p.id;
                opt.dataset.name          = p.name;
                opt.dataset.sku           = p.sku ?? '';
                opt.dataset.barcode       = p.barcode ?? '';
                opt.dataset.purchasePrice = p.purchase_price;
                opt.dataset.sellingPrice  = p.selling_price;
                productDataList.appendChild(opt);
            }

            // 1b) Add to barcode datalist too
            if (barcodeListEl && p.barcode) {
                const optB = document.createElement('option');
                optB.value                 = p.barcode;
                optB.dataset.id            = p.id;
                optB.dataset.name          = p.name;
                optB.dataset.sku           = p.sku ?? '';
                optB.dataset.purchasePrice = p.purchase_price;
                optB.dataset.sellingPrice  = p.selling_price;
                barcodeListEl.appendChild(optB);
            }

            // 2) Fill the active row's product input + hidden ID + prices + barcode
            let targetInput = lastFocusedProductInput;
            if (!targetInput) {
                targetInput = document.querySelector('.product-input');
            }

            if (targetInput) {
                const row          = targetInput.closest('tr');
                const hiddenId     = row.querySelector('.product-id-field');
                const hiddenName   = row.querySelector('.product-name-field');
                const hiddenSku    = row.querySelector('.product-sku-field');
                const costInput    = row.querySelector('.item-cost');
                const sellInput    = row.querySelector('.item-selling');
                const barcodeInput = row.querySelector('.product-barcode-input');

                targetInput.value = p.name + ' (' + p.sku + ')';
                if (hiddenId)   hiddenId.value   = p.id;
                if (hiddenName) hiddenName.value = p.name;
                if (hiddenSku)  hiddenSku.value  = p.sku ?? '';
                if (costInput)  costInput.value  = p.purchase_price ?? 0;
                if (sellInput)  sellInput.value  = p.selling_price ?? 0;
                if (barcodeInput && p.barcode) barcodeInput.value = p.barcode;

                const evt = new Event('input', { bubbles: true });
                if (costInput)  costInput.dispatchEvent(evt);
                if (sellInput)  sellInput.dispatchEvent(evt);
                targetInput.dispatchEvent(evt);
            }

            // 3) Success flash
            if (newProductSuccess) {
                newProductSuccess.textContent = data.message || 'Product created successfully.';
                newProductSuccess.style.display = 'block';
                setTimeout(() => {
                    newProductSuccess.style.display = 'none';
                }, 3000);
            }

            newProductForm.reset();
            closeNewProductModal();
        })
        .catch((err) => {
            console.error(err);
        });
    });
});
</script>
@endpush
