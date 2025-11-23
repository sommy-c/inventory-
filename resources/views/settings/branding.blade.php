@extends('admin.layout')

@section('title', 'Logo & Branding')

@section('content')
<style>
    :root {
        --orange-main: #c05621;
        --orange-strong: #9a3412;
        --orange-light: #f97316;
        --orange-light-hover: #ea580c;
        --border-soft: rgba(192,132,45,0.35);
    }

    .settings-page {
        padding: 20px;
        font-family: "Segoe UI", sans-serif;
    }

    /* Text color per theme */
    body.theme-dark .settings-page {
        color: #e5e7eb;
    }
    body.theme-light .settings-page {
        color: var(--orange-main);
    }

    .settings-page h1 {
        font-size: 26px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    body.theme-light .settings-page h1 {
        color: var(--orange-strong);
    }

    /* Card */
    .settings-card {
        border-radius: 14px;
        padding: 20px;
        max-width: 650px;
        margin-bottom: 20px;
        border: 1px solid;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.4);
    }

    body.theme-dark .settings-card {
        background: rgba(15, 23, 42, 0.95);
        border-color: rgba(148, 163, 184, 0.3);
    }
    body.theme-light .settings-card {
        background: rgba(255,255,255,0.96);
        border-color: var(--border-soft);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.18);
    }

    .settings-card h2 {
        font-size: 20px;
        margin-bottom: 12px;
    }
    body.theme-dark .settings-card h2 {
        color: #f9fafb;
    }
    body.theme-light .settings-card h2 {
        color: var(--orange-strong);
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        font-size: 14px;
        margin-bottom: 6px;
        display: block;
    }
    body.theme-dark .form-group label {
        color: #cbd5e1;
    }
    body.theme-light .form-group label {
        color: var(--orange-strong);
    }

    /* File input theme-aware */
    .settings-page input[type="file"] {
        border-radius: 8px;
        padding: 10px;
        width: 100%;
        border: 1px solid;
        font-size: 14px;
        outline: none;
    }

    body.theme-dark .settings-page input[type="file"] {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }
    body.theme-light .settings-page input[type="file"] {
        background: #ffffff;
        border-color: rgba(209,213,219,0.9);
        color: var(--orange-main);
    }

    .settings-page input[type="file"]:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.8);
    }

    .preview-image {
        margin-top: 10px;
        max-height: 80px;
        display: block;
        border-radius: 6px;
        border: 1px solid;
        padding: 4px;
    }

    body.theme-dark .preview-image {
        background: #020617;
        border-color: rgba(148, 163, 184, 0.4);
    }
    body.theme-light .preview-image {
        background: #ffffff;
        border-color: rgba(209,213,219,0.9);
    }

    /* Save button */
    .btn-save {
        border: none;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    body.theme-dark .btn-save {
        background: rgba(34, 197, 94, 0.9);
        color: #ecfdf5;
    }
    body.theme-dark .btn-save:hover {
        background: rgba(22, 163, 74, 1);
        transform: translateY(-1px);
    }

    body.theme-light .btn-save {
        background: var(--orange-light);
        color: #fff7ed;
        box-shadow: 0 4px 10px rgba(248,148,6,0.3);
    }
    body.theme-light .btn-save:hover {
        background: var(--orange-light-hover);
        transform: translateY(-1px);
    }

    /* Alerts theme-aware */
    .alert-success,
    .alert-error {
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 15px;
        font-size: 0.9rem;
        border: 1px solid;
    }

    body.theme-dark .alert-success {
        background: rgba(22, 163, 74, 0.2);
        border-color: rgba(22, 163, 74, 0.7);
        color: #bbf7d0;
    }
    body.theme-light .alert-success {
        background: rgba(22,163,74,0.08);
        border-color: rgba(22,163,74,0.7);
        color: #166534;
    }

    body.theme-dark .alert-error {
        background: rgba(220, 38, 38, 0.2);
        border-color: rgba(220, 38, 38, 0.7);
        color: #fecaca;
    }
    body.theme-light .alert-error {
        background: rgba(248,113,113,0.08);
        border-color: rgba(248,113,113,0.7);
        color: #b91c1c;
    }
</style>

<div class="settings-page">

    <h1>Logo & Branding</h1>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert-error">
            <ul style="margin:0; padding-left: 18px;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="settings-card">
        <h2>Upload Logo & Favicon</h2>

        <form action="{{ url('admin/settings/branding') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Logo --}}
            <div class="form-group">
                <label>Store Logo (Recommended: 200×200 PNG)</label>
                <input type="file" name="logo" accept="image/*">

                @if($logo_path)
                    <img src="{{ asset('storage/'.$logo_path) }}" class="preview-image" alt="Logo">
                @endif
            </div>

            {{-- Favicon --}}
            <div class="form-group">
                <label>Favicon (Recommended: 32×32 PNG)</label>
                <input type="file" name="favicon" accept="image/*">

                @if($favicon_path)
                    <img src="{{ asset('storage/'.$favicon_path) }}" class="preview-image" alt="Favicon">
                @endif
            </div>

            <button class="btn-save">Save Branding</button>
        </form>
    </div>
</div>

@endsection
