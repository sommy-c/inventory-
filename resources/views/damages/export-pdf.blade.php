<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Damage / Expiry Report</title>
    <style>
        /* ===== BASE ===== */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 20px;
        }

        h1, h2, h3, h4 {
            margin: 0;
            padding: 0;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        /* ===== FILTER SUMMARY ===== */
        .filters-card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 10px;
            background: #f9fafb;
        }

        .filters-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #374151;
        }

        .filters-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .filters-grid td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .filters-label {
            font-weight: 600;
            color: #4b5563;
            white-space: nowrap;
        }

        /* ===== TABLE ===== */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.report-table thead th {
            border: 1px solid #d1d5db;
            padding: 6px 5px;
            background: #f3f4f6;
            font-size: 10px;
            font-weight: 700;
            text-align: left;
            white-space: nowrap;
        }

        table.report-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 5px 5px;
            font-size: 10px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Zebra rows for readability */
        table.report-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        /* Status pill (simple) */
        .status-pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 9px;
            border: 1px solid #d1d5db;
        }
        .status-open {
            background: #fef9c3;
            border-color: #eab308;
            color: #854d0e;
        }
        .status-pending {
            background: #e0f2fe;
            border-color: #3b82f6;
            color: #1d4ed8;
        }
        .status-resolved {
            background: #dcfce7;
            border-color: #22c55e;
            color: #166534;
        }
        .status-rejected {
            background: #fee2e2;
            border-color: #ef4444;
            color: #b91c1c;
        }

        /* Small footer note */
        .footer-note {
            margin-top: 10px;
            font-size: 9px;
            color: #9ca3af;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <h2>Damage / Expiry Report</h2>
    <div class="subtitle">
        Generated on: {{ now()->format('Y-m-d H:i') }}
    </div>

    {{-- FILTER SUMMARY (only show *exact* filters used) --}}
    <div class="filters-card">
        <div class="filters-title">Applied Filters</div>

        @if(empty($isFiltered))
            <div style="font-size:10px; color:#6b7280;">
                No filters applied (showing all records).
            </div>
        @else
            <table class="filters-grid">
                @if(!empty($type))
                    <tr>
                        <td class="filters-label">Type:</td>
                        <td>{{ ucfirst($type) }}</td>
                    </tr>
                @endif

                @if(!empty($supplier))
                    <tr>
                        <td class="filters-label">Supplier:</td>
                        <td>{{ $supplier }}</td>
                    </tr>
                @endif

                @if(!empty($productName))
                    <tr>
                        <td class="filters-label">Product:</td>
                        <td>{{ $productName }}</td>
                    </tr>
                @endif

                @if(!empty($from) || !empty($to))
                    <tr>
                        <td class="filters-label">Date Range:</td>
                        <td>
                            {{ $from ?: '—' }} → {{ $to ?: '—' }}
                        </td>
                    </tr>
                @endif
            </table>
        @endif
    </div>

    {{-- MAIN TABLE --}}
    <table class="report-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Date &amp; Time</th>
                <th>ID</th>
                <th>Product</th>
                <th>Type</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Remaining</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Reported By</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
        @foreach($damages as $index => $damage)
            @php
                $status = strtolower($damage->status ?? '');
                $statusClass = match($status) {
                    'open'     => 'status-pill status-open',
                    'pending'  => 'status-pill status-pending',
                    'resolved' => 'status-pill status-resolved',
                    'rejected' => 'status-pill status-rejected',
                    default    => 'status-pill',
                };
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $damage->created_at ? $damage->created_at->format('Y-m-d H:i') : '' }}</td>
                <td>{{ $damage->id }}</td>
                <td>{{ optional($damage->product)->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($damage->type) }}</td>
                <td class="text-right">{{ $damage->quantity }}</td>
                <td class="text-right">{{ $damage->remaining }}</td>
                <td>{{ optional($damage->product)->supplier ?? '-' }}</td>
                <td>
                    <span class="{{ $statusClass }}">
                        {{ ucfirst($status ?: 'unknown') }}
                    </span>
                </td>
                <td>{{ optional($damage->user)->name ?? 'N/A' }}</td>
                <td>{{ $damage->note ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="footer-note">
        Total records: {{ $damages->count() }}
    </div>

</body>
</html>
