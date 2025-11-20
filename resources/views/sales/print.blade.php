<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $sale->id }}</title>
</head>
<body onload="window.print()">
    @include('sales._receipt', ['sale' => $sale])
</body>
</html>
