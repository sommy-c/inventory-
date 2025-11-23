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
                    <tr>
                        <td>{{ $purchase->id }}</td>
                        <td>{{ $purchase->purchase_date }}</td>
                        <td>{{ optional($purchase->supplier)->name }}</td>
                        <td>{{ $purchase->reference ?? '—' }}</td>

                        {{-- Total with currency --}}
                        <td>{{ $formatCurrency($purchase->total) }}</td>

                        {{-- Amount paid with currency --}}
                        <td>{{ $formatCurrency($purchase->amount_paid) }}</td>

                        <td>{{ ucfirst($purchase->payment_status) }}</td>
                        <td>
                            <a href="{{ route('admin.purchases.show', $purchase) }}"
                               class="btn-small btn-view-purchase">
                                View
                            </a>
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
@endsection

@push('scripts')
<script>
    // Expose currency config to JS safely
    window.APP_CURRENCY = {
       symbol: JSON.parse(`@json($currencySymbol)`),
position: JSON.parse(`@json($currencyPosition)`),

    };

    /**
     * JS helper: format money using Settings (symbol + position)
     */
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
                e.preventDefault(); // stop full-page navigation
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

                    // Items table
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
                        const label = item.product_name +
                            (item.sku ? ' (' + item.sku + ')' : '');
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

                    // Totals
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
    });
</script>
@endpush
