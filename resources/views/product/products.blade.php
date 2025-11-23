@extends('admin.layout')
@section('title','Products')

@section('content')
<div class="products-container">
    <h3 class="page-title">Products</h3>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Search & Filters -->
    <form method="GET" action="{{ url()->current() }}" class="search-form">
        {{-- Search text --}}
        <input type="text"
               name="search"
               value="{{ $search ?? '' }}"
               placeholder="Search by name, SKU, barcode..."
               autofocus>

        {{-- Status filter --}}
        <select name="status" class="filter-select">
            <option value="">All Statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" {{ ($statusFilt == $status) ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </option>
            @endforeach
        </select>

        {{-- Expiry filter --}}
        <select name="expiry" class="filter-select">
            <option value="">Expiry Filter</option>
            <option value="expired"  {{ $expiryFilt == 'expired' ? 'selected' : '' }}>Expired</option>
            <option value="expiring" {{ $expiryFilt == 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
        </select>

        {{-- Category filter --}}
        <select name="category" class="filter-select">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ (isset($categoryFilt) && $categoryFilt == $cat) ? 'selected' : '' }}>
                    {{ $cat }}
                </option>
            @endforeach
        </select>

        {{-- Supplier filter --}}
        <select name="supplier" class="filter-select">
            <option value="">All Suppliers</option>
            @foreach($suppliers as $sup)
                <option value="{{ $sup }}" {{ (isset($supplierFilt) && $supplierFilt == $sup) ? 'selected' : '' }}>
                    {{ $sup }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="filter-btn">Filter</button>

        {{-- Optional: clear filters --}}
        @if(request()->hasAny(['search','category','supplier','status','expiry']))
            <a href="{{ url()->current() }}" class="clear-filters-link">Clear</a>
        @endif
    </form>

    <!-- Products Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    @if(auth()->user()->hasAnyRole(['admin','manager']))
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td data-label="SKU">{{ $product->sku }}</td>
                        <td data-label="Name">
                            <a href="{{ auth()->user()->hasRole('cashier') 
                                       ? route('cashier.products.show', $product->id) 
                                       : route('admin.products.show', $product->id) }}">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td data-label="Category">{{ $product->category ?? '-' }}</td>
                        <td data-label="Brand">{{ $product->brand ?? '-' }}</td>
                        <td data-label="Quantity">{{ $product->quantity }}</td>
                        <td data-label="Status">{{ ucfirst($product->status) }}</td>

                        @if(auth()->user()->hasAnyRole(['admin','manager']))
                            <td data-label="Actions" class="actions-cell">
                                <!-- Edit (Admin & Manager) -->
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-table btn-edit">
                                    Edit
                                </a>

                                <!-- Toggle suspend/activate (Admin & Manager) -->
                                <form action="{{ route('admin.products.toggle', $product->id) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-table btn-toggle">
                                        {{ $product->is_suspended ? 'Activate' : 'Suspend' }}
                                    </button>
                                </form>

                                <!-- Delete (Admin Only) -->
                                @if(auth()->user()->hasRole('admin'))
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn-table btn-delete"
                                                onclick="return confirm('Are you sure you want to delete this product?');">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-wrapper">
        {{ $products->withQueryString()->links() }}
    </div>
</div>

<!-- Full Page Loading Overlay -->
<div id="loading-overlay">
    <div class="spinner"></div>
</div>

{{-- THEME-AWARE STYLES --}}
<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
    --muted-text: #7c2d12;
}

/* ---------- PAGE LAYOUT (shared) ---------- */
.products-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 16px 32px;
    min-height: 100vh;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.page-title {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 18px;
    text-align: left;
}

/* Dark theme text */
body.theme-dark .products-container {
    color: #f9fafb;
}

/* Light theme text */
body.theme-light .products-container {
    color: var(--orange-main);
}

/* ---------- ALERTS ---------- */
.alert {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

/* dark theme alerts */
body.theme-dark .alert-success {
    background: rgba(22, 163, 74, 0.15);
    border: 1px solid rgba(22, 163, 74, 0.6);
    color: #bbf7d0;
}

body.theme-dark .alert-error {
    background: rgba(220, 38, 38, 0.12);
    border: 1px solid rgba(248, 113, 113, 0.7);
    color: #fecaca;
}

/* light theme alerts */
body.theme-light .alert-success {
    background: rgba(22,163,74,0.08);
    border: 1px solid rgba(22,163,74,0.6);
    color: #166534;
}

body.theme-light .alert-error {
    background: rgba(248,113,113,0.08);
    border: 1px solid rgba(248,113,113,0.7);
    color: #b91c1c;
}

/* ---------- SEARCH & FILTERS ---------- */
.search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 16px;
    padding: 10px;
    border-radius: 10px;
}

/* dark */
body.theme-dark .search-form {
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(148, 163, 184, 0.4);
}

/* light */
body.theme-light .search-form {
    background: rgba(255,255,255,0.9);
    border: 1px solid var(--border-soft);
    box-shadow: 0 10px 24px rgba(0,0,0,0.06);
}

.search-form input[type="text"] {
    flex: 1 1 220px;
    padding: 8px 10px;
    border-radius: 6px;
    outline: none;
    font-size: 0.9rem;
    border: 1px solid transparent;
}

body.theme-dark .search-form input[type="text"] {
    background: #020617;
    color: #e5e7eb;
    border-color: rgba(75, 85, 99, 0.9);
}

body.theme-light .search-form input[type="text"] {
    background: rgba(255,255,255,0.98);
    color: var(--orange-main);
    border-color: rgba(209,213,219,0.9);
}

.search-form input[type="text"]::placeholder {
    color: #9ca3af;
    font-size: 0.85rem;
}

/* filters */
.filter-select {
    flex: 0 0 180px;
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 0.85rem;
    border: 1px solid transparent;
    outline: none;
}

/* dark */
body.theme-dark .filter-select {
    background: #020617;
    color: #e5e7eb;
    border-color: rgba(75, 85, 99, 0.9);
}

/* light */
body.theme-light .filter-select {
    background: rgba(255,255,255,0.98);
    color: var(--orange-main);
    border-color: rgba(209,213,219,0.9);
}

/* filter button */
.filter-btn {
    padding: 8px 14px;
    border-radius: 6px;
    border: none;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
}

/* dark */
body.theme-dark .filter-btn {
    background: rgba(37, 99, 235, 0.85);
    color: #f9fafb;
}

body.theme-dark .filter-btn:hover {
    background: rgba(37, 99, 235, 1);
    transform: translateY(-1px);
}

/* light */
body.theme-light .filter-btn {
    background: var(--orange-light);
    color: #fff;
    box-shadow: 0 4px 10px rgba(248,148,6,0.25);
}

body.theme-light .filter-btn:hover {
    background: var(--orange-light-hover);
    transform: translateY(-1px);
}

/* clear filters */
.clear-filters-link {
    font-size: 0.8rem;
    margin-left: auto;
    text-decoration: none;
    padding: 4px 6px;
    border-radius: 4px;
    transition: color 0.15s ease, background 0.15s ease;
}

/* dark */
body.theme-dark .clear-filters-link {
    color: #9ca3af;
}

body.theme-dark .clear-filters-link:hover {
    color: #e5e7eb;
    background: rgba(55, 65, 81, 0.75);
}

/* light */
body.theme-light .clear-filters-link {
    color: var(--muted-text);
}

body.theme-light .clear-filters-link:hover {
    background: rgba(254,243,199,0.9);
}

/* ---------- TABLE ---------- */
.table-wrapper {
    overflow-x: auto;
    margin-top: 8px;
    border-radius: 12px;
}

/* dark */
body.theme-dark .table-wrapper {
    background: rgba(15, 23, 42, 0.9);
    border: 1px solid rgba(31, 41, 55, 1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.55);
}

/* light */
body.theme-light .table-wrapper {
    background: rgba(255,255,255,0.9);
    border: 1px solid var(--border-soft);
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}

table {
    width: 100%;
    min-width: 720px;
    border-collapse: collapse;
}

/* header */
thead {
    background: linear-gradient(90deg, #1d4ed8, #2563eb);
}

/* light header tweak */
body.theme-light thead {
    background: linear-gradient(90deg, #fed7aa, #fdba74);
}

thead th {
    padding: 11px 14px;
    text-align: left;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid rgba(148, 163, 184, 0.4);
}

/* dark */
body.theme-dark thead th {
    color: #e5e7eb;
}

/* light */
body.theme-light thead th {
    color: var(--orange-strong);
}

/* rows */
tbody tr {
    border-bottom: 1px solid rgba(55, 65, 81, 0.85);
    transition: background 0.15s ease;
}

/* dark stripe */
body.theme-dark tbody tr:nth-child(even) {
    background: rgba(15, 23, 42, 0.92);
}
body.theme-dark tbody tr:nth-child(odd) {
    background: rgba(15, 23, 42, 0.98);
}

body.theme-dark tbody tr:hover {
    background: rgba(30, 64, 175, 0.35);
}

/* light stripe */
body.theme-light tbody tr {
    border-bottom: 1px solid rgba(229,231,235,0.9);
}

body.theme-light tbody tr:nth-child(even) {
    background: rgba(255,255,255,0.98);
}
body.theme-light tbody tr:nth-child(odd) {
    background: rgba(255,255,255,0.93);
}

body.theme-light tbody tr:hover {
    background: rgba(254,243,199,0.85);
}

/* cells */
tbody td {
    padding: 10px 14px;
    font-size: 0.86rem;
}

/* dark text */
body.theme-dark tbody td {
    color: #e5e7eb;
}

/* light text */
body.theme-light tbody td {
    color: var(--orange-main);
}

/* links */
tbody td a {
    text-decoration: none;
    font-weight: 500;
}

/* dark links */
body.theme-dark tbody td a {
    color: #93c5fd;
}

/* light links */
body.theme-light tbody td a {
    color: var(--orange-strong);
}

tbody td a:hover {
    text-decoration: underline;
}

/* Actions cell layout */
.actions-cell {
    white-space: nowrap;
}

/* ---------- TABLE BUTTONS ---------- */
.btn-table {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 10px;
    margin: 2px 3px;
    border-radius: 6px;
    border: 1px solid transparent;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}

/* Edit */
body.theme-dark .btn-edit {
    background: rgba(16, 185, 129, 0.18);
    color: #6ee7b7;
    border-color: rgba(16, 185, 129, 0.8);
}
body.theme-dark .btn-edit:hover {
    background: rgba(16, 185, 129, 0.35);
    transform: translateY(-1px);
}

body.theme-light .btn-edit {
    background: rgba(22,163,74,0.08);
    color: #166534;
    border-color: rgba(22,163,74,0.7);
}
body.theme-light .btn-edit:hover {
    background: rgba(22,163,74,0.2);
    transform: translateY(-1px);
}

/* Toggle */
body.theme-dark .btn-toggle {
    background: rgba(234, 179, 8, 0.15);
    color: #facc15;
    border-color: rgba(234, 179, 8, 0.8);
}
body.theme-dark .btn-toggle:hover {
    background: rgba(234, 179, 8, 0.35);
    transform: translateY(-1px);
}

body.theme-light .btn-toggle {
    background: rgba(250,204,21,0.08);
    color: #854d0e;
    border-color: rgba(250,204,21,0.7);
}
body.theme-light .btn-toggle:hover {
    background: rgba(250,204,21,0.2);
    transform: translateY(-1px);
}

/* Delete */
body.theme-dark .btn-delete {
    background: rgba(239, 68, 68, 0.15);
    color: #fecaca;
    border-color: rgba(239, 68, 68, 0.85);
}
body.theme-dark .btn-delete:hover {
    background: rgba(239, 68, 68, 0.3);
    transform: translateY(-1px);
}

body.theme-light .btn-delete {
    background: rgba(248,113,113,0.08);
    color: #b91c1c;
    border-color: rgba(239,68,68,0.85);
}
body.theme-light .btn-delete:hover {
    background: rgba(248,113,113,0.22);
    transform: translateY(-1px);
}

.inline-form {
    display: inline;
}

/* ---------- PAGINATION ---------- */
.pagination-wrapper {
    margin-top: 16px;
}

/* Tailwind-style nav */
.pagination-wrapper nav {
    display: flex;
    justify-content: center;
    margin-top: 6px;
}

/* Bootstrap-style ul.pagination (in case) */
.pagination-wrapper .pagination {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 4px;
    padding-left: 0;
}

/* Base styles for links/spans */
.pagination-wrapper nav a,
.pagination-wrapper nav span,
.pagination-wrapper .pagination .page-link {
    text-decoration: none !important;
    font-size: 0.85rem;
}

/* dark pills */
body.theme-dark .pagination-wrapper nav a,
body.theme-dark .pagination-wrapper nav span,
body.theme-dark .pagination-wrapper .pagination .page-link {
    color: #e5e7eb;
    padding: 6px 11px;
    margin: 2px 3px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.65);
    background: rgba(15, 23, 42, 0.95);
}

/* light pills */
body.theme-light .pagination-wrapper nav a,
body.theme-light .pagination-wrapper nav span,
body.theme-light .pagination-wrapper .pagination .page-link {
    color: var(--orange-main);
    padding: 6px 11px;
    margin: 2px 3px;
    border-radius: 999px;
    border: 1px solid rgba(209,213,219,0.9);
    background: rgba(255,255,255,0.95);
}

/* Active page (Tailwind) */
.pagination-wrapper nav span[aria-current="page"] {
    color: #ffffff;
}

/* dark active */
body.theme-dark .pagination-wrapper nav span[aria-current="page"] {
    background: rgba(37, 99, 235, 1);
    border-color: rgba(37, 99, 235, 1);
}

/* light active */
body.theme-light .pagination-wrapper nav span[aria-current="page"] {
    background: var(--orange-light);
    border-color: var(--orange-light-hover);
}

/* Active page (Bootstrap) */
body.theme-dark .pagination-wrapper .page-item.active .page-link {
    background: rgba(37, 99, 235, 1);
    border-color: rgba(37, 99, 235, 1);
    color: #ffffff;
}

body.theme-light .pagination-wrapper .page-item.active .page-link {
    background: var(--orange-light);
    border-color: var(--orange-light-hover);
    color: #ffffff;
}

/* Hover for clickable links */
body.theme-dark .pagination-wrapper nav a:hover,
body.theme-dark .pagination-wrapper .pagination .page-link:hover {
    background: rgba(37, 99, 235, 0.9);
    border-color: rgba(37, 99, 235, 1);
    transform: translateY(-1px);
}

body.theme-light .pagination-wrapper nav a:hover,
body.theme-light .pagination-wrapper .pagination .page-link:hover {
    background: rgba(254,243,199,0.95);
    border-color: var(--orange-light);
    transform: translateY(-1px);
}

/* Disabled (Tailwind & Bootstrap) */
.pagination-wrapper nav span[aria-disabled="true"],
.pagination-wrapper .page-item.disabled .page-link {
    opacity: 0.45;
    cursor: not-allowed;
}

/* Optional: hide "Showing X to Y of Z results" text (Tailwind) */
.pagination-wrapper nav > div:first-child {
    display: none;
}

/* ---------- LOADING OVERLAY ---------- */
#loading-overlay {
    position: fixed;
    inset: 0;
    display: none;
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

/* dark overlay */
body.theme-dark #loading-overlay {
    background: rgba(0,0,0,0.55);
}

/* light overlay */
body.theme-light #loading-overlay {
    background: rgba(255,255,255,0.35);
}

#loading-overlay .spinner {
    border: 6px solid rgba(148,163,184,0.4);
    border-top: 6px solid #2563eb;
    border-radius: 999px;
    width: 46px;
    height: 46px;
    animation: spin 0.9s linear infinite;
}

/* light spinner accent */
body.theme-light #loading-overlay .spinner {
    border: 6px solid rgba(255,255,255,0.9);
    border-top: 6px solid var(--orange-light);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ---------- RESPONSIVE ---------- */
@media (max-width: 768px) {
    .products-container {
        padding: 16px 10px 28px;
    }

    .page-title {
        font-size: 22px;
    }

    .search-form {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-select,
    .filter-btn,
    .clear-filters-link {
        width: 100%;
    }

    table {
        min-width: 600px;
    }

    tbody td {
        font-size: 0.8rem;
        padding: 8px 10px;
    }

    .btn-table {
        padding: 4px 8px;
        font-size: 0.7rem;
    }
}
/* ⬇ soften white overlay */
body.theme-light #loading-overlay {
    background: rgba(255,255,255,0.07) !important;
    backdrop-filter: none !important;
}

/* optional: slightly dim spinner contrast */
body.theme-light #loading-overlay .spinner {
    border: 6px solid rgba(255,255,255,0.5) !important;
    border-top: 6px solid #ea580c !important;
}
/* ========== FIX LARAVEL PAGINATION LAYOUT ========== */

/* Tailwind-style <nav> wrapper: center and keep buttons in 1 row */
.pagination-wrapper nav {
    display: flex !important;
    justify-content: center !important;
}

/* Inside Tailwind's nav, the second div contains the buttons */
.pagination-wrapper nav > div {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Hide the "Showing 1 to 10 of X results" block (first div) */
.pagination-wrapper nav > div:first-child {
    display: none !important;
}

/* Bootstrap-style <ul class="pagination"> (if used) */
.pagination-wrapper .pagination {
    display: flex !important;
    justify-content: center !important;
    align-items: center;
    gap: 4px;
    padding-left: 0;
    margin: 0;
}

/* ========== MAKE ALL PAGINATION PILLS SMALLER ========== */

.pagination-wrapper nav a,
.pagination-wrapper nav span,
.pagination-wrapper .pagination .page-link {
    padding: 4px 8px !important;      /* smaller buttons */
    margin: 2px 2px !important;
    font-size: 0.8rem !important;     /* smaller text */
    line-height: 1 !important;
    border-radius: 999px;
}

/* ========== SHRINK ARROW ICONS (SVG) ========== */

.pagination-wrapper svg {
    width: 12px !important;
    height: 12px !important;
}

/* If your arrows are text (like « and »), they will shrink with font-size above */

/* ========== KEEP THEME COLORS BUT USE SMALLER SIZE ========== */

/* dark theme pills */
body.theme-dark .pagination-wrapper nav a,
body.theme-dark .pagination-wrapper nav span,
body.theme-dark .pagination-wrapper .pagination .page-link {
    color: #e5e7eb;
    border: 1px solid rgba(148, 163, 184, 0.65);
    background: rgba(15, 23, 42, 0.95);
    text-decoration: none !important;
}

/* light theme pills */
body.theme-light .pagination-wrapper nav a,
body.theme-light .pagination-wrapper nav span,
body.theme-light .pagination-wrapper .pagination .page-link {
    color: var(--orange-main);
    border: 1px solid rgba(209,213,219,0.9);
    background: rgba(255,255,255,0.95);
    text-decoration: none !important;
}

/* active state */
body.theme-dark .pagination-wrapper nav span[aria-current="page"],
body.theme-dark .pagination-wrapper .page-item.active .page-link {
    background: rgba(37, 99, 235, 1);
    border-color: rgba(37, 99, 235, 1);
    color: #ffffff;
}

body.theme-light .pagination-wrapper nav span[aria-current="page"],
body.theme-light .pagination-wrapper .page-item.active .page-link {
    background: var(--orange-light);
    border-color: var(--orange-light-hover);
    color: #ffffff;
}

/* disabled */
.pagination-wrapper nav span[aria-disabled="true"],
.pagination-wrapper .page-item.disabled .page-link {
    opacity: 0.45;
    cursor: not-allowed;
    transform: none;
}



</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.querySelector('.search-form input[name="search"]');
    const searchForm = document.querySelector('.search-form');
    const loadingOverlay = document.getElementById('loading-overlay');

    if (searchInput && searchForm && loadingOverlay) {
        let lastTime = 0;
        let buffer = "";

        searchInput.focus();

        searchInput.addEventListener("keydown", function(e) {
            const now = Date.now();

            if (now - lastTime < 50) {
                buffer += e.key;
            } else {
                buffer = e.key;
            }

            lastTime = now;

            if (e.key === "Enter") {
                e.preventDefault();
                searchInput.value = buffer.trim();
                buffer = "";

                loadingOverlay.style.display = 'flex';
                searchForm.submit();
            }
        });

        searchForm.addEventListener('submit', function() {
            loadingOverlay.style.display = 'flex';
        });
    }
});
</script>
@endsection
