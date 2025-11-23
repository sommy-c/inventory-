@extends('admin.layout')

@section('title', 'Currency & Exchange Settings')

@section('content')
<div class="settings-page">
  

    <div class="page-header">
        <h1>Currency &amp; Exchange</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <h2>Currency Configuration</h2>
        <p class="subtitle">Configure how monetary amounts are displayed and exchanged.</p>

        <form method="POST" action="{{ url('admin/settings/currency-exchange') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Currency Code</label>
                    <input type="text" name="currency_code"
                           value="{{ old('currency_code', $currency_code) }}" required>
                    <span class="help-text">Example: NGN, USD, GHS.</span>
                    @error('currency_code') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Currency Symbol</label>
                    <input type="text" name="currency_symbol"
                           value="{{ old('currency_symbol', $currency_symbol) }}" required>
                    <span class="help-text">Example: ₦, $, GH₵.</span>
                    @error('currency_symbol') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Exchange Rate (Base → Local)</label>
                    <input type="number" step="0.0001" min="0.0001"
                           name="exchange_rate"
                           value="{{ old('exchange_rate', $exchange_rate) }}" required>
                    <span class="help-text">
                        For most single-currency setups, keep this as <strong>1</strong>.
                    </span>
                    @error('exchange_rate') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Show Currency Code with Amount</label>
                    <div class="switch-row">
                        <input type="checkbox" id="show_currency_code"
                               name="show_currency_code"
                               value="1"
                               {{ old('show_currency_code', $show_currency_code) == '1' ? 'checked' : '' }}>
                        <label for="show_currency_code">Display code (e.g. NGN 10,000) on receipts & reports</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">Save Currency Settings</button>
        </form>
    </div>
</div>
@endsection
