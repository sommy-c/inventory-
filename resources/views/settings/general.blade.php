@extends('admin.layout')

@section('title', 'General Settings')

@section('content')
<div class="settings-page">
   

    <div class="page-header">
        <h1>General Settings</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <h2>Store Information</h2>
        <p class="subtitle">Basic details used across your receipts, POS and reports.</p>

        <form method="POST" action="{{ url('admin/settings/general') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Store Name</label>
                    <input type="text" name="store_name" value="{{ old('store_name', $store_name) }}" required>
                    @error('store_name') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Store Email</label>
                    <input type="email" name="store_email" value="{{ old('store_email', $store_email) }}">
                    @error('store_email') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Store Phone</label>
                    <input type="text" name="store_phone" value="{{ old('store_phone', $store_phone) }}">
                    @error('store_phone') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Store Address</label>
                    <textarea name="store_address">{{ old('store_address', $store_address) }}</textarea>
                    @error('store_address') <span class="help-text" style="color:#f97373;">{{ $message }}</span> @enderror
                </div>
            </div>

            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </div>
</div>
@endsection
