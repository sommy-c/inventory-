<h2>Daily Sales Summary - {{ $summary['date'] }}</h2>

<ul>
    <li><strong>Total Sales:</strong> ₦{{ number_format($summary['totalSales'], 2) }}</li>
    <li><strong>Total VAT:</strong> ₦{{ number_format($summary['totalVat'] ?? 0, 2) }}</li>
    <li><strong>Transactions:</strong> {{ $summary['transactionCount'] ?? $summary['countSales'] ?? 0 }}</li>
</ul>
