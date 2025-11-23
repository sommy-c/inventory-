@extends('admin.layout')

@section('title', 'Notification Settings')

@section('content')
<div class="settings-page">
   

    <div class="page-header">
        <h1>Notifications</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <h2>Alerts &amp; Emails</h2>
        <p class="subtitle">Set up automatic alerts for low stock and daily summaries.</p>

        <form method="POST" action="{{ url('admin/settings/notifications') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Low Stock Threshold</label>
                    <input type="number" min="1" name="low_stock_threshold"
                           value="{{ old('low_stock_threshold', $low_stock_threshold) }}" required>
                    <span class="help-text">
                        When product quantity is below this number, it is considered low stock.
                    </span>
                    @error('low_stock_threshold') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Admin Email for Alerts</label>
                    <input type="email" name="notify_admin_email"
                           value="{{ old('notify_admin_email', $notify_admin_email) }}">
                    <span class="help-text">Where low stock / summary notifications will be sent.</span>
                    @error('notify_admin_email') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Low Stock Notifications</label>
                    <div class="switch-row">
                        <input type="checkbox" id="notify_on_low_stock"
                               name="notify_on_low_stock"
                               value="1"
                               {{ old('notify_on_low_stock', $notify_on_low_stock) == '1' ? 'checked' : '' }}>
                        <label for="notify_on_low_stock">Enable low stock alerts.</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Daily Summary Email</label>
                    <div class="switch-row">
                        <input type="checkbox" id="notify_on_daily_summary"
                               name="notify_on_daily_summary"
                               value="1"
                               {{ old('notify_on_daily_summary', $notify_on_daily_summary) == '1' ? 'checked' : '' }}>
                        <label for="notify_on_daily_summary">Send daily sales summary email.</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">Save Notification Settings</button>
        </form>
    </div>
</div>
@endsection
