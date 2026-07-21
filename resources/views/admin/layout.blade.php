<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Админка</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        nav a { margin-right: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ccc; padding: 8px; }
        form { display: inline; }
    </style>
</head>
<body>

<nav>
    <a href="{{ route('admin.dashboard') }}">Главная</a>
    <a href="{{ route('admin.products.index') }}">Товары</a>
    <a href="{{ route('admin.categories.index') }}">Категории</a>
    <a href="{{ route('admin.orders.index') }}">Заказы</a>
</nav>

@if(session('success'))
    <div style="color: green; margin-top: 10px">{{ session('success') }}</div>
@endif

@yield('content')

</body>
</html>
