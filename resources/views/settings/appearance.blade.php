@extends('admin.layout')

@section('title', 'Theme & Appearance')

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

    /* --------- TEXT COLORS PER THEME --------- */
    body.theme-dark .settings-page {
        color: #e5e7eb;
    }
    body.theme-light .settings-page {
        color: var(--orange-main);
    }

    /* --------- CARD --------- */
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

    .settings-page h1 {
        font-size: 26px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    body.theme-light .settings-page h1 {
        color: var(--orange-strong);
    }

    /* --------- FORM --------- */
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

    .radio-group,
    .inline-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .radio-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        cursor: pointer;
        font-size: 13px;
        transition: background 0.15s ease, border-color 0.15s ease;
        border: 1px solid;
    }

    body.theme-dark .radio-pill {
        border-color: #334155;
        background: #020617;
        color: #e5e7eb;
    }
    body.theme-light .radio-pill {
        border-color: rgba(209,213,219,0.9);
        background: #fff7ed;
        color: var(--orange-main);
    }

    .radio-pill input {
        accent-color: #3b82f6;
    }

    body.theme-dark .radio-pill.active {
        border-color: #3b82f6;
        background: rgba(37, 99, 235, 0.25);
    }
    body.theme-light .radio-pill.active {
        border-color: var(--orange-light);
        background: rgba(254,243,199,0.95);
    }

    input[type="color"],
    .settings-page input[type="text"],
    .settings-page select {
        border-radius: 8px;
        padding: 8px 10px;
        width: 100%;
        font-size: 14px;
        border: 1px solid;
        outline: none;
    }

    body.theme-dark input[type="color"],
    body.theme-dark .settings-page input[type="text"],
    body.theme-dark .settings-page select {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }

    body.theme-light input[type="color"],
    body.theme-light .settings-page input[type="text"],
    body.theme-light .settings-page select {
        background: #fff;
        border-color: rgba(209,213,219,0.9);
        color: var(--orange-main);
    }

    input[type="color"] {
        padding: 0;
        height: 38px;
        cursor: pointer;
        max-width: 70px;
        min-width: 60px;
    }

    input[type="color"]:focus,
    .settings-page input[type="text"]:focus,
    .settings-page select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.8);
    }

    .hint {
        font-size: 12px;
        margin-top: 4px;
    }
    body.theme-dark .hint {
        color: #9ca3af;
    }
    body.theme-light .hint {
        color: #9a3412;
    }

    /* --------- SAVE BUTTON --------- */
    .btn-save {
        border: none;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    body.theme-dark .btn-save {
        background: rgba(37, 99, 235, 0.95);
        color: #eff6ff;
    }
    body.theme-dark .btn-save:hover {
        background: rgba(30, 64, 175, 1);
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

    /* --------- ALERTS --------- */
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

    <h1>Theme & Appearance</h1>

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
        <h2>Layout & Theme</h2>

        <form action="{{ url('admin/settings/appearance') }}" method="POST">
            @csrf

            {{-- Theme mode --}}
            <div class="form-group">
                <label>Theme Mode</label>
                <div class="radio-group">
                    @php $mode = old('theme_mode', $theme_mode ?? 'dark'); @endphp

                    <label class="radio-pill {{ $mode === 'dark' ? 'active' : '' }}">
                        <input type="radio" name="theme_mode" value="dark"
                               {{ $mode === 'dark' ? 'checked' : '' }}>
                        <span>Dark</span>
                    </label>

                    <label class="radio-pill {{ $mode === 'light' ? 'active' : '' }}">
                        <input type="radio" name="theme_mode" value="light"
                               {{ $mode === 'light' ? 'checked' : '' }}>
                        <span>Light</span>
                    </label>
                </div>
                <div class="hint">Controls overall tone of the dashboard (sidebar, topbar, etc.).</div>
            </div>

            {{-- Primary color (color picker + text, only text is submitted) --}}
            <div class="form-group">
                <label>Primary Color</label>
                @php
                    $color = old('primary_color', $primary_color ?? '#2563eb');
                @endphp
                <div class="inline-group">
                    {{-- Visual picker (no name, just helper) --}}
                    <input type="color"
                           id="primary_color_picker"
                           value="{{ $color }}">

                    {{-- Actual value sent to backend --}}
                    <input type="text"
                           id="primary_color_input"
                           name="primary_color"
                           value="{{ $color }}"
                           placeholder="#2563eb">
                </div>
                <div class="hint">Used for highlights, buttons, and the sidebar background.</div>
            </div>

            {{-- Sidebar style --}}
            <div class="form-group">
                <label>Sidebar Style</label>
                @php $style = old('sidebar_style', $sidebar_style ?? 'compact'); @endphp
                <select name="sidebar_style">
                    <option value="compact" {{ $style === 'compact' ? 'selected' : '' }}>Compact (narrow)</option>
                    <option value="full" {{ $style === 'full' ? 'selected' : '' }}>Full (wider)</option>
                </select>
                <div class="hint">Controls the width of the left sidebar.</div>
            </div>

            <button class="btn-save">Save Appearance</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const picker = document.getElementById('primary_color_picker');
        const input  = document.getElementById('primary_color_input');

        if (picker && input) {
            // picker -> text
            picker.addEventListener('input', function () {
                input.value = picker.value;
            });

            // text -> picker (when HEX is valid)
            input.addEventListener('input', function () {
                const val = input.value.trim();
                const hexMatch = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/;
                if (hexMatch.test(val)) {
                    picker.value = val;
                }
            });
        }

        // Toggle active state on theme radio pills
        document.querySelectorAll('.radio-pill input[name="theme_mode"]').forEach(r => {
            r.addEventListener('change', function () {
                document.querySelectorAll('.radio-pill').forEach(p => p.classList.remove('active'));
                this.closest('.radio-pill').classList.add('active');
            });
        });
    });
</script>
@endsection
