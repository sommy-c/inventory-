{{-- resources/views/sales/index.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="container-fluid">

    {{-- WRAP EVERYTHING YOU WANT TO PRINT --}}
    <div id="sales-report-print-area">
        <h1 class="mb-4">Sales Report</h1>

       {{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">

        @if(!empty($errorMessage))
            <div class="alert alert-error">
                {{ $errorMessage }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.sales.index') }}" class="row g-3">

            {{-- Date range --}}
            <div class="col-md-3">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" id="from_date" name="from_date"
                       value="{{ $fromDate }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" id="to_date" name="to_date"
                       value="{{ $toDate }}" class="form-control">
            </div>

            {{-- Cashier --}}
            <div class="col-md-3">
                <label for="cashier" class="form-label">Cashier / Seller</label>
                <input type="text" id="cashier" name="cashier"
                       value="{{ $cashierInput ?? '' }}" class="form-control"
                       placeholder="Type full cashier name...">
            </div>

            {{-- Customer name / phone --}}
            <div class="col-md-3">
                <label for="customer" class="form-label">Customer (Name / Phone)</label>
                <input type="text" id="customer" name="customer"
                       value="{{ $customerInput ?? '' }}" class="form-control"
                       placeholder="Search customer...">
            </div>

            {{-- Product name / SKU --}}
            <div class="col-md-3">
                <label for="product" class="form-label">Product (Name / SKU)</label>
                <input type="text" id="product" name="product"
                       value="{{ $productInput ?? '' }}" class="form-control"
                       placeholder="Search product...">
            </div>

            {{-- Payment method --}}
            <div class="col-md-3">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select id="payment_method" name="payment_method" class="form-control">
                    <option value="">All Methods</option>
                    <option value="cash"          {{ ($paymentMethod ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card"          {{ ($paymentMethod ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="bank_transfer" {{ ($paymentMethod ?? '') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="pos"           {{ ($paymentMethod ?? '') === 'pos' ? 'selected' : '' }}>POS</option>
                    {{-- adjust / add more to match your DB values --}}
                </select>
            </div>

            {{-- Buttons --}}
            <div class="col-md-3 d-flex align-items-end gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary flex-fill">Reset</a>
            </div>
        </form>

        {{-- Extra actions: Print table & Export PDF (keep as you had) --}}
        <div class="mt-3 d-flex flex-wrap gap-2">
            <button type="button" id="btn-print-sales-table" class="btn btn-outline-light btn-sm">
                Print Sales Table
            </button>

            <a href="{{ route('admin.sales.export.pdf', request()->query()) }}"
   class="btn btn-outline-warning btn-sm"
   target="_blank">
    Export as PDF
</a>

        </div>
    </div>
</div>


        {{-- Summary Cards (dark glass cards similar to form-card) --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card" style="background: rgba(255,255,255,0.08); backdrop-filter: blur(8px); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                    <div class="card-body">
                        <h6 class="card-title" style="font-size: 14px; color:#d1d5db;">
                            @if($cashier)
                                Total Sales ({{ $cashier }})
                            @else
                                Total Sales (Filtered)
                            @endif
                        </h6>
                        <h3 class="mb-0" style="color:#fff;">{{ number_format($totalSales, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card" style="background: rgba(255,255,255,0.08); backdrop-filter: blur(8px); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                    <div class="card-body">
                        <h6 class="card-title" style="font-size: 14px; color:#d1d5db;">
                            @if($cashier)
                                Number of Sales ({{ $cashier }})
                            @else
                                Number of Sales
                            @endif
                        </h6>
                        <h3 class="mb-0" style="color:#fff;">{{ $countSales }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales Table --}}
        <div class="card glass-card">
            <div class="table-responsive" id="sales-table-wrapper">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>Sale ID</th>

                            @if(empty($cashier))
                                <th>Cashier</th>
                            @endif

                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $index => $sale)
                            <tr class="sale-row"
                                data-sale-id="{{ $sale->id }}"
                                data-details-url="{{ route('admin.sales.details', $sale->id) }}"
                                style="cursor: pointer;">

                                <td>{{ $sales->firstItem() + $index }}</td>
                                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $sale->id }}</td>

                                @if(empty($cashier))
                                    <td>{{ optional($sale->user)->name ?? 'N/A' }}</td>
                                @endif

                                <td>{{ $sale->customer_name ?? '-' }}</td>
                                <td>{{ $sale->items->sum('qty') }}</td>
                                <td><strong>{{ number_format($sale->total, 2) }}</strong></td>
                                <td>{{ ucfirst($sale->payment_method) }}</td>
                                <td>
                                    <span class="badge 
                                        @if($sale->status === 'completed') bg-success
                                        @elseif($sale->status === 'paused') bg-warning
                                        @else bg-secondary @endif">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-print-receipt"
                                        data-sale-id="{{ $sale->id }}">
                                        Print
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No sales found for this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div> {{-- /#sales-report-print-area --}}

    {{-- Buttons UNDER the printable area (so they don't print) --}}
    <!-- <div class="mt-3 d-flex flex-wrap gap-2">
        <button type="button" id="btn-print-sales-table" class="btn btn-outline-light btn-sm">
            Print Sales Table
        </button>

        <a href="{{ route('admin.sales.export.pdf', request()->query()) }}"
           class="btn btn-outline-warning btn-sm">
            Export as PDF
        </a>
    </div> -->

</div>
</div>

{{-- Global Loading Overlay --}}
<div id="loading-overlay">
    <div class="spinner"></div>
</div>

{{-- Receipt Modal for Sales Report (WHITE, normal print look) --}}
<div class="modal fade light-modal" id="report-receipt-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 320px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Receipt</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="report-receipt-content">
          {{-- Receipt HTML will be injected here --}}
        </div>
      </div>
      <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">
            Close
        </button>
        <button type="button"
                class="btn btn-primary"
                id="report-print-btn">
            Print
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Sale Details Modal (dark glass style like dashboard) --}}
<div class="modal fade" id="sale-details-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Sale Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="sale-details-content">
          {{-- Sale details content will be rendered here via JS --}}
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
<script>
    window.printCashierName = "{{ $cashier ? $cashier : 'All Cashiers' }}";
    window.printFromDate    = "{{ $fromDate ?: '—' }}";
    window.printToDate      = "{{ $toDate ?: '—' }}";
</script>


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const printButtons         = document.querySelectorAll('.btn-print-receipt');
    const receiptModalEl       = document.getElementById('report-receipt-modal');
    const receiptContentEl     = document.getElementById('report-receipt-content');
    const reportPrintBtn       = document.getElementById('report-print-btn');

    const saleRows             = document.querySelectorAll('.sale-row');
    const saleDetailsModalEl   = document.getElementById('sale-details-modal');
    const saleDetailsContentEl = document.getElementById('sale-details-content');

    const loadingOverlay       = document.getElementById('loading-overlay');

    const printTableBtn        = document.getElementById('btn-print-sales-table');
    const salesTableWrapper    = document.getElementById('sales-table-wrapper');

    let receiptModal = null;
    let saleDetailsModal = null;

    function showLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'flex';
    }
    function hideLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'none';
    }

    hideLoading();

    if (receiptModalEl && window.bootstrap) {
        receiptModal = new bootstrap.Modal(receiptModalEl);
    }
    if (saleDetailsModalEl && window.bootstrap) {
        saleDetailsModal = new bootstrap.Modal(saleDetailsModalEl);
    }

    // Base URL only for PRINT
    const baseUrl = "{{ url('admin/sales') }}/";

    // =====================================================
    // 1) PRINT SINGLE RECEIPT (existing behaviour)
    // =====================================================
    printButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.stopPropagation(); // don't trigger row click

            const saleId = btn.dataset.saleId;
            if (!saleId) return;

            const url = baseUrl + saleId + "/print";

            showLoading();

            try {
                const res = await fetch(url);

                if (!res.ok) {
                    console.error('Failed to load receipt', res.status);
                    alert('Could not load receipt');
                    hideLoading();
                    return;
                }

                const html = await res.text();
                receiptContentEl.innerHTML = html;

                hideLoading();

                if (receiptModal) {
                    receiptModal.show();
                } else if (receiptModalEl) {
                    receiptModalEl.style.display = 'block';
                }

            } catch (err) {
                console.error('Error loading receipt', err);
                alert('Error loading receipt');
                hideLoading();
            }
        });
    });

    if (reportPrintBtn) {
        reportPrintBtn.addEventListener('click', () => {
            if (!receiptContentEl || !receiptContentEl.innerHTML.trim()) {
                alert('No receipt loaded to print');
                return;
            }

            const printWindow = window.open('', '', 'width=400,height=600');
            printWindow.document.write('<html><head><title>Receipt</title></head><body>');
            printWindow.document.write(receiptContentEl.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });
    }

    // Helper: format money
    function formatMoney(amount) {
        const n = Number(amount ?? 0);
        return n.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

   // =====================================================
// PRINT ONLY THE SALES TABLE (NO BUTTONS / NO RECEIPT COL)
// =====================================================
// =====================================================
// PRINT ONLY THE SALES TABLE (NO BUTTONS / NO RECEIPT COL)
// =====================================================
if (printTableBtn && salesTableWrapper) {
    printTableBtn.addEventListener('click', () => {
        const originalTable = salesTableWrapper.querySelector('table');
        if (!originalTable) {
            alert('Sales table not found.');
            return;
        }

        // Clone current table
        const tableClone = originalTable.cloneNode(true);

        // Remove "Receipt" column (last column) from all rows
        tableClone.querySelectorAll('tr').forEach(row => {
            if (row.cells.length > 0) {
                row.deleteCell(-1);
            }
        });

        const tableHtml  = tableClone.outerHTML;
        const cashier    = window.printCashierName || 'All Cashiers';
        const fromDate   = window.printFromDate || '—';
        const toDate     = window.printToDate || '—';

        const printWindow = window.open('', '', 'width=900,height=700');
        printWindow.document.open();
        printWindow.document.write(`
            <!doctype html>
            <html>
                <head>
                    <title>Sales Report</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background: #ffffff !important;
                            color: #000000 !important;
                            padding: 20px;
                            font-size: 13px;
                        }
                        h2, h3, h4 {
                            margin: 0;
                            padding: 0;
                        }
                        h2 { margin-bottom: 4px; }
                        h3 { margin-bottom: 4px; font-weight: normal; }
                        h4 { margin: 6px 0 12px 0; font-weight: normal; }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 10px;
                        }
                        th, td {
                            border: 1px solid #ccc;
                            padding: 6px 8px;
                            text-align: left;
                        }
                        th {
                            background: #f3f4f6;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>

                    <h2>Sales Report</h2>
                    <h3>Cashier: ${cashier}</h3>
                    <h4>Date Range: ${fromDate} → ${toDate}</h4>

                    ${tableHtml}

                </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        // optional: printWindow.close();
    });
}

    

    // =====================================================
    // 3) ROW CLICK -> SALE DETAILS MODAL (unchanged)
    // =====================================================
    saleRows.forEach(row => {
        row.addEventListener('click', async (e) => {
            // Ignore clicks from the print button
            if (e.target.closest('.btn-print-receipt')) {
                return;
            }

            const url = row.dataset.detailsUrl;
            if (!url) return;

            saleDetailsContentEl.innerHTML = '<div class="text-center text-muted">Loading...</div>';
            if (saleDetailsModal) {
                saleDetailsModal.show();
            } else if (saleDetailsModalEl) {
                saleDetailsModalEl.style.display = 'block';
            }

            showLoading();

            try {
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!res.ok) {
                    console.error('Failed to load sale details', res.status);
                    saleDetailsContentEl.innerHTML = '<div class="text-danger">Could not load sale details.</div>';
                    hideLoading();
                    return;
                }

                const data = await res.json();

                let itemsRowsHtml = '';
                if (Array.isArray(data.items)) {
                    itemsRowsHtml = data.items.map((item, idx) => `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${item.name ?? ''}</td>
                            <td>${item.sku ?? ''}</td>
                            <td style="text-align:right;">${item.qty}</td>
                            <td style="text-align:right;">${formatMoney(item.price)}</td>
                            <td style="text-align:right;">${formatMoney(item.total)}</td>
                        </tr>
                    `).join('');
                }

                const detailsHtml = `
    <div class="glass-card mb-3 p-3">
        <div class="d-flex justify-content-between flex-wrap">
            <div>
                <h5 class="mb-1 text-white">Sale #${data.id}</h5>
                <div class="small" style="color:#d1d5db;">
                    <div><strong class="text-white">Date:</strong> ${data.date_time ?? ''}</div>
                    <div><strong class="text-white">Status:</strong> ${(data.status ?? '').toUpperCase()}</div>
                </div>
            </div>
            <div class="text-end small" style="color:#d1d5db;">
                <div><strong class="text-white">Cashier:</strong> ${data.cashier ?? 'N/A'}</div>
                <div><strong class="text-white">Payment:</strong> ${(data.payment_method ?? '-').toUpperCase()}</div>
            </div>
        </div>
    </div>

    <div class="glass-card mb-3 p-3">
        <h6 class="text-white mb-2">Customer</h6>
        <div class="small" style="color:#d1d5db;">
            <div><strong class="text-white">Name:</strong> ${data.customer_name ?? 'Walk-in customer'}</div>
            <div><strong class="text-white">Phone:</strong> ${data.customer_phone ?? '-'}</div>
            <div><strong class="text-white">Email:</strong> ${data.customer_email ?? '-'}</div>
        </div>
    </div>

    <div class="glass-card mb-3 p-3">
        <h6 class="text-white mb-2">Items</h6>
        <div class="table-responsive">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>SKU</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Price</th>
                        <th style="text-align:right;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsRowsHtml || '<tr><td colspan="6" style="text-align:center; color:#9ca3af;">No items found.</td></tr>'}
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card p-3">
        <div class="small" style="color:#d1d5db;">
            <div class="d-flex justify-content-between mb-1">
                <span>Subtotal:</span>
                <strong class="text-white">${formatMoney(data.subtotal)}</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span>Discount:</span>
                <strong class="text-white">${formatMoney(data.discount)}</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span>Fee:</span>
                <strong class="text-white">${formatMoney(data.fee)}</strong>
            </div>

            <hr style="border-color:rgba(255,255,255,0.2)">

            <div class="d-flex justify-content-between mb-1">
                <span>Total:</span>
                <strong class="text-white">${formatMoney(data.total)}</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span>Amount Paid:</span>
                <strong class="text-white">${formatMoney(data.amount_paid)}</strong>
            </div>
            <div class="d-flex justify-content-between">
                <span>Change:</span>
                <strong class="text-white">${formatMoney(data.change)}</strong>
            </div>
        </div>
    </div>
`;

                saleDetailsContentEl.innerHTML = detailsHtml;
                hideLoading();

            } catch (err) {
                console.error('Error loading sale details', err);
                saleDetailsContentEl.innerHTML = '<div class="text-danger">Error loading sale details.</div>';
                hideLoading();
            }
        });
    });
});
</script>
@endpush
