<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Dashboard') | IMS</title>
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}"> <!-- External CSS -->
@stack('styles')
</head>
<body>
    @include('admin.partials.sidebar')

    <div class="main-content">
        @include('admin.partials.topbar')

        <div class="content">
            @yield('content')
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
</body>
</html>
