@extends('admin.layout')
@section('title','Dashboard')

@section('content')
<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
}

/* ============= PAGE LAYOUT ============= */
.dashboard-page {
    padding: 20px;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

/* theme text */
body.theme-dark .dashboard-page {
    color: #e5e7eb;
}
body.theme-light .dashboard-page {
    color: var(--orange-main);
}

/* ============= HEADER ============= */
.dashboard-page .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.dashboard-page .page-header h1 {
    font-size: 26px;
    font-weight: 600;
    margin: 0;
}
body.theme-dark .dashboard-page .page-header h1 {
    color: #f9fafb;
}
body.theme-light .dashboard-page .page-header h1 {
    color: var(--orange-strong);
}

.dashboard-page .header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* ============= BUTTONS (match damages) ============= */
.dashboard-page .btn-primary,
.dashboard-page .btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    border-radius: 8px;
    border: none;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.15s ease;
    white-space: nowrap;
}

/* primary */
body.theme-dark .dashboard-page .btn-primary {
    background: rgba(37, 99, 235, 0.9);
    color: #f9fafb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}
body.theme-dark .dashboard-page .btn-primary:hover {
    background: rgba(37, 99, 235, 1);
    transform: translateY(-1px);
}

body.theme-light .dashboard-page .btn-primary {
    background: var(--orange-light);
    color: #fff7ed;
    box-shadow: 0 4px 10px rgba(248,148,6,0.32);
}
body.theme-light .dashboard-page .btn-primary:hover {
    background: var(--orange-light-hover);
    transform: translateY(-1px);
}

/* secondary */
body.theme-dark .dashboard-page .btn-secondary {
    background: rgba(15, 23, 42, 0.9);
    color: #e5e7eb;
    border: 1px solid rgba(148, 163, 184, 0.6);
}
body.theme-dark .dashboard-page .btn-secondary:hover {
    background: rgba(30, 64, 175, 0.9);
    border-color: rgba(129, 140, 248, 0.9);
}

body.theme-light .dashboard-page .btn-secondary {
    background: #ffffff;
    color: var(--orange-main);
    border: 1px solid rgba(209,213,219,0.95);
}
body.theme-light .dashboard-page .btn-secondary:hover {
    background: #fffbeb;
    border-color: var(--orange-light);
}

/* ============= WIDGET CARDS ============= */
.dashboard-page .widgets {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}

.dashboard-page .widget {
    border-radius: 14px;
    padding: 12px 14px;
    border: 1px solid;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.75);
}

/* dark */
body.theme-dark .dashboard-page .widget {
    background: radial-gradient(circle at top left, #1d4ed8, #020617);
    border-color: rgba(148,163,184,0.45);
}

/* light */
body.theme-light .dashboard-page .widget {
    background: radial-gradient(circle at top left, #fed7aa, #fff7ed);
    border-color: var(--border-soft);
    box-shadow: 0 8px 22px rgba(15,23,42,0.2);
}

.dashboard-page .widget-label {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}
body.theme-dark .dashboard-page .widget-label {
    color: #d1d5db;
}
body.theme-light .dashboard-page .widget-label {
    color: var(--orange-strong);
}

.dashboard-page .widget-value {
    font-size: 1.6rem;
    font-weight: 700;
}
body.theme-dark .dashboard-page .widget-value {
    color: #f9fafb;
}
body.theme-light .dashboard-page .widget-value {
    color: var(--orange-main);
}

.dashboard-page .widget-value.widget-text {
    font-size: 1.05rem;
    word-break: break-word;
}

/* ============= CHART CARD (reuse damages style) ============= */
.dashboard-page .chart-card {
    margin-top: 10px;
    margin-bottom: 18px;
    border-radius: 14px;
    padding: 12px 16px;
    border: 1px solid;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.9);
}

/* dark */
body.theme-dark .dashboard-page .chart-card {
    background: rgba(15, 23, 42, 0.95);
    border-color: rgba(148, 163, 184, 0.4);
}

/* light */
body.theme-light .dashboard-page .chart-card {
    background: rgba(255,255,255,0.98);
    border-color: var(--border-soft);
    box-shadow: 0 10px 24px rgba(15,23,42,0.16);
}

.dashboard-page .chart-card h3 {
    margin: 0 0 8px 0;
    font-size: 1rem;
}
body.theme-dark .dashboard-page .chart-card h3 {
    color: #e5e7eb;
}
body.theme-light .dashboard-page .chart-card h3 {
    color: var(--orange-strong);
}

/* responsive */
@media (max-width: 640px) {
    .dashboard-page .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<div class="dashboard-page">

    <div class="page-header">
        <h1>Dashboard</h1>
        <div class="header-actions">
            {{-- Example header actions --}}
            {{-- <a href="#" class="btn-primary">New Sale</a> --}}
        </div>
    </div>

    <div class="widgets">
        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager'))
            <div class="widget">
                <div class="widget-label">Total Sales Today</div>
                <div class="widget-value" id="salesCounter">0</div>
            </div>

            <div class="widget">
                <div class="widget-label">Total Profit</div>
                <div class="widget-value" id="profitCounter">0</div>
            </div>

            <div class="widget">
                <div class="widget-label">Low Stock Items</div>
                <div class="widget-value" id="lowStockCounter">0</div>
            </div>

            <div class="widget">
                <div class="widget-label">Top Selling Product</div>
                <div class="widget-value widget-text">
                    {{ $topProduct->name ?? 'N/A' }}
                </div>
            </div>
        @elseif(auth()->user()->hasRole('cashier'))
            <div class="widget">
                <div class="widget-label">Today's Sales</div>
                <div class="widget-value" id="salesCounter">0</div>
            </div>

            <div class="widget">
                <div class="widget-label">Low Stock Items</div>
                <div class="widget-value" id="lowStockCounter">0</div>
            </div>
        @endif
    </div>

    {{-- SALES CHART --}}
    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager'))
        <div class="chart-card">
            <h3>Sales Overview (Last 7 Days)</h3>
            <canvas id="salesChart" height="250"></canvas>
        </div>
    @endif

</div> {{-- /.dashboard-page --}}
@endsection

@push('scripts')
<script>
// @ts-nocheck   // ignore TS warnings in this Blade-mixed JS file

window.dashboardData = JSON.parse(`{!! json_encode([
    'todaySales'     => $todaySales    ?? 0,
    'todayProfit'    => $todayProfit   ?? 0,
    'lowStockCount'  => $lowStockCount ?? 0,
    'chartDays'      => $chartDays     ?? [],
    'chartTotals'    => $chartTotals   ?? [],
    'isAdmin'        => auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager'),
]) !!}`);

function animateCounter(id, target) {
    const el = document.getElementById(id);
    if (!el) return;

    let count = 0;
    target = Number(target || 0);

    if (!target) {
        el.innerText = '0';
        return;
    }

    const step = Math.ceil(target / 100) || 1;

    const interval = setInterval(() => {
        count += step;
        if (count >= target) count = target;
        el.innerText = count.toLocaleString();

        if (count >= target) clearInterval(interval);
    }, 20);
}

let salesChartInstance = null;

function initDashboard() {
    const d = window.dashboardData || {};

    const salesEl    = document.getElementById('salesCounter');
    const profitEl   = document.getElementById('profitCounter');
    const lowStockEl = document.getElementById('lowStockCounter');

    if (salesEl)    salesEl.innerText    = '0';
    if (profitEl)   profitEl.innerText   = '0';
    if (lowStockEl) lowStockEl.innerText = '0';

    animateCounter('salesCounter',    d.todaySales);
    animateCounter('profitCounter',   d.todayProfit);
    animateCounter('lowStockCounter', d.lowStockCount);

    if (!d.isAdmin) return;

    const canvas = document.getElementById('salesChart');
    if (!canvas || !Array.isArray(d.chartDays) || !Array.isArray(d.chartTotals)) return;
    if (typeof Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');

    // detect light/dark from body class (same as other pages)
    const isLight = document.body.classList.contains('theme-light');

    const axisTextColor   = isLight ? '#9a3412' : '#e5e7eb';
    const gridColor       = isLight ? 'rgba(148,163,184,0.35)' : 'rgba(255,255,255,0.1)';
    const legendTextColor = axisTextColor;

    // Optional: adjust chart line color a bit for light mode
    const borderColor = isLight ? '#f97316' : '#2563eb';
    const fillColor   = isLight ? 'rgba(249,115,22,0.15)' : 'rgba(37,99,235,0.2)';

    if (salesChartInstance && typeof salesChartInstance.destroy === 'function') {
        salesChartInstance.destroy();
    }

    salesChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: d.chartDays,
            datasets: [{
                label: 'Sales',
                data: d.chartTotals,
                backgroundColor: fillColor,
                borderColor: borderColor,
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: legendTextColor }
                }
            },
            scales: {
                x: {
                    ticks: { color: axisTextColor },
                    grid:  { color: gridColor }
                },
                y: {
                    ticks: { color: axisTextColor },
                    grid:  { color: gridColor }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', initDashboard);

window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        initDashboard();
    }
});
</script>
@endpush
