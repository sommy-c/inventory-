<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | IMS</title>

    {{-- CSRF Token for AJAX / fetch --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap CSS --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    {{-- Dashboard styles --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    @stack('styles')
</head>

@php
    use App\Models\Setting;
    $themeMode = Setting::get('theme_mode', 'dark'); // dark | light
@endphp

<body class="theme-{{ $themeMode }}">
    @include('admin.partials.sidebar')

    <div class="main-content">
        @include('admin.partials.topbar')

        {{-- Center + max-width control for all pages --}}
        <div class="page-container">
            <div class="content">
                @yield('content')
            </div>
        </div>
    </div>

    {{-- Chart.js (global) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Bootstrap JS --}}
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>

    {{-- Page-specific scripts --}}
    @stack('scripts')
</body>
</html>
