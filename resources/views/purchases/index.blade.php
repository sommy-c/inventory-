{{-- resources/views/purchases/index.blade.php --}}
@extends('admin.layout')

@section('title', 'Purchases')

@php
    use App\Models\Setting;

    // Currency settings from DB
    $currencySymbol   = Setting::get('currency_symbol', '₦');
    $currencyPosition = Setting::get('currency_position', 'left');

    // Blade helper for amounts
    $formatCurrency = function ($amount) use ($currencySymbol, $currencyPosition) {
        $value = number_format($amount ?? 0, 2);
        return $currencyPosition === 'right'
            ? "{$value} {$currencySymbol}"
            : "{$currencySymbol} {$value}";
    };
@endphp

@section('content')
<div class="customers-page">
    <div class="page-header">
        <h1>Purchases</h1>

        <form action="{{ route('admin.purchases.index') }}" method="GET" class="search-form">
            <input type="text"
                   name="supplier"
                   value="{{ $supplierFilter }}"
                   placeholder="Supplier name">

            <select name="payment_status" class="filter-select">
                <option value="">Payment status (all)</option>
                <option value="paid"    @selected($statusFilter === 'paid')>Paid</option>
                <option value="partial" @selected($statusFilter === 'partial')>Partial</option>
                <option value="unpaid"  @selected($statusFilter === 'unpaid')>Unpaid</option>
            </select>

            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <h2>Purchase List</h2>
            <a href="{{ route('admin.purchases.create') }}" class="btn-primary">+ New Purchase</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Reference</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
               <tbody>
@forelse($purchases as $purchase)
    <tr
        data-purchase-id="{{ $purchase->id }}"
        data-total="{{ $purchase->total }}"
        data-paid="{{ $purchase->amount_paid }}"
    >
        <td>{{ $purchase->id }}</td>
        <td>{{ $purchase->purchase_date }}</td>
        <td>{{ optional($purchase->supplier)->name }}</td>
        <td>{{ $purchase->reference ?? '—' }}</td>

        {{-- Total with currency --}}
        <td class="cell-total">{{ $formatCurrency($purchase->total) }}</td>

        {{-- Amount paid with currency --}}
        <td class="cell-paid">{{ $formatCurrency($purchase->amount_paid) }}</td>

        <td class="cell-status">{{ ucfirst($purchase->payment_status) }}</td>
        <td>
            <a href="{{ route('admin.purchases.show', $purchase) }}"
               class="btn-small btn-view-purchase">
                View
            </a>

            {{-- 🔹 New: Add Payment button --}}
            @if($purchase->total > $purchase->amount_paid)
                <button type="button"
                        class="btn-small btn-secondary btn-add-payment"
                        data-id="{{ $purchase->id }}">
                    Add Payment
                </button>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align:center;">No purchases found.</td>
    </tr>
@endforelse
</tbody>

            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $purchases->links() }}
        </div>
    </div>
</div>

{{-- Purchase details modal --}}
<div class="modal-overlay hidden" id="purchaseDetailModal">
    <div class="modal-card">
        <div class="modal-header">
            <h2 id="purchaseDetailTitle">Purchase details</h2>
            <button type="button" class="modal-close" id="purchaseDetailClose">&times;</button>
        </div>

        <div class="modal-body" id="purchaseDetailBody">
            <p style="font-size:14px; color:#9ca3af;">Loading…</p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="purchaseDetailCloseFooter">
                Close
            </button>
        </div>
    </div>
</div>

{{-- 🔹 New: Add Payment Modal --}}
<div class="modal-overlay hidden" id="addPaymentModal">
    <div class="modal-card" style="max-width: 420px;">
        <div class="modal-header">
            <h2 id="addPaymentTitle">Add Payment</h2>
            <button type="button" class="modal-close" id="addPaymentClose">&times;</button>
        </div>

        <div class="modal-body">
            <form id="addPaymentForm">
                @csrf
                <input type="hidden" id="addPaymentPurchaseId">

                <div class="form-group">
                    <label>Purchase Total</label>
                    <input type="text" id="addPaymentTotal" readonly>
                </div>

                <div class="form-group">
                    <label>Already Paid</label>
                    <input type="text" id="addPaymentAlreadyPaid" readonly>
                </div>

                <div class="form-group">
                    <label>Current Balance</label>
                    <input type="text" id="addPaymentBalance" readonly>
                </div>

                <div class="form-group">
                    <label>Additional Payment Amount</label>
                    <input type="number" step="0.01" min="0.01" id="addPaymentAmount" required>
                </div>

                <div class="alert alert-error" id="addPaymentError" style="display:none; margin-top:8px;"></div>

                <div class="modal-footer" style="margin-top: 16px; justify-content:flex-end;">
                    <button type="button" class="btn-secondary" id="addPaymentCancel">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection


@push('scripts')
<script>
    // Expose currency config to JS safely
    window.APP_CURRENCY = {
        symbol: JSON.parse(`@json($currencySymbol)`),
        position: JSON.parse(`@json($currencyPosition)`),
    };

    function formatMoneyJS(amount) {
        const cfg = window.APP_CURRENCY || { symbol: '₦', position: 'left' };
        const symbol   = cfg.symbol || '₦';
        const position = cfg.position || 'left';

        const value = Number(amount || 0).toFixed(2);

        return position === 'right'
            ? `${value} ${symbol}`
            : `${symbol} ${value}`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        // ------- Existing detail modal code (unchanged) -------
        const modal          = document.getElementById('purchaseDetailModal');
        const modalTitle     = document.getElementById('purchaseDetailTitle');
        const modalBody      = document.getElementById('purchaseDetailBody');
        const closeBtn       = document.getElementById('purchaseDetailClose');
        const closeBtnFooter = document.getElementById('purchaseDetailCloseFooter');

        function openModal() {
            if (modal) modal.classList.remove('hidden');
        }

        function closeModal() {
            if (modal) modal.classList.add('hidden');
        }

        if (closeBtn)       closeBtn.addEventListener('click', closeModal);
        if (closeBtnFooter) closeBtnFooter.addEventListener('click', closeModal);

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }

        document.querySelectorAll('.btn-view-purchase').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const url = btn.getAttribute('href');
                if (!url) return;

                modalTitle.textContent = 'Purchase details';
                modalBody.innerHTML = '<p style="font-size:14px; color:#9ca3af;">Loading…</p>';
                openModal();

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const p = data.purchase;
                    let html = '';

                    html += '<div class="customer-details-body">';
                    html += '  <div class="detail-row">';
                    html += '    <div class="detail-label">Supplier</div>';
                    html += '    <div class="detail-value">' + p.supplier_name + '</div>';
                    html += '  </div>';
                    html += '  <div class="detail-row">';
                    html += '    <div class="detail-label">Date</div>';
                    html += '    <div class="detail-value">' + p.purchase_date + '</div>';
                    html += '  </div>';
                    html += '  <div class="detail-row">';
                    html += '    <div class="detail-label">Reference</div>';
                    html += '    <div class="detail-value">' + (p.reference ?? '—') + '</div>';
                    html += '  </div>';
                    html += '  <div class="detail-row">';
                    html += '    <div class="detail-label">Status</div>';
                    html += '    <div class="detail-value"><span class="badge">' +
                            p.payment_status.charAt(0).toUpperCase() + p.payment_status.slice(1) +
                            '</span></div>';
                    html += '  </div>';

                    if (p.created_by) {
                        html += '  <div class="detail-row">';
                        html += '    <div class="detail-label">Created by</div>';
                        html += '    <div class="detail-value">' + p.created_by + '</div>';
                        html += '  </div>';
                    }

                    if (p.notes) {
                        html += '  <div class="detail-row">';
                        html += '    <div class="detail-label">Notes</div>';
                        html += '    <div class="detail-value">' + p.notes + '</div>';
                        html += '  </div>';
                    }

                    html += '</div>';
                    html += '<hr style="margin: 14px 0; border-color: rgba(148, 163, 184, 0.4);">';

                    html += '<div class="table-wrapper">';
                    html += '  <table>';
                    html += '    <thead>';
                    html += '      <tr>';
                    html += '        <th>Product</th>';
                    html += '        <th>Qty</th>';
                    html += '        <th>Cost Price</th>';
                    html += '        <th>Line Total</th>';
                    html += '        <th>Expiry</th>';
                    html += '      </tr>';
                    html += '    </thead>';
                    html += '    <tbody>';

                    p.items.forEach(function (item) {
                        const label = item.product_name + (item.sku ? ' (' + item.sku + ')' : '');
                        html += '  <tr>';
                        html += '    <td>' + label + '</td>';
                        html += '    <td>' + item.quantity + '</td>';
                        html += '    <td>' + formatMoneyJS(item.cost_price) + '</td>';
                        html += '    <td>' + formatMoneyJS(item.line_total) + '</td>';
                        html += '    <td>' + (item.expiry_date || '—') + '</td>';
                        html += '  </tr>';
                    });

                    html += '    </tbody>';
                    html += '  </table>';
                    html += '</div>';

                    html += '<div style="margin-top:16px; max-width:320px; margin-left:auto;">';
                    html += '  <table style="width:100%; font-size:13px;">';
                    html += '    <tbody>';
                    html += '      <tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Subtotal</td>' +
                            '<td style="padding:4px 8px; text-align:right;">' + formatMoneyJS(p.subtotal) + '</td></tr>';
                    html += '      <tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Discount</td>' +
                            '<td style="padding:4px 8px; text-align:right;">- ' + formatMoneyJS(p.discount) + '</td></tr>';
                    html += '      <tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Tax</td>' +
                            '<td style="padding:4px 8px; text-align:right;">+ ' + formatMoneyJS(p.tax) + '</td></tr>';
                    html += '      <tr><td style="padding:4px 8px; text-align:right; font-weight:600;">Total</td>' +
                            '<td style="padding:4px 8px; text-align:right; font-weight:600;">' + formatMoneyJS(p.total) + '</td></tr>';
                    html += '      <tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Amount Paid</td>' +
                            '<td style="padding:4px 8px; text-align:right;">' + formatMoneyJS(p.amount_paid) + '</td></tr>';
                    html += '      <tr><td style="padding:4px 8px; text-align:right; color:#9ca3af;">Balance</td>' +
                            '<td style="padding:4px 8px; text-align:right;">' + formatMoneyJS(p.balance) + '</td></tr>';
                    html += '    </tbody>';
                    html += '  </table>';
                    html += '</div>';

                    modalBody.innerHTML = html;
                })
                .catch((err) => {
                    console.error(err);
                    modalBody.innerHTML = '<p style="color:#fecaca;">Failed to load purchase details.</p>';
                });
            });
        });

        // ------- 🔹 Add Payment Modal -------

        const payModal         = document.getElementById('addPaymentModal');
        const payTitle         = document.getElementById('addPaymentTitle');
        const payClose         = document.getElementById('addPaymentClose');
        const payCancel        = document.getElementById('addPaymentCancel');
        const payForm          = document.getElementById('addPaymentForm');
        const payPurchaseIdInp = document.getElementById('addPaymentPurchaseId');
        const payTotalInp      = document.getElementById('addPaymentTotal');
        const payPaidInp       = document.getElementById('addPaymentAlreadyPaid');
        const payBalanceInp    = document.getElementById('addPaymentBalance');
        const payAmountInp     = document.getElementById('addPaymentAmount');
        const payErrorBox      = document.getElementById('addPaymentError');

        function openPayModal() {
            if (payModal) payModal.classList.remove('hidden');
        }
        function closePayModal() {
            if (payModal) payModal.classList.add('hidden');
            if (payErrorBox) {
                payErrorBox.style.display = 'none';
                payErrorBox.textContent = '';
            }
            if (payForm) payForm.reset();
        }

        if (payClose)  payClose.addEventListener('click', closePayModal);
        if (payCancel) payCancel.addEventListener('click', closePayModal);

        if (payModal) {
            payModal.addEventListener('click', function(e) {
                if (e.target === payModal) {
                    closePayModal();
                }
            });
        }

        // When user clicks "Add Payment"
        document.querySelectorAll('.btn-add-payment').forEach(function(btn) {
            btn.addEventListener('click', function () {
                const row = btn.closest('tr');
                if (!row) return;

                const purchaseId = row.dataset.purchaseId;
                const total      = parseFloat(row.dataset.total || 0);
                const paid       = parseFloat(row.dataset.paid || 0);
                const balance    = total - paid;

                payPurchaseIdInp.value = purchaseId;
                payTotalInp.value      = formatMoneyJS(total);
                payPaidInp.value       = formatMoneyJS(paid);
                payBalanceInp.value    = formatMoneyJS(balance);
                payAmountInp.value     = balance > 0 ? balance.toFixed(2) : '';

                payTitle.textContent = 'Add Payment for Purchase #' + purchaseId;

                openPayModal();
            });
        });

        // Submit payment
        if (payForm) {
            payForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const purchaseId = payPurchaseIdInp.value;
                const amount     = parseFloat(payAmountInp.value || 0);

                if (!purchaseId || amount <= 0) {
                    if (payErrorBox) {
                        payErrorBox.textContent = 'Please enter a valid payment amount.';
                        payErrorBox.style.display = 'block';
                    }
                    return;
                }

                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const token     = tokenMeta ? tokenMeta.getAttribute('content') : '';

                fetch(`{{ url('/admin/purchases') }}/${purchaseId}/add-payment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ amount: amount })
                })
                .then(async (response) => {
                    if (response.status === 422) {
                        const data = await response.json();
                        let msg = 'Validation error.';
                        if (data.errors && data.errors.amount && data.errors.amount[0]) {
                            msg = data.errors.amount[0];
                        }
                        if (payErrorBox) {
                            payErrorBox.textContent = msg;
                            payErrorBox.style.display = 'block';
                        }
                        throw new Error('Validation error');
                    }

                    if (!response.ok) {
                        throw new Error('Failed to add payment');
                    }

                    return response.json();
                })
                .then((data) => {
                    const p = data.purchase;

                    // Update row on the table
                    const row = document.querySelector(`tr[data-purchase-id="${p.id}"]`);
                    if (row) {
                        row.dataset.paid = p.amount_paid;

                        const cellPaid   = row.querySelector('.cell-paid');
                        const cellStatus = row.querySelector('.cell-status');
                        const btnAddPay  = row.querySelector('.btn-add-payment');

                        if (cellPaid) {
                            cellPaid.textContent = formatMoneyJS(p.amount_paid);
                        }
                        if (cellStatus) {
                            cellStatus.textContent = p.payment_status.charAt(0).toUpperCase() + p.payment_status.slice(1);
                        }

                        // If fully paid, hide the "Add Payment" button
                        if (btnAddPay && p.amount_paid >= p.total) {
                            btnAddPay.remove();
                        }
                    }

                    closePayModal();
                })
                .catch((err) => {
                    console.error(err);
                    if (payErrorBox) {
                        payErrorBox.textContent = 'Failed to save payment.';
                        payErrorBox.style.display = 'block';
                    }
                });
            });
        }
    });
</script>
@endpush


