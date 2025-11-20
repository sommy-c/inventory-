<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }
        h2 {
            margin-bottom: 5px;
        }
        .filters {
            font-size: 11px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #cccccc;
            padding: 4px 6px;
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

    <div class="filters">
        @if($fromDate)
            <strong>From:</strong> {{ $fromDate }}&nbsp;&nbsp;
        @endif
        @if($toDate)
            <strong>To:</strong> {{ $toDate }}&nbsp;&nbsp;
        @endif
        @if($cashier)
            <strong>Cashier:</strong> {{ $cashier }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date &amp; Time</th>
                <th>Sale ID</th>
                <th>Cashier</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $sale->id }}</td>
                    <td>{{ optional($sale->user)->name ?? 'N/A' }}</td>
                    <td>{{ $sale->customer_name ?? '-' }}</td>
                    <td>{{ $sale->items->sum('qty') }}</td>
                    <td>{{ number_format($sale->total, 2) }}</td>
                    <td>{{ ucfirst($sale->payment_method) }}</td>
                    <td>{{ ucfirst($sale->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">No sales found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
