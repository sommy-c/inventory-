@extends('admin.layout')

@section('title', 'Tax / VAT Settings')

@section('content')
<div class="settings-page">
  

    <div class="page-header">
        <h1>Tax / VAT Settings</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <h2>VAT Configuration</h2>
        <p class="subtitle">Control how VAT is calculated and displayed on receipts and POS.</p>

        <form method="POST" action="{{ url('admin/settings/vat') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>VAT Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100"
                           name="vat_rate"
                           value="{{ old('vat_rate', $vat_rate) }}" required>
                    <span class="help-text">Example: <strong>7.5</strong> for 7.5%.</span>
                    @error('vat_rate') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>VAT Label</label>
                    <input type="text" name="vat_label" value="{{ old('vat_label', $vat_label) }}">
                    <span class="help-text">Shown on receipts, e.g. VAT, GST, Tax.</span>
                    @error('vat_label') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>VAT Calculation</label>
                    <div class="switch-row">
                        <input type="checkbox" id="vat_inclusive"
                               name="vat_inclusive"
                               value="1"
                               {{ old('vat_inclusive', $vat_inclusive) == '1' ? 'checked' : '' }}>
                        <label for="vat_inclusive">Prices are VAT inclusive</label>
                    </div>
                    <span class="help-text">
                        If checked, your selling prices already include VAT. If unchecked, VAT is added on top.
                    </span>
                </div>
            </div>

            <button type="submit" class="btn-primary">Save VAT Settings</button>
        </form>
    </div>
</div>
@endsection
