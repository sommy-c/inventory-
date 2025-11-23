@extends('admin.layout')

@section('title', 'Damages & Expired Stock')

@section('content')

<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
}

/* ====== PAGE LAYOUT ====== */
.damages-page {
    padding: 20px;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

/* theme text */
body.theme-dark .damages-page {
    color: #e5e7eb;
}
body.theme-light .damages-page {
    color: var(--orange-main);
}

/* ====== HEADER ====== */
.damages-page .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.damages-page .page-header h1 {
    font-size: 26px;
    font-weight: 600;
    margin: 0;
}
body.theme-dark .damages-page .page-header h1 {
    color: #f9fafb;
}
body.theme-light .damages-page .page-header h1 {
    color: var(--orange-strong);
}

.damages-page .header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* ====== BUTTONS ====== */
.damages-page .btn-primary,
.damages-page .btn-secondary,
.damages-page .btn-resolve {
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

/* primary button */
body.theme-dark .damages-page .btn-primary {
    background: rgba(37, 99, 235, 0.9);
    color: #f9fafb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}
body.theme-dark .damages-page .btn-primary:hover {
    background: rgba(37, 99, 235, 1);
    transform: translateY(-1px);
}

body.theme-light .damages-page .btn-primary {
    background: var(--orange-light);
    color: #fff7ed;
    box-shadow: 0 4px 10px rgba(248,148,6,0.32);
}
body.theme-light .damages-page .btn-primary:hover {
    background: var(--orange-light-hover);
    transform: translateY(-1px);
}

/* secondary button */
body.theme-dark .damages-page .btn-secondary {
    background: rgba(15, 23, 42, 0.9);
    color: #e5e7eb;
    border: 1px solid rgba(148, 163, 184, 0.6);
}
body.theme-dark .damages-page .btn-secondary:hover {
    background: rgba(30, 64, 175, 0.9);
    border-color: rgba(129, 140, 248, 0.9);
}

body.theme-light .damages-page .btn-secondary {
    background: #ffffff;
    color: var(--orange-main);
    border: 1px solid rgba(209,213,219,0.95);
}
body.theme-light .damages-page .btn-secondary:hover {
    background: #fffbeb;
    border-color: var(--orange-light);
}

/* resolve pill in table */
.damages-page .btn-resolve {
    background: rgba(16, 185, 129, 0.9);
    color: #ecfdf5;
    border-radius: 999px;
    padding-inline: 12px;
}
.damages-page .btn-resolve:hover {
    background: rgba(5, 150, 105, 1);
}

/* Export button group */
.damages-page .export-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* ====== ALERTS ====== */
.damages-page .alert {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 14px;
    font-size: 0.9rem;
}

/* dark alerts */
body.theme-dark .damages-page .alert-success {
    background: rgba(22, 163, 74, 0.15);
    border: 1px solid rgba(22, 163, 74, 0.6);
    color: #bbf7d0;
}
body.theme-dark .damages-page .alert-error {
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(220, 38, 38, 0.7);
    color: #fecaca;
}

/* light alerts */
body.theme-light .damages-page .alert-success {
    background: rgba(22,163,74,0.08);
    border: 1px solid rgba(22,163,74,0.7);
    color: #166534;
}
body.theme-light .damages-page .alert-error {
    background: rgba(248,113,113,0.08);
    border: 1px solid rgba(248,113,113,0.7);
    color: #b91c1c;
}

.damages-page .alert-error ul {
    margin: 0;
    padding-left: 16px;
}

/* ====== STATS CARDS ====== */
.damages-page .stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.damages-page .stat-card {
    border-radius: 14px;
    padding: 12px 14px;
    border: 1px solid;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.75);
}

body.theme-dark .damages-page .stat-card {
    background: radial-gradient(circle at top left, #1d4ed8, #020617);
    border-color: rgba(148, 163, 184, 0.4);
}
body.theme-light .damages-page .stat-card {
    background: radial-gradient(circle at top left, #fed7aa, #fff7ed);
    border-color: var(--border-soft);
    box-shadow: 0 8px 22px rgba(15,23,42,0.2);
}

.damages-page .stat-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}
body.theme-dark .damages-page .stat-label {
    color: #9ca3af;
}
body.theme-light .damages-page .stat-label {
    color: var(--orange-strong);
}

.damages-page .stat-value {
    font-size: 1.4rem;
    font-weight: 700;
}
body.theme-dark .damages-page .stat-value {
    color: #f9fafb;
}
body.theme-light .damages-page .stat-value {
    color: var(--orange-main);
}

/* ====== FILTER FORM ====== */
.damages-page .filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 18px;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid;
}

/* background per theme */
body.theme-dark .damages-page .filter-form {
    background: rgba(15, 23, 42, 0.9);
    border-color: rgba(148, 163, 184, 0.4);
}
body.theme-light .damages-page .filter-form {
    background: rgba(255,255,255,0.96);
    border-color: var(--border-soft);
    box-shadow: 0 8px 22px rgba(15,23,42,0.18);
}

.damages-page .filter-form select,
.damages-page .filter-form input[type="date"] {
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 0.85rem;
    min-width: 150px;
    border: 1px solid;
}

/* input theme */
body.theme-dark .damages-page .filter-form select,
body.theme-dark .damages-page .filter-form input[type="date"] {
    background: #020617;
    border-color: rgba(148, 163, 184, 0.7);
    color: #e5e7eb;
}
body.theme-light .damages-page .filter-form select,
body.theme-light .damages-page .filter-form input[type="date"] {
    background: #ffffff;
    border-color: rgba(209,213,219,0.9);
    color: var(--orange-main);
}

.damages-page .filter-form select:focus,
.damages-page .filter-form input[type="date"]:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.7);
}

/* filter submit button */
.damages-page .filter-form button[type="submit"] {
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.1s ease;
}

body.theme-dark .damages-page .filter-form button[type="submit"] {
    background: rgba(37, 99, 235, 0.95);
    color: #f9fafb;
}
body.theme-dark .damages-page .filter-form button[type="submit"]:hover {
    background: rgba(37, 99, 235, 1);
    transform: translateY(-1px);
}

body.theme-light .damages-page .filter-form button[type="submit"] {
    background: var(--orange-light);
    color: #fff7ed;
}
body.theme-light .damages-page .filter-form button[type="submit"]:hover {
    background: var(--orange-light-hover);
    transform: translateY(-1px);
}

.damages-page .clear-link {
    align-self: center;
    font-size: 0.8rem;
    text-decoration: underline;
}
body.theme-dark .damages-page .clear-link {
    color: #9ca3af;
}
body.theme-dark .damages-page .clear-link:hover {
    color: #e5e7eb;
}
body.theme-light .damages-page .clear-link {
    color: var(--orange-strong);
}
body.theme-light .damages-page .clear-link:hover {
    color: var(--orange-main);
}

/* ====== CHART CARD ====== */
.damages-page .chart-card {
    margin-top: 10px;
    margin-bottom: 18px;
    border-radius: 14px;
    padding: 12px 16px;
    border: 1px solid;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.9);
}

body.theme-dark .damages-page .chart-card {
    background: rgba(15, 23, 42, 0.95);
    border-color: rgba(148, 163, 184, 0.4);
}
body.theme-light .damages-page .chart-card {
    background: rgba(255,255,255,0.98);
    border-color: var(--border-soft);
}

.damages-page .chart-card h3 {
    margin: 0 0 8px 0;
    font-size: 1rem;
}
body.theme-dark .damages-page .chart-card h3 {
    color: #e5e7eb;
}
body.theme-light .damages-page .chart-card h3 {
    color: var(--orange-strong);
}

/* ====== TABLE ====== */
.damages-page .table-wrapper {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.9);
}

body.theme-dark .damages-page .table-wrapper {
    background: rgba(15, 23, 42, 0.95);
    border-color: rgba(148, 163, 184, 0.4);
}
body.theme-light .damages-page .table-wrapper {
    background: rgba(255,255,255,0.98);
    border-color: var(--border-soft);
}

.damages-page .table-wrapper table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.damages-page .table-wrapper thead {
    background: linear-gradient(to right, #1d4ed8, #1e40af);
}
body.theme-light .damages-page .table-wrapper thead {
    background: linear-gradient(to right, #fed7aa, #fdba74);
}

.damages-page .table-wrapper thead th {
    padding: 10px 12px;
    text-align: left;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
body.theme-dark .damages-page .table-wrapper thead th {
    color: #e5e7eb;
}
body.theme-light .damages-page .table-wrapper thead th {
    color: var(--orange-strong);
}

.damages-page .table-wrapper tbody tr {
    transition: background 0.15s ease, transform 0.1s ease;
}

/* row stripes */
body.theme-dark .damages-page .table-wrapper tbody tr {
    border-bottom: 1px solid rgba(55, 65, 81, 0.7);
}
body.theme-dark .damages-page .table-wrapper tbody tr:hover {
    background: rgba(30, 64, 175, 0.25);
}

body.theme-light .damages-page .table-wrapper tbody tr {
    border-bottom: 1px solid rgba(229,231,235,0.9);
}
body.theme-light .damages-page .table-wrapper tbody tr:nth-child(even) {
    background: rgba(255,255,255,0.98);
}
body.theme-light .damages-page .table-wrapper tbody tr:nth-child(odd) {
    background: rgba(255,255,255,0.94);
}
body.theme-light .damages-page .table-wrapper tbody tr:hover {
    background: rgba(254,243,199,0.9);
}

.damages-page .table-wrapper tbody td {
    padding: 8px 10px;
    font-size: 0.85rem;
}
body.theme-dark .damages-page .table-wrapper tbody td {
    color: #e5e7eb;
}
body.theme-light .damages-page .table-wrapper tbody td {
    color: var(--orange-main);
}

/* ====== BADGES ====== */
.damages-page .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* type badges */
.damages-page .badge-damaged {
    background: rgba(220, 38, 38, 0.2);
    color: #fecaca;
    border: 1px solid rgba(220, 38, 38, 0.7);
}
.damages-page .badge-expired {
    background: rgba(234, 179, 8, 0.2);
    color: #fef9c3;
    border: 1px solid rgba(234, 179, 8, 0.7);
}

/* status badges (shared with other pages) */
.badge-status-pending {
    background: rgba(234, 179, 8, 0.12);
    color: #fef9c3;
    border: 1px solid rgba(234, 179, 8, 0.7);
}

.badge-status-open {
    background: rgba(59, 130, 246, 0.15);
    color: #bfdbfe;
    border: 1px solid rgba(59, 130, 246, 0.8);
}

.badge-status-resolved {
    background: rgba(22, 163, 74, 0.15);
    color: #bbf7d0;
    border: 1px solid rgba(22, 163, 74, 0.8);
}

.badge-status-rejected {
    background: rgba(220, 38, 38, 0.12);
    color: #fecaca;
    border: 1px solid rgba(220, 38, 38, 0.8);
}

/* ====== RESOLVE FORM ====== */
.damages-page .resolve-form {
    display: flex;
    align-items: center;
    gap: 6px;
}

.damages-page .resolve-input {
    width: 70px;
    padding: 4px 6px;
    border-radius: 8px;
    border: 1px solid rgba(148, 163, 184, 0.7);
    background: #020617;
    color: #e5e7eb;
    font-size: 0.8rem;
}
.damages-page .resolve-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.7);
}

/* ====== PAGINATION (unified Tailwind/Bootstrap) ====== */
.damages-page .pagination-wrapper {
    margin-top: 16px;
    display: flex;
    justify-content: center;
}

/* Tailwind-style nav */
.damages-page .pagination-wrapper nav {
    display: flex !important;
    justify-content: center !important;
}

/* hide "Showing x to y of z" */
.damages-page .pagination-wrapper nav > div:first-child {
    display: none !important;
}

/* inner Tailwind <div> */
.damages-page .pagination-wrapper nav > div {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Bootstrap <ul> */
.damages-page .pagination-wrapper .pagination {
    display: flex !important;
    justify-content: center !important;
    align-items: center;
    gap: 4px;
    padding-left: 0;
    margin: 0;
}

/* base pill */
.damages-page .pagination-wrapper nav a,
.damages-page .pagination-wrapper nav span,
.damages-page .pagination-wrapper .page-link {
    padding: 4px 8px !important;
    margin: 2px 2px !important;
    font-size: 0.8rem !important;
    line-height: 1 !important;
    border-radius: 999px;
    text-decoration: none !important;
    border-width: 1px;
    border-style: solid;
}

/* colors: dark */
body.theme-dark .damages-page .pagination-wrapper nav a,
body.theme-dark .damages-page .pagination-wrapper nav span,
body.theme-dark .damages-page .pagination-wrapper .page-link {
    color: #e5e7eb;
    border-color: rgba(148,163,184,0.65);
    background: rgba(15,23,42,0.95);
}

/* colors: light */
body.theme-light .damages-page .pagination-wrapper nav a,
body.theme-light .damages-page .pagination-wrapper nav span,
body.theme-light .damages-page .pagination-wrapper .page-link {
    color: var(--orange-main);
    border-color: rgba(209,213,219,0.9);
    background: rgba(255,255,255,0.95);
}

/* active */
body.theme-dark .damages-page .pagination-wrapper nav span[aria-current="page"],
body.theme-dark .damages-page .pagination-wrapper .page-item.active .page-link {
    background: rgba(37,99,235,1);
    border-color: rgba(37,99,235,1);
    color: #ffffff;
}
body.theme-light .damages-page .pagination-wrapper nav span[aria-current="page"],
body.theme-light .damages-page .pagination-wrapper .page-item.active .page-link {
    background: var(--orange-light);
    border-color: var(--orange-light-hover);
    color: #ffffff;
}

/* hover */
body.theme-dark .damages-page .pagination-wrapper nav a:hover,
body.theme-dark .damages-page .pagination-wrapper .page-link:hover {
    background: rgba(37,99,235,0.9);
    border-color: rgba(37,99,235,1);
    transform: translateY(-1px);
}
body.theme-light .damages-page .pagination-wrapper nav a:hover,
body.theme-light .damages-page .pagination-wrapper .page-link:hover {
    background: rgba(254,243,199,0.95);
    border-color: var(--orange-light);
    transform: translateY(-1px);
}

/* disabled */
.damages-page .pagination-wrapper nav span[aria-disabled="true"],
.damages-page .pagination-wrapper .page-item.disabled .page-link {
    opacity: 0.45;
    cursor: not-allowed;
    transform: none;
}

/* shrink svg arrows */
.damages-page .pagination-wrapper svg {
    width: 12px !important;
    height: 12px !important;
}

/* ====== RESPONSIVE ====== */
@media (max-width: 900px) {
    .damages-page .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 640px) {
    .damages-page .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .damages-page .stats-grid {
        grid-template-columns: 1fr;
    }
    .damages-page .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    .damages-page .filter-form select,
    .damages-page .filter-form input[type="date"],
    .damages-page .filter-form button[type="submit"] {
        width: 100%;
    }
    .damages-page .table-wrapper table {
        min-width: 700px;
    }
}

/* ===========================
   APPROVE / REJECT / RESOLVE
=========================== */
.btn-approve {
    background: rgba(34, 197, 94, 0.9);
    color: #ecfdf5;
    border: none;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-approve:hover {
    background: rgba(22, 163, 74, 1);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
}
.btn-approve:active {
    transform: translateY(0);
}

.btn-reject {
    background: rgba(239, 68, 68, 0.9);
    color: #fee2e2;
    border: none;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-reject:hover {
    background: rgba(220, 38, 38, 1);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}
.btn-reject:active {
    transform: translateY(0);
}

/* Optional small action buttons */
.btn-sm-action {
    padding: 4px 10px;
    font-size: 0.75rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.15s ease;
}
.btn-sm-action.green {
    background: rgba(34, 197, 94, 0.7);
    color: white;
}
.btn-sm-action.green:hover {
    background: rgba(34, 197, 94, 1);
}
.btn-sm-action.red {
    background: rgba(239, 68, 68, 0.7);
    color: white;
}
.btn-sm-action.red:hover {
    background: rgba(239, 68, 68, 1);
}

/* Disabled */
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<div class="customers-page damages-page">
    <div class="page-header">
        <h1>Damages & Expired</h1>

        <div class="header-actions">
            <a href="{{ route('admin.damages.create') }}" class="btn-primary">
                + Log Damage / Expired
            </a>

            <div class="export-group">
                <a href="{{ route('admin.damages.export.excel') }}" class="btn-secondary">Export Excel</a>
                <a href="{{ route('admin.damages.export.pdf') }}" class="btn-secondary">Export PDF</a>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Stats cards --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Damaged</div>
            <div class="stat-value">{{ $totalDamaged }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Expired</div>
            <div class="stat-value">{{ $totalExpired }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Open Entries</div>
            <div class="stat-value">{{ $openCount }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.damages.index') }}" class="filter-form">
        <select name="type">
            <option value="">All Types</option>
            <option value="damaged" {{ $filterType === 'damaged' ? 'selected' : '' }}>Damaged</option>
            <option value="expired" {{ $filterType === 'expired' ? 'selected' : '' }}>Expired</option>
        </select>

        <select name="product_id">
            <option value="">All Products</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" {{ $filterProduct == $p->id ? 'selected' : '' }}>
                    {{ $p->name }} ({{ $p->sku }})
                </option>
            @endforeach
        </select>

        <select name="supplier">
            <option value="">All Suppliers</option>
            @foreach($suppliers as $sup)
                <option value="{{ $sup }}" {{ $filterSupplier == $sup ? 'selected' : '' }}>
                    {{ $sup }}
                </option>
            @endforeach
        </select>

        <input type="date" name="from" value="{{ $filterFrom }}">
        <input type="date" name="to" value="{{ $filterTo }}">

        <button type="submit">Filter</button>

        @if(request()->hasAny(['type','product_id','supplier','from','to']))
            <a href="{{ route('admin.damages.index') }}" class="clear-link">Clear</a>
        @endif
    </form>

    {{-- Trend chart --}}
    <div class="chart-card">
        <h3>Damage Trend (Last 30 days)</h3>
        <canvas id="damageTrendChart" height="80"></canvas>
    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Supplier</th>
                    <th>Qty</th>
                    <th>Remaining</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Logged By</th>
                    <th>Resolve</th>
                </tr>
            </thead>
            <tbody>
@forelse($damages as $dmg)
    <tr>
        <td>{{ $dmg->created_at->format('Y-m-d') }}</td>
        <td>
            <span class="badge badge-{{ $dmg->type }}">
                {{ ucfirst($dmg->type) }}
            </span>
        </td>
        <td>{{ $dmg->product->name ?? 'Deleted product' }}</td>
        <td>{{ $dmg->product->sku ?? '-' }}</td>
        <td>{{ $dmg->product->supplier ?? '-' }}</td>
        <td>{{ $dmg->quantity }}</td>
        <td>{{ $dmg->remaining }}</td>
        <td>{{ $dmg->expiry_date?->format('Y-m-d') ?? '-' }}</td>

        {{-- STATUS column --}}
        <td>
            <span class="badge badge-status-{{ $dmg->status }}">
                {{ ucfirst($dmg->status) }}
            </span>
        </td>

        {{-- Logged By --}}
        <td>{{ $dmg->user->name ?? '-' }}</td>

        {{-- RESOLVE / APPROVE / REJECT column --}}
        <td>
            @php
                $isAdmin = auth()->user()->hasRole('admin');
            @endphp

            {{-- Pending: only admin sees Approve / Reject --}}
            @if($dmg->status === 'pending')
                @if($isAdmin)
                    <div class="resolve-form" style="flex-direction: column; align-items: flex-start; gap:4px;">
                        <form action="{{ route('admin.damages.approve', $dmg->id) }}"
                              method="POST"
                              onsubmit="return confirm('Approve this damage and reduce stock?');">
                            @csrf
                            <button type="submit" class="btn-approve">
                                Approve
                            </button>
                        </form>

                        <form action="{{ route('admin.damages.reject', $dmg->id) }}"
                              method="POST"
                              onsubmit="return confirm('Reject this damage entry? No stock will be changed.');">
                            @csrf
                            <button type="submit" class="btn-reject">
                                Reject
                            </button>
                        </form>
                    </div>
                @else
                    <span style="font-size: 0.8rem; color:#facc15;">Pending approval</span>
                @endif

            {{-- Open: resolving allowed --}}
            @elseif($dmg->status === 'open' && $dmg->remaining > 0)
                <form action="{{ route('admin.damages.resolve', $dmg->id) }}"
                      method="POST"
                      class="resolve-form">
                    @csrf
                    <input type="number"
                           name="resolved_quantity"
                           min="1"
                           max="{{ $dmg->remaining }}"
                           value="{{ $dmg->remaining }}"
                           class="resolve-input">
                    <button type="submit" class="btn-resolve">
                        Resolve
                    </button>
                </form>

            {{-- Resolved / Rejected --}}
            @else
                <span style="font-size: 0.8rem; color:#9ca3af;">—</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" style="text-align:center;">No damage records found.</td>
    </tr>
@endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $damages->withQueryString()->links() }}
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = JSON.parse(`{!! json_encode($trendDays) !!}`);
    const data   = JSON.parse(`{!! json_encode($trendTotals) !!}`);

    const canvas = document.getElementById('damageTrendChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Damaged / Expired Qty (Last 30 days)',
                data: data,
                borderWidth: 2,
                tension: 0.2,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
});
</script>
@endsection
