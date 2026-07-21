<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Магазин</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<header>
    <a href="{{ route('home') }}">Магазин</a>
    <a href="{{ route('cart.index') }}">Корзина</a>
    @auth
        <span>{{ auth()->user()->name }}</span>
        <a href="{{ route('profile') }}">Профиль</a>
        <form action="{{ route('logout') }}" method="POST" style="display:inline">
            @csrf
            <button>Выйти</button>
        </form>
    @else
        <a href="{{ route('login') }}">Войти</a>
        <a href="{{ route('register') }}">Регистрация</a>
    @endauth
</header>

@if(session('success'))
    <div>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div>{{ session('error') }}</div>
@endif

@yield('content')
</body>
</html>
