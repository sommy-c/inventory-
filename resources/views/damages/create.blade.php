@extends('admin.layout')

@section('title', 'Log Damage / Expired')

@section('content')
<div class="damages-page">
    <div class="page-header">
        <h1>Log Damage / Expired Stock</h1>
        <a href="{{ route('admin.damages.index') }}" class="btn-secondary">← Back to list</a>
    </div>

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

    <div class="card-form">
        <form action="{{ route('admin.damages.store') }}" method="POST">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Product <span class="required">*</span></label>
                    <select name="product_id" required>
                        <option value="">-- Select product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->sku }}) - Stock: {{ $p->quantity }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Type <span class="required">*</span></label>
                    <select name="type" id="damageType" required>
                        <option value="damaged" {{ old('type') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="expired" {{ old('type') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Quantity <span class="required">*</span></label>
                    <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required>
                </div>

                <div class="form-group" id="expiryWrapper">
                    <label>Expiry Date <span class="required expiry-required" style="display:none;">*</span></label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}">
                    <small class="hint">Required for expired goods.</small>
                </div>

                <div class="form-group full-width">
                    <label>Note / Reason</label>
                    <textarea name="note" rows="3" placeholder="E.g. bottles broken in transit, expired on shelf...">{{ old('note') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect   = document.getElementById('damageType');
    const expiryWrap   = document.getElementById('expiryWrapper');
    const expiryInput  = expiryWrap ? expiryWrap.querySelector('input[name="expiry_date"]') : null;
    const expiryStar   = expiryWrap ? expiryWrap.querySelector('.expiry-required') : null;

    function toggleExpiry() {
        if (!typeSelect || !expiryInput) return;
        if (typeSelect.value === 'expired') {
            if (expiryStar) expiryStar.style.display = 'inline';
            expiryInput.required = true;
        } else {
            if (expiryStar) expiryStar.style.display = 'none';
            expiryInput.required = false;
        }
    }

    toggleExpiry();
    if (typeSelect) {
        typeSelect.addEventListener('change', toggleExpiry);
    }
});
</script>

<style>
:root {
    --orange-main: #c05621;
    --orange-strong: #9a3412;
    --orange-light: #f97316;
    --orange-light-hover: #ea580c;
    --border-soft: rgba(192,132,45,0.35);
}

/* PAGE */
.damages-page {
    padding:20px;
    min-height:100vh;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* text color per theme */
body.theme-dark .damages-page {
    color:#e5e7eb;
}
body.theme-light .damages-page {
    color:var(--orange-main);
}

/* HEADER */
.page-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:16px;
    gap:10px;
    flex-wrap:wrap;
}
.page-header h1 {
    font-size:24px;
    font-weight:600;
    margin:0;
}
body.theme-dark .page-header h1 {
    color:#f9fafb;
}
body.theme-light .page-header h1 {
    color:var(--orange-strong);
}

/* CARD */
.card-form {
    border-radius:12px;
    border:1px solid;
    padding:18px;
    max-width:800px;
    box-shadow:0 10px 28px rgba(15,23,42,0.85);
}
body.theme-dark .card-form {
    background:rgba(15,23,42,0.95);
    border-color:rgba(55,65,81,0.85);
}
body.theme-light .card-form {
    background:rgba(255,255,255,0.98);
    border-color:var(--border-soft);
    box-shadow:0 10px 24px rgba(15,23,42,0.15);
}

/* GRID */
.form-grid {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap:14px;
}
.form-group {
    display:flex;
    flex-direction:column;
    gap:4px;
}
.form-group label {
    font-size:0.9rem;
    font-weight:500;
}
body.theme-light .form-group label {
    color:var(--orange-strong);
}

/* INPUTS */
.form-group input,
.form-group select,
.form-group textarea {
    border-radius:8px;
    padding:8px 10px;
    font-size:0.9rem;
    border:1px solid;
}

/* dark inputs */
body.theme-dark .form-group input,
body.theme-dark .form-group select,
body.theme-dark .form-group textarea {
    background:#020617;
    border-color:#4b5563;
    color:#e5e7eb;
}

/* light inputs */
body.theme-light .form-group input,
body.theme-light .form-group select,
body.theme-light .form-group textarea {
    background:#ffffff;
    border-color:rgba(209,213,219,0.9);
    color:var(--orange-main);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color:#9ca3af;
}

.form-group textarea {
    resize:vertical;
}
.full-width {
    grid-column:1 / -1;
}

/* required star */
.required {
    color:#f97373;
}

/* hint text */
.hint {
    font-size:0.75rem;
}
body.theme-dark .hint {
    color:#9ca3af;
}
body.theme-light .hint {
    color:#b45309;
}

/* BUTTONS */
.btn-primary,
.btn-secondary {
    display:inline-block;
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    font-size:0.9rem;
    font-weight:600;
    border:none;
    cursor:pointer;
    transition:background 0.18s ease, transform 0.12s ease, box-shadow 0.15s ease;
}

/* primary */
body.theme-dark .btn-primary {
    background:rgba(37,99,235,0.95);
    color:#fff;
    box-shadow:0 4px 12px rgba(37,99,235,0.35);
}
body.theme-dark .btn-primary:hover {
    background:rgba(37,99,235,1);
    transform:translateY(-1px);
}
body.theme-light .btn-primary {
    background:var(--orange-light);
    color:#fff7ed;
    box-shadow:0 4px 10px rgba(248,148,6,0.3);
}
body.theme-light .btn-primary:hover {
    background:var(--orange-light-hover);
    transform:translateY(-1px);
}

/* secondary */
body.theme-dark .btn-secondary {
    background:rgba(31,41,55,0.9);
    color:#e5e7eb;
    border:1px solid rgba(148,163,184,0.75);
}
body.theme-dark .btn-secondary:hover {
    background:rgba(55,65,81,1);
}
body.theme-light .btn-secondary {
    background:#ffffff;
    color:var(--orange-main);
    border:1px solid rgba(209,213,219,0.9);
}
body.theme-light .btn-secondary:hover {
    background:#fffbeb;
    border-color:var(--orange-light);
}

/* FORM ACTIONS */
.form-actions {
    margin-top:16px;
    text-align:right;
}

/* ALERTS */
.alert {
    padding:10px 12px;
    border-radius:8px;
    margin-bottom:10px;
    font-size:0.9rem;
}

/* dark alerts */
body.theme-dark .alert-success {
    background:rgba(16,185,129,0.18);
    border:1px solid rgba(16,185,129,0.7);
    color:#6ee7b7;
}
body.theme-dark .alert-error {
    background:rgba(248,113,113,0.18);
    border:1px solid rgba(248,113,113,0.7);
    color:#fecaca;
}

/* light alerts */
body.theme-light .alert-success {
    background:rgba(22,163,74,0.08);
    border:1px solid rgba(22,163,74,0.7);
    color:#166534;
}
body.theme-light .alert-error {
    background:rgba(248,113,113,0.08);
    border:1px solid rgba(248,113,113,0.7);
    color:#b91c1c;
}

@media (max-width:768px) {
    .card-form {
        padding:14px;
    }
}
</style>
@endsection
