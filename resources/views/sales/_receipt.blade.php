@php
    use App\Models\Setting;

    // ----- Store info from settings -----
    $STORE_NAME    = Setting::get('store_name', config('app.name', 'My Store'));
    $STORE_ADDRESS = Setting::get('store_address', '123 Main Street, City, Country');
    $STORE_PHONE   = Setting::get('store_phone', '000-000-0000');
    $STORE_EMAIL   = Setting::get('store_email');
    
    // 👇 Receipt options
    $showVatLine           = Setting::get('show_vat_on_receipt', '1') === '1';
    $showCustomerOnReceipt = Setting::get('show_customer_on_receipt', '1') === '1';
    $receiptFooter         = Setting::get('receipt_footer', 'Thank you for shopping!');

    // 👇 FIXED: use /storage/... for logos stored on 'public' disk
    $logoPath   = Setting::get('logo_path'); // e.g. "settings/logo_xxx.png"
    $STORE_LOGO = $logoPath
        ? asset('storage/'.$logoPath)
        : asset('images/logo.png');

    // ----- Currency -----
    $CURRENCY = Setting::get('currency_symbol', '₦');   // default Naira

    // helper function (no space so symbol never wraps on top)
    function money($c, $amount) {
        return $c . number_format($amount, 2);
    }

    // ----- Sale / parties -----
    $cashierName  = optional($sale->user)->name ?? 'Cashier';
    $customerName = $sale->customer_name ?? 'Walk-in Customer';

    // ----- Money calculations -----
    $subtotal = $sale->subtotal
        ?? $sale->items->sum(fn ($item) => $item->subtotal ?? ($item->qty * $item->price));

    $discount = $sale->discount ?? 0;
    $fee      = $sale->fee      ?? 0;

    $vatPercent = Setting::vatPercent();  
    $vatAmount  = $sale->vat_amount
        ?? (($subtotal - $discount + $fee) * Setting::vatRate());

    $total  = $sale->total ?? ($subtotal - $discount + $fee + $vatAmount);
    $paid   = $sale->amount_paid ?? $total;
    $change = $sale->change ?? max($paid - $total, 0);
@endphp

<style>
    .receipt-wrapper {
        width: 260px;
        font-family: "Courier New", monospace;
        font-size: 12px;
        margin: 0 auto;
        text-align: center;
    }
    .receipt-wrapper h2 {
        margin: 4px 0;
        font-size: 14px;
        letter-spacing: 1px;
    }
    .receipt-logo {
        max-width: 60px;
        max-height: 60px;
        margin-bottom: 4px;
    }
    .receipt-line {
        border-top: 1px dashed #000;
        margin: 6px 0;
    }
    .receipt-section {
        margin: 6px 0;
        text-align: left;
    }
    .receipt-section p {
        margin: 2px 0;
    }
    .receipt-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
        text-align: left;
    }
    .receipt-items th,
    .receipt-items td {
        padding: 2px 0;
    }
    .receipt-items th {
        border-bottom: 1px solid #000;
        font-weight: bold;
    }

    .receipt-totals {
        margin-top: 6px;
        text-align: right;
    }

    /* 👇 make totals align like real POS: label left, amount right, no wrap */
    .receipt-totals p {
        margin: 2px 0;
        display: flex;
        justify-content: space-between;
        white-space: nowrap;
    }
    .receipt-totals p span.amount {
        min-width: 80px;
        text-align: right;
    }

    /* keep numbers in items from wrapping weirdly */
    .receipt-items td:nth-child(3),
    .receipt-items td:nth-child(4) {
        white-space: nowrap;
        text-align: right;
    }
.receipt-items td,
.receipt-items th {
    font-size: 10.5px !important;
}

    .receipt-barcode {
        margin-top: 8px;
        padding-top: 4px;
        border-top: 1px dashed #000;
        font-size: 10px;
        letter-spacing: 3px;
    }

    /* ✅ Force print to look EXACTLY like receipt */
    @media print {
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            margin: 0;
            padding: 0;
            background: #fff !important;
            color: #000 !important;
        }

        .receipt-wrapper {
            width: 260px !important;
            margin: 10px auto !important;  /* more breathing room */
            padding: 6px 0 !important;     /* extra spacing */
            font-family: "Courier New", monospace;
            font-size: 10px !important;    /* slightly larger */
            line-height: 1.35 !important;  /* better readability */
            text-align: center;
            color: #000 !important;
        }

        /* ✅ ensure ALL text & elements print cleanly */
        * {
            color: #000 !important;
            background: transparent !important;
        }

        /* ✅ allow logo to print fully */
        .receipt-logo {
            display: block !important;
            margin: 8px auto 10px !important;
            max-width: 70px !important;
            max-height: 70px !important;
            image-rendering: crisp-edges;
            -webkit-print-color-adjust: exact;
        }

        /* ✅ more spacing between sections */
        .receipt-section,
        .receipt-totals {
            margin-top: 8px !important;
        }

        .receipt-line {
            margin: 8px 0 !important;
        }

        table.receipt-items td,
        table.receipt-items th {
            padding: 4px 0 !important; /* increased row height */
        }
        /* ✅ add spacing between Qty and Price columns */
.receipt-items td:nth-child(2),
.receipt-items th:nth-child(2) {
    padding-right: 10px !important;  /* more space after qty */
}

.receipt-items td:nth-child(3),
.receipt-items th:nth-child(3) {
    padding-left: 6px !important;   /* small offset before price */
}



        /* ✅ prevent browser header/footer margins */
        @page {
            margin: 0;
            size: auto;
        }

        /* ✅ prevent content splitting */
        .receipt-wrapper,
        table,
        tr,
        td {
            page-break-inside: avoid !important;
        }
    }
</style>

<div class="receipt-wrapper">
    <div>
        <img src="{{ $STORE_LOGO }}" class="receipt-logo" alt="Logo" crossorigin="anonymous">
    </div>

    <!-- <h2>{{ $STORE_NAME }}</h2> -->
    <p>{{ $STORE_ADDRESS }}</p>
    <p>{{ $STORE_PHONE }}</p>
    @if($STORE_EMAIL)
        <p>{{ $STORE_EMAIL }}</p>
    @endif

    <div class="receipt-line"></div>
    <p><strong>RECEIPT</strong></p>
    <div class="receipt-line"></div>

    <div class="receipt-section">
        @if($showCustomerOnReceipt)
            <p><strong>Customer:</strong> {{ $customerName }}</p>
        @endif
        <p><strong>Cashier:</strong> {{ $cashierName }}</p>
        <p><strong>Payment:</strong> {{ ucfirst($sale->payment_method) }}</p>
        <p><strong>Date:</strong> {{ $sale->created_at->format('Y-m-d H:i') }}</p>
        <p><strong>Receipt #:</strong> {{ $sale->id }}</p>
    </div>

    <div class="receipt-line"></div>

    <table class="receipt-items">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align:right;">Qty</th>
                <th style="text-align:right;">Price</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                @php
                    $lineTotal = $item->subtotal ?? ($item->qty * $item->price);
                @endphp
                <tr>
                    <td>{{ $item->name }}</td>
                    <td style="text-align:right;">{{ $item->qty }}</td>
                    <td>{{ money($CURRENCY, $item->price) }}</td>
                    <td>{{ money($CURRENCY, $lineTotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="receipt-totals" style="margin-top:6px;">
        <p>
            <span><strong>SubTotal:</strong></span>
            <span class="amount">{{ money($CURRENCY, $subtotal) }}</span>
        </p>

        @if($discount > 0)
            <p>
                <span><strong>Discount:</strong></span>
                <span class="amount">-{{ money($CURRENCY, $discount) }}</span>
            </p>
        @endif

        @if($fee > 0)
            <p>
                <span><strong>Fee:</strong></span>
                <span class="amount">+{{ money($CURRENCY, $fee) }}</span>
            </p>
        @endif

        @if($showVatLine)
            <p>
                <span><strong>VAT ({{ number_format($vatPercent, 2) }}%):</strong></span>
                <span class="amount">{{ money($CURRENCY, $vatAmount) }}</span>
            </p>
        @endif

        <p>
            <span><strong>Total:</strong></span>
            <span class="amount">{{ money($CURRENCY, $total) }}</span>
        </p>
        <p>
            <span><strong>Paid:</strong></span>
            <span class="amount">{{ money($CURRENCY, $paid) }}</span>
        </p>
        <p>
            <span><strong>Change:</strong></span>
            <span class="amount">{{ money($CURRENCY, $change) }}</span>
        </p>
    </div>

    <div class="receipt-barcode">
        {{ str_pad($sale->id, 10, '0', STR_PAD_LEFT) }}
    </div>

    <p style="margin-top:6px;">{{ $receiptFooter }}</p>
</div>

<script>
    window.onload = function () {
        window.print();
        window.onafterprint = function () {
            window.close();
        };
    };
</script>
