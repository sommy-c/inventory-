<h2>Daily Sales Summary - {{ $date }}</h2>

<p>Here is today's performance report:</p>

<ul>
    <li><strong>Total Sales:</strong> ₦{{ number_format($totalSales, 2) }}</li>
    <li><strong>Total VAT:</strong> ₦{{ number_format($totalVat, 2) }}</li>
    <li><strong>Transactions:</strong> {{ $transactionCount }}</li>
</ul>

<p>Have a great day!</p>
