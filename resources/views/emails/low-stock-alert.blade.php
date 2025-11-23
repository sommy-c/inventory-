<h2>Low Stock Alert</h2>

<p>The following products are below the threshold ({{ $threshold }}):</p>

<ul>
@foreach($products as $product)
    <li>{{ $product->name }} — Qty: {{ $product->quantity }}</li>
@endforeach
</ul>

<p>Please restock soon.</p>
