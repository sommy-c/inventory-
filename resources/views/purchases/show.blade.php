{{-- resources/views/purchases/show.blade.php --}}
@extends('admin.layout')

@section('title', 'Purchase #'.$purchase->id)

@section('content')
<div class="customers-page">
    <div class="card">
        <h2 style="margin-bottom:12px;">Purchase #{{ $purchase->id }}</h2>

        {{-- Simple reuse of data already loaded --}}
        <p><strong>Supplier:</strong> {{ optional($purchase->supplier)->name }}</p>
        <p><strong>Date:</strong> {{ optional($purchase->purchase_date)->format('Y-m-d') }}</p>
        <p><strong>Reference:</strong> {{ $purchase->reference ?? '—' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($purchase->payment_status) }}</p>

        <hr style="margin:14px 0; border-color:rgba(148,163,184,0.4);">

        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Cost Price</th>
                    <th>Line Total</th>
                    <th>Expiry</th>
                </tr>
                </thead>
                <tbody>
                @foreach($purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Deleted product' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->cost_price, 2) }}</td>
                        <td>{{ number_format($item->line_total, 2) }}</td>
                        <td>{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
