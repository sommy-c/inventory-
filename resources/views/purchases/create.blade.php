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

    <style>
        .customers-page,
        .customers-page h1,
        .customers-page h2,
        .customers-page h3,
        .customers-page label,
        .customers-page td,
        .customers-page th {
            color: #ffffff;
        }

        .customers-page input,
        .customers-page select,
        .customers-page textarea {
            color: #ffffff;
            background-color: #020617;
            border: 1px solid #4b5563;
        }

        .customers-page input::placeholder,
        .customers-page textarea::placeholder {
            color: #9ca3af;
        }

        .btn-small.btn-delete {
            background: rgba(239,68,68,0.9);
        }
        .btn-small.btn-delete:hover {
            background: rgba(239,68,68,1);
        }

        #itemsSummary table {
            width: 100%;
            border-collapse: collapse;
            background-color: #020617;
        }
        #itemsSummary th,
        #itemsSummary td {
            color: #f9fafb;
            padding: 6px 10px;
            font-size: 13px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.4);
        }
        #itemsSummary tbody tr:nth-child(even) {
            background-color: rgba(15, 23, 42, 0.7);
        }
        #itemsSummary tbody tr:nth-child(odd) {
            background-color: rgba(15, 23, 42, 0.4);
        }
    </style>

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
                <th style="width: 30%">Product</th>
                <th style="width: 10%">Qty</th>
                <th style="width: 12%">Cost Price</th>
                <th style="width: 12%">Selling Price</th>
                <th style="width: 12%">Expiry</th>
                <th style="width: 12%">Line Total</th>
                <th style="width: 5%"></th>
            </tr>
        </thead>

        <tbody id="itemsTableBody">
            {{-- initial row --}}
            <tr>
                <td>
                    {{-- Existing or New product combined --}}
                    <input type="text"
                           name="items[0][product]"
                           class="product-input"
                           list="productList"
                           placeholder="Select or type new product">

                    <datalist id="productList">
                        @foreach($products as $p)
                            <option value="{{ $p->name }} ({{ $p->sku }})"
                                    data-id="{{ $p->id }}"
                                    data-sku="{{ $p->sku }}">
                        @endforeach
                    </option>
                    </datalist>

                    <input type="hidden" name="items[0][product_id]" class="product-id-field">
                </td>

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

{{-- Summary modal (unchanged if you already have one) --}}
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
                    <input type="text" name="barcode">
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

@endsection





@push('scripts')
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
            } else if (el.classList.contains('product-id-field')) {
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

    // Live summary updates
    itemsTableBody.addEventListener('input', function (e) {
        if (
            e.target.classList.contains('item-qty') ||
            e.target.classList.contains('item-cost') ||
            e.target.classList.contains('item-selling') ||
            e.target.classList.contains('product-input') ||
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

    // ---------- PURCHASE AJAX SUBMIT (unchanged except using summary) ----------
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
    const newProductCheckbox = document.getElementById('newProductCheckbox');
    const newProductModal    = document.getElementById('newProductModal');
    const newProductForm     = document.getElementById('newProductForm');
    const newProductClose    = document.getElementById('newProductClose');
    const newProductCancel   = document.getElementById('newProductCancel');
    const newProductSuccess  = document.getElementById('newProductSuccess');
    const newProductErrorBox  = document.getElementById('newProductErrorBox');
    const newProductErrorList = document.getElementById('newProductErrorList');
    const productDataList    = document.getElementById('productList');

    // We'll reuse this from main script (last focused product input)
    let lastFocusedProductInput = null;
    document.getElementById('itemsTableBody').addEventListener('focusin', function(e) {
        if (e.target.classList.contains('product-input')) {
            lastFocusedProductInput = e.target;
        }
    });

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

        if (newProductErrorList && newProductErrorBox) {
            newProductErrorList.innerHTML = '';
            newProductErrorBox.style.display = 'none';
        }

        const url = "{{ route('admin.products.store') }}";
        const formData = new FormData(newProductForm);
        const tokenInput = document.querySelector('input[name="_token"]');
        const token = tokenInput ? tokenInput.value : '';

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

            // 1) Add to datalist so it's available immediately
            if (productDataList) {
                const opt = document.createElement('option');
                opt.value = p.name + ' (' + p.sku + ')';
                opt.dataset.id = p.id;
                productDataList.appendChild(opt);
            }

            // 2) Fill the active row's product input + hidden ID + prices
            let targetInput = lastFocusedProductInput;
            if (!targetInput) {
                targetInput = document.querySelector('.product-input');
            }
            if (targetInput) {
                const row = targetInput.closest('tr');
                const hiddenId   = row.querySelector('.product-id-field');
                const costInput  = row.querySelector('.item-cost');
                const sellInput  = row.querySelector('.item-selling');

                targetInput.value = p.name + ' (' + p.sku + ')';
                if (hiddenId) hiddenId.value = p.id;
                if (costInput) costInput.value = p.purchase_price ?? 0;
                if (sellInput) sellInput.value = p.selling_price ?? 0;

                const evt = new Event('input', { bubbles: true });
                if (costInput) costInput.dispatchEvent(evt);
                if (sellInput) sellInput.dispatchEvent(evt);
                targetInput.dispatchEvent(evt);
            }

            // success flash
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



