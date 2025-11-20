@php
    $STORE_NAME    = config('app.name', 'My Store');
    $STORE_ADDRESS = '123 Main Street, City, Country'; // change
    $STORE_PHONE   = '000-000-0000';                   // change
    $STORE_LOGO    = asset('images/logo.png');         // change

    $cashierName   = optional($sale->user)->name ?? 'Cashier';
    $customerName  = $sale->customer_name ?? 'Walk-in Customer';

    $total   = $sale->total;
    $paid    = $sale->amount_paid;
    $change  = $sale->change;
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
    .receipt-totals p {
        margin: 2px 0;
    }
    .receipt-barcode {
        margin-top: 8px;
        padding-top: 4px;
        border-top: 1px dashed #000;
        font-size: 10px;
        letter-spacing: 3px;
    }
</style>

<div class="receipt-wrapper">
    <div>
        <img src="{{ $STORE_LOGO }}" alt="Logo" class="receipt-logo">
    </div>

    <h2>{{ $STORE_NAME }}</h2>
    <p>{{ $STORE_ADDRESS }}</p>
    <p>{{ $STORE_PHONE }}</p>

    <div class="receipt-line"></div>
    <p><strong>RECEIPT</strong></p>
    <div class="receipt-line"></div>

    <div class="receipt-section">
        <p><strong>Customer:</strong> {{ $customerName }}</p>
        <p><strong>Cashier:</strong> {{ $cashierName }}</p>
        <p><strong>Payment:</strong> {{ ucfirst($sale->payment_method) }}</p>
        <p><strong>Date:</strong> {{ $sale->created_at->format('Y-m-d H:i') }}</p>
        <p><strong>Receipt #:</strong> {{ $sale->id }}</p>
    </div>

    <div class="receipt-line"></div>

    <table class="receipt-items">
        <thead>
            <tr>
                <th style="text-align:left;">Item</th>
                <th style="text-align:right;">Qty</th>
                <th style="text-align:right;">Price</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                @php
                    $lineTotal = $item->qty * $item->price;
                @endphp
                <tr>
                    <td>{{ $item->name }}</td>
                    <td style="text-align:right;">{{ $item->qty }}</td>
                    <td style="text-align:right;">{{ number_format($item->price, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="receipt-totals">
        <p><strong>Total:</strong> {{ number_format($total, 2) }}</p>
        <p><strong>Paid:</strong> {{ number_format($paid, 2) }}</p>
        <p><strong>Change:</strong> {{ number_format(max($change, 0), 2) }}</p>
    </div>

    <div class="receipt-barcode">
        {{ str_pad($sale->id, 10, '0', STR_PAD_LEFT) }}
    </div>

    <p style="margin-top:6px;">Thank you for shopping!</p>
</div>
