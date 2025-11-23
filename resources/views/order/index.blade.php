@extends('admin.layout')

@section('title', 'Purchase Orders')

@section('content')
<div class="damages-page"><!-- wrapper for styles -->

    {{-- HEADER --}}
    <div class="page-header">
        <h1>Purchase Orders</h1>

        <div class="header-actions">
            <a href="{{ route('admin.create') }}" class="btn-primary">
                + New Order
            </a>


        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FILTER FORM --}}
    <form method="GET"
          action="{{ route('admin.index') }}"
          class="filter-form">

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search by order no. or supplier..." />

        {{-- Status filter --}}
        <select name="status">
            <option value="">All Statuses</option>
            <option value="waiting"  {{ $statusFilt === 'waiting'  ? 'selected' : '' }}>Waiting (Manager)</option>
            <option value="pending"  {{ $statusFilt === 'pending'  ? 'selected' : '' }}>Pending (Approved)</option>
            <option value="supplied" {{ $statusFilt === 'supplied' ? 'selected' : '' }}>Supplied</option>
        </select>

        <button type="submit">
            Filter
        </button>

        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.index') }}" class="clear-link">
                Clear
            </a>
        @endif
    </form>

    {{-- TABLE --}}
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order No.</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th style="text-align:right;">Total</th>
                    <th>Created At</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>

                        <td>
                            <a href="{{ route('admin.show', $order) }}" style="color:#93c5fd;">
                                {{ $order->order_number ?? ('ORD-'.$order->id) }}
                            </a>
                        </td>

                        <td>{{ $order->supplier_name ?? '-' }}</td>

                        <td>
                            @php
                                $statusClass = match($order->status) {
                                    'waiting'  => 'badge-status-pending',
                                    'pending'  => 'badge-status-open',
                                    'supplied' => 'badge-status-resolved',
                                    default    => 'badge-status-open',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>

                        <td style="text-align:right;">
                            {{ number_format($order->total ?? 0, 2) }}
                        </td>

                        <td>
                            {{ $order->created_at?->format('Y-m-d H:i') }}
                        </td>

                        <td style="text-align:right; white-space:nowrap;">

                            {{-- VIEW --}}
                            <a href="{{ route('admin.show', $order) }}"
                               class="btn-sm-action green">
                                View
                            </a>

                            @php
                                $user      = auth()->user();
                                $isAdmin   = $user && $user->hasRole('admin');
                                $isManager = $user && $user->hasRole('manager');
                            @endphp

                            {{-- ADMIN APPROVE / SUPPLIED BUTTONS --}}
                            @if($isAdmin)
                                @if($order->status === 'waiting')
                                    <form action="{{ route('admin.approve', $order) }}"
                                          method="POST"
                                          style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn-approve btn-sm-action">
                                            Approve
                                        </button>
                                    </form>
                                @endif

                                @if($order->status === 'pending')
                                    <form action="{{ route('admin.orders.supplied', $order) }}"
                                          method="POST"
                                          style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn-resolve btn-sm-action">
                                            Mark Supplied
                                        </button>
                                    </form>
                                @endif
                            @endif

                            {{-- DELETE:
                                 - Admin can delete anytime
                                 - Manager only if status = waiting
                            --}}
                            @php
                                $canManagerDelete = $isManager && $order->status === 'waiting';
                                $canAdminDelete   = $isAdmin;
                            @endphp

                            @if($canManagerDelete || $canAdminDelete)
                                <form method="POST"
                                      action="{{ route('admin.destroy', $order) }}"
                                      style="display:inline-block;"
                                      onsubmit="return confirm('Are you sure you want to delete this order?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-reject btn-sm-action red">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:14px; color:#9ca3af;">
                            No orders found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="pagination-wrapper">
        {{ $orders->withQueryString()->links() }}
    </div>

</div>

{{-- LOCAL STYLES --}}
<style>
    :root {
        --orange-main: #c05621;
        --orange-strong: #9a3412;
        --orange-light: #f97316;
        --orange-light-hover: #ea580c;
        --border-soft: rgba(192,132,45,0.35);
        --muted-text: #7c2d12;
    }

    .damages-page {
        padding: 20px;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Dark vs Light base text */
    body.theme-dark .damages-page {
        color: #e5e7eb;
    }
    body.theme-light .damages-page {
        color: var(--orange-main);
    }

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

    .damages-page .btn-primary,
    .damages-page .btn-secondary {
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

    /* Buttons – dark */
    body.theme-dark .damages-page .btn-primary {
        background: rgba(37, 99, 235, 0.9);
        color: #f9fafb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }
    body.theme-dark .damages-page .btn-primary:hover {
        background: rgba(37, 99, 235, 1);
        transform: translateY(-1px);
    }
    body.theme-dark .damages-page .btn-secondary {
        background: rgba(15, 23, 42, 0.9);
        color: #e5e7eb;
        border: 1px solid rgba(148, 163, 184, 0.6);
    }
    body.theme-dark .damages-page .btn-secondary:hover {
        background: rgba(30, 64, 175, 0.9);
        border-color: rgba(129, 140, 248, 0.9);
    }

    /* Buttons – light */
    body.theme-light .damages-page .btn-primary {
        background: var(--orange-light);
        color: #fff;
        box-shadow: 0 4px 12px rgba(248, 148, 6, 0.3);
    }
    body.theme-light .damages-page .btn-primary:hover {
        background: var(--orange-light-hover);
        transform: translateY(-1px);
    }
    body.theme-light .damages-page .btn-secondary {
        background: #ffffff;
        color: var(--orange-main);
        border: 1px solid rgba(209,213,219,0.9);
    }
    body.theme-light .damages-page .btn-secondary:hover {
        background: #fef3c7;
        border-color: var(--orange-light);
    }

    /* Alerts */
    .damages-page .alert {
        padding: 10px 12px;
        border-radius: 8px;
        margin-bottom: 14px;
        font-size: 0.9rem;
    }

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

    body.theme-light .damages-page .alert-success {
        background: rgba(22,163,74,0.08);
        border: 1px solid rgba(22,163,74,0.6);
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

    /* Filter form */
    .damages-page .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
        padding: 10px 12px;
        border-radius: 10px;
    }

    body.theme-dark .damages-page .filter-form {
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.4);
    }
    body.theme-light .damages-page .filter-form {
        background: rgba(255,255,255,0.95);
        border: 1px solid var(--border-soft);
        box-shadow: 0 10px 24px rgba(0,0,0,0.06);
    }

    .damages-page .filter-form input[type="text"],
    .damages-page .filter-form select {
        padding: 6px 8px;
        border-radius: 8px;
        font-size: 0.85rem;
        min-width: 150px;
        border-width: 1px;
        border-style: solid;
        outline: none;
    }

    body.theme-dark .damages-page .filter-form input[type="text"],
    body.theme-dark .damages-page .filter-form select {
        background: #020617;
        border-color: rgba(148, 163, 184, 0.7);
        color: #e5e7eb;
    }

    body.theme-light .damages-page .filter-form input[type="text"],
    body.theme-light .damages-page .filter-form select {
        background: rgba(255,255,255,0.98);
        border-color: rgba(209,213,219,0.9);
        color: var(--orange-main);
    }

    .damages-page .filter-form button[type="submit"] {
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
    }

    body.theme-dark .damages-page .filter-form button[type="submit"] {
        background: rgba(37, 99, 235, 0.95);
        color: #f9fafb;
    }
    body.theme-light .damages-page .filter-form button[type="submit"] {
        background: var(--orange-light);
        color: #fff;
    }

    .damages-page .clear-link {
        align-self: center;
        font-size: 0.8rem;
        text-decoration: underline;
    }
    body.theme-dark .damages-page .clear-link {
        color: #9ca3af;
    }
    body.theme-light .damages-page .clear-link {
        color: var(--muted-text);
    }

    /* Table wrapper */
    .damages-page .table-wrapper {
        overflow-x: auto;
        border-radius: 12px;
        margin-top: 12px;
        border-width: 1px;
        border-style: solid;
    }

    body.theme-dark .damages-page .table-wrapper {
        background: rgba(15, 23, 42, 0.95);
        border-color: rgba(148, 163, 184, 0.4);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.9);
    }
    body.theme-light .damages-page .table-wrapper {
        background: rgba(255,255,255,0.96);
        border-color: var(--border-soft);
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    }

    .damages-page .table-wrapper table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
        font-size: 0.85rem;
    }

    /* Thead */
    body.theme-dark .damages-page .table-wrapper thead {
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

    /* Body rows */
    .damages-page .table-wrapper tbody tr {
        transition: background 0.15s ease;
    }

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
        background: rgba(255,255,255,0.93);
    }
    body.theme-light .damages-page .table-wrapper tbody tr:hover {
        background: rgba(254,243,199,0.85);
    }

    .damages-page .table-wrapper tbody td {
        padding: 8px 10px;
    }
    body.theme-dark .damages-page .table-wrapper tbody td {
        color: #e5e7eb;
    }
    body.theme-light .damages-page .table-wrapper tbody td {
        color: var(--orange-main);
    }

    /* Status badges */
    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-width: 1px;
        border-style: solid;
    }

    /* Waiting */
    body.theme-dark .badge-status-pending {
        background: rgba(234, 179, 8, 0.12);
        color: #fef9c3;
        border-color: rgba(234, 179, 8, 0.7);
    }
    body.theme-light .badge-status-pending {
        background: rgba(250,204,21,0.1);
        color: #854d0e;
        border-color: rgba(250,204,21,0.8);
    }

    /* Pending / Open */
    body.theme-dark .badge-status-open {
        background: rgba(59, 130, 246, 0.15);
        color: #bfdbfe;
        border-color: rgba(59, 130, 246, 0.8);
    }
    body.theme-light .badge-status-open {
        background: rgba(59,130,246,0.1);
        color: #1d4ed8;
        border-color: rgba(59,130,246,0.8);
    }

    /* Supplied / Resolved */
    body.theme-dark .badge-status-resolved {
        background: rgba(22, 163, 74, 0.15);
        color: #bbf7d0;
        border-color: rgba(22, 163, 74, 0.8);
    }
    body.theme-light .badge-status-resolved {
        background: rgba(22,163,74,0.1);
        color: #166534;
        border-color: rgba(22,163,74,0.8);
    }

    /* Small action buttons */
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

    .btn-approve {
        background: rgba(34, 197, 94, 0.9);
        color: #ecfdf5;
        border-radius: 8px;
    }
    .btn-approve:hover {
        background: rgba(22, 163, 74, 1);
    }

    .btn-reject {
        background: rgba(239, 68, 68, 0.9);
        color: #fee2e2;
        border-radius: 8px;
    }
    .btn-reject:hover {
        background: rgba(220, 38, 38, 1);
    }

    .btn-resolve {
        background: rgba(16, 185, 129, 0.9);
        color: white;
        border-radius: 8px;
    }
    .btn-resolve:hover {
        background: rgba(5, 150, 105, 1);
    }

    /* Pagination (compact, centered) */
    .pagination-wrapper {
        margin-top: 16px;
    }

    .pagination-wrapper nav {
        display: flex !important;
        justify-content: center !important;
    }

    .pagination-wrapper nav > div {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Hide "showing X to Y of Z" */
    .pagination-wrapper nav > div:first-child {
        display: none !important;
    }

    .pagination-wrapper .pagination {
        display: flex !important;
        justify-content: center !important;
        align-items: center;
        gap: 4px;
        padding-left: 0;
        margin: 0;
    }

    .pagination-wrapper nav a,
    .pagination-wrapper nav span,
    .pagination-wrapper .pagination .page-link {
        padding: 4px 8px !important;
        margin: 2px 2px !important;
        font-size: 0.8rem !important;
        line-height: 1 !important;
        border-radius: 999px;
        text-decoration: none !important;
        border-width: 1px;
        border-style: solid;
    }

    /* Dark pagination pills */
    body.theme-dark .pagination-wrapper nav a,
    body.theme-dark .pagination-wrapper nav span,
    body.theme-dark .pagination-wrapper .page-link {
        color: #e5e7eb;
        border-color: rgba(148,163,184,0.65);
        background: rgba(15,23,42,0.95);
    }

    /* Light pagination pills */
    body.theme-light .pagination-wrapper nav a,
    body.theme-light .pagination-wrapper nav span,
    body.theme-light .pagination-wrapper .page-link {
        color: var(--orange-main);
        border-color: rgba(209,213,219,0.9);
        background: rgba(255,255,255,0.95);
    }

    /* Active */
    body.theme-dark .pagination-wrapper nav span[aria-current="page"],
    body.theme-dark .pagination-wrapper .page-item.active .page-link {
        background: rgba(37,99,235,1);
        border-color: rgba(37,99,235,1);
        color: #ffffff;
    }

    body.theme-light .pagination-wrapper nav span[aria-current="page"],
    body.theme-light .pagination-wrapper .page-item.active .page-link {
        background: var(--orange-light);
        border-color: var(--orange-light-hover);
        color: #ffffff;
    }

    /* Hover */
    body.theme-dark .pagination-wrapper nav a:hover,
    body.theme-dark .pagination-wrapper .page-link:hover {
        background: rgba(37,99,235,0.9);
        border-color: rgba(37,99,235,1);
        transform: translateY(-1px);
    }

    body.theme-light .pagination-wrapper nav a:hover,
    body.theme-light .pagination-wrapper .page-link:hover {
        background: rgba(254,243,199,0.95);
        border-color: var(--orange-light);
        transform: translateY(-1px);
    }

    /* Disabled */
    .pagination-wrapper nav span[aria-disabled="true"],
    .pagination-wrapper .page-item.disabled .page-link {
        opacity: 0.45;
        cursor: not-allowed;
        transform: none;
    }

    /* Shrink arrow svgs */
    .pagination-wrapper svg {
        width: 12px !important;
        height: 12px !important;
    }

    @media (max-width: 640px) {
        .damages-page .filter-form {
            flex-direction: column;
        }
        .damages-page .filter-form input[type="text"],
        .damages-page .filter-form select,
        .damages-page .filter-form button {
            width: 100%;
        }
        .damages-page .table-wrapper table {
            min-width: 700px;
        }
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .damages-page, .damages-page * {
            visibility: visible;
        }
        .damages-page {
            margin: 0;
            padding: 0;
        }
        .btn-primary,
        .btn-secondary,
        .btn-approve,
        .btn-reject,
        .btn-resolve,
        .btn-sm-action,
        .filter-form,
        .pagination-wrapper {
            display: none !important;
        }
    }
</style>
@endsection
