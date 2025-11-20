@extends('admin.layout')
@section('title','Edit Product')

@section('content')
<div class="form-container">
    <h3 class="page-title">Edit Product</h3>

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

    <form id="edit-product-form" action="{{ route('admin.products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label for="sku">SKU <span class="required">*</span></label>
                <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required>
            </div>

            <div class="form-group">
                <label for="name">Product Name <span class="required">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="form-group">
                <label for="barcode">Barcode</label>
                <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}" autofocus>
            </div>

            <div class="form-group">
    <label for="category">Category</label>
    <input
        list="categoryList"
        id="category"
        name="category"
        value="{{ old('category', $product->category ?? '') }}"
        placeholder="e.g. Beverages, Snacks, Electronics"
    >

    <datalist id="categoryList">
        @foreach($categories as $cat)
            <option value="{{ $cat }}"></option>
        @endforeach
    </datalist>
</div>

            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" name="brand" id="brand" value="{{ old('brand', $product->brand) }}">
            </div>

            <div class="form-group">
    <label for="supplier">Supplier</label>
    <input
        list="supplierList"
        id="supplier"
        name="supplier"
        value="{{ old('supplier', $product->supplier ?? '') }}"
        placeholder="Type or select supplier"
    >

    <datalist id="supplierList">
        @foreach($suppliers as $supName)
            <option value="{{ $supName }}"></option>
        @endforeach
    </datalist>
</div>


            <div class="form-group">
                <label for="purchase_price">Purchase Price <span class="required">*</span></label>
                <input type="number" name="purchase_price" id="purchase_price"
                       value="{{ old('purchase_price', $product->purchase_price) }}" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="selling_price">Selling Price <span class="required">*</span></label>
                <input type="number" name="selling_price" id="selling_price"
                       value="{{ old('selling_price', $product->selling_price) }}" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" id="quantity"
                       value="{{ old('quantity', $product->quantity) }}" min="0" required>
            </div>

            <div class="form-group">
                <label for="reorder_level">Reorder Level</label>
                <input type="number" name="reorder_level" id="reorder_level"
                       value="{{ old('reorder_level', $product->reorder_level ?? 10) }}" min="0">
            </div>

            <div class="form-group">
                <label for="supply_date">Supply Date</label>
                <input type="date" name="supply_date" id="supply_date"
                       value="{{ old('supply_date', $product->supply_date?->format('Y-m-d')) }}">
            </div>

            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="date" name="expiry_date" id="expiry_date"
                       value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}">
            </div>

            <div class="form-group full-width">
                <label>
                    <input type="checkbox" name="is_suspended" value="1"
                           {{ old('is_suspended', $product->is_suspended) ? 'checked' : '' }}>
                    Suspend Product
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="submit-btn">Update Product</button>
        </div>
    </form>
</div>

<!-- Full Page Loading Overlay -->
<div id="loading-overlay">
    <div class="spinner"></div>
</div>

<style>
.form-container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
    background-color: rgba(255,255,255,0.05);
    backdrop-filter: blur(8px);
    border-radius: 12px;
    color: #fff;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.page-title { font-size: 28px; font-weight: 600; margin-bottom: 20px; text-align:center; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-weight: 600; margin-bottom: 5px; }
.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="date"] { padding: 10px; border-radius: 6px; border: none; outline: none; background: rgba(255,255,255,0.1); color: #fff; }
.form-group input:focus { background: rgba(255,255,255,0.15); }

/* Alerts */
.alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; font-size: 0.95rem; }
.alert-success { background-color: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.5); }
.alert-error { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.5); }

.full-width { grid-column: 1/-1; }
.required { color: #f87171; }
.form-actions { margin-top: 20px; text-align:center; }
.submit-btn { padding: 12px 25px; border:none; border-radius: 8px; background: rgba(37,99,235,0.8); font-weight: 600; cursor: pointer; transition: 0.2s; }
.submit-btn:hover { background: rgba(37,99,235,1); transform: translateY(-1px); }
@media(max-width:768px){ .form-grid { grid-template-columns: 1fr; } }

/* Loading Overlay */
#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
#loading-overlay .spinner {
    border: 6px solid rgba(255,255,255,0.3);
    border-top: 6px solid #2563eb;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const barcodeInput = document.getElementById("barcode");
    const form = document.getElementById("edit-product-form");
    const loadingOverlay = document.getElementById("loading-overlay");

    // Focus on barcode input
    if (barcodeInput) {
        barcodeInput.focus();
        let lastTime = 0;
        let buffer = "";

        barcodeInput.addEventListener("keydown", function (e) {
            const now = Date.now();
            if (now - lastTime < 50) {
                buffer += e.key;
            } else {
                buffer = e.key;
            }
            lastTime = now;

            if (e.key === "Enter") {
                e.preventDefault();
                barcodeInput.value = buffer;
                buffer = "";
            }
        });
    }

    // Show loading overlay on form submit
    if (form && loadingOverlay) {
        form.addEventListener('submit', function() {
            loadingOverlay.style.display = 'flex';
        });
    }
});
</script>
@endsection
