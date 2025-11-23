@extends('admin.layout')

@section('title', 'Receipt / POS Settings')

@section('content')
<div class="settings-page">
  

    <div class="page-header">
        <h1>Receipt &amp; POS Settings</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <h2>Receipt Layout</h2>
        <p class="subtitle">Customize how receipts appear in POS and print view.</p>

        <form method="POST" action="{{ url('admin/settings/receipt') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Receipt Footer Text</label>
                    <textarea name="receipt_footer">{{ old('receipt_footer', $receipt_footer) }}</textarea>
                    <span class="help-text">Appears at the bottom of printed receipts.</span>
                    @error('receipt_footer') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Show VAT Line on Receipt</label>
                    <div class="switch-row">
                        <input type="checkbox" id="show_vat_on_receipt"
                               name="show_vat_on_receipt"
                               value="1"
                               {{ old('show_vat_on_receipt', $show_vat_on_receipt) == '1' ? 'checked' : '' }}>
                        <label for="show_vat_on_receipt">Display VAT total separately on receipts.</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Show Customer Info</label>
                    <div class="switch-row">
                        <input type="checkbox" id="show_customer_on_receipt"
                               name="show_customer_on_receipt"
                               value="1"
                               {{ old('show_customer_on_receipt', $show_customer_on_receipt) == '1' ? 'checked' : '' }}>
                        <label for="show_customer_on_receipt">Show customer name / contact on receipt.</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Enable WhatsApp Receipt (Future Feature)</label>
                    <div class="switch-row">
                        <input type="checkbox" id="enable_whatsapp_receipt"
                               name="enable_whatsapp_receipt"
                               value="1"
                               {{ old('enable_whatsapp_receipt', $enable_whatsapp_receipt) == '1' ? 'checked' : '' }}>
                        <label for="enable_whatsapp_receipt">Allow sending receipt link via WhatsApp (UI only for now).</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">Save Receipt Settings</button>
        </form>
    </div>
</div>
@endsection
