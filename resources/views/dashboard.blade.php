@extends('admin.layout')
@section('title','Dashboard')

@section('content')

<div class="widgets">
    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager'))
        <div class="widget">
            <h3>Total Sales Today</h3>
            <p id="salesCounter">0</p>
        </div>

        <div class="widget">
            <h3>Total Profit</h3>
            <p id="profitCounter">0</p>
        </div>

        <div class="widget">
            <h3>Low Stock Items</h3>
            <p id="lowStockCounter">0</p>
        </div>

        <div class="widget">
            <h3>Top Selling Product</h3>
            <p>{{ $topProduct->name ?? 'N/A' }}</p>
        </div>
    @elseif(auth()->user()->hasRole('cashier'))
        <div class="widget">
            <h3>Today's Sales</h3>
            <p id="salesCounter">0</p>
        </div>

        <div class="widget">
            <h3>Low Stock Items</h3>
            <p id="lowStockCounter">0</p>
        </div>
    @endif
</div>

{{-- RECENT SALES (ADMIN & MANAGER ONLY) --}}
@if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager'))
    <div class="card">
        <h3>Recent Sales</h3>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSales ?? [] as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>{{ $sale->customer_name ?? 'Walk-in' }}</td>
                        <td>{{ $sale->user->name ?? 'N/A' }}</td>
                        <td>{{ number_format($sale->total, 2) }}</td>
                        <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;">No recent sales.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SALES CHART --}}
    <div class="card">
        <h3>Sales Overview (Last 7 Days)</h3>
        <canvas id="salesChart" height="250"></canvas>
    </div>
@endif

@endsection


@push('scripts')
@php
    // Build one array with all dashboard data
    $dashboardData = [
        'todaySales'     => $todaySales    ?? 0,
        'todayProfit'    => $todayProfit   ?? 0,
        'lowStockCount'  => $lowStockCount ?? 0,
        'chartDays'      => $chartDays     ?? [],
        'chartTotals'    => $chartTotals   ?? [],
        'isAdmin'        => auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager'),
    ];
@endphp

<script>
// Small helper
function animateCounter(id, target){
    let count = 0;
    const el = document.getElementById(id);
    if (!el) return;

    const speed = Math.ceil(target / 100) || 1;

    const interval = setInterval(() => {
        count += speed;
        if (count >= target) count = target;
        el.innerText = count.toLocaleString();
        if (count >= target) clearInterval(interval);
    }, 20);
}

// Expose data from backend as pure JSON (no Blade syntax inside JS)
window.dashboardData = <?php echo json_encode($dashboardData); ?>;

document.addEventListener('DOMContentLoaded', function () {
    const d = window.dashboardData || {};

    // Counters
    animateCounter('salesCounter',  Number(d.todaySales  || 0));
    animateCounter('profitCounter', Number(d.todayProfit || 0));
    animateCounter('lowStockCounter', Number(d.lowStockCount || 0));

    // Chart only for admin/manager
    if (d.isAdmin) {
        const canvas = document.getElementById('salesChart');
        if (canvas && Array.isArray(d.chartDays) && Array.isArray(d.chartTotals)) {
            const ctx = canvas.getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: d.chartDays,
                    datasets: [{
                        label: 'Sales',
                        data: d.chartTotals,
                        backgroundColor: 'rgba(37,99,235,0.2)',
                        borderColor: '#2563eb',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { labels: { color: 'white' } }
                    },
                    scales: {
                        x: {
                            ticks: { color: 'white' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        },
                        y: {
                            ticks: { color: 'white' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        }
                    }
                }
            });
        }
    }
});
</script>
@endpush


