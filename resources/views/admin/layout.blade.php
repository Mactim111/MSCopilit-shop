<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Админка</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: sans-serif; padding: 20px; }
        nav a { margin-right: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ccc; padding: 8px; }
        form { display: inline; }
    </style>
</head>
<body>
{{-- Единый fixed-контейнер сообщений для страниц административного интерфейса. --}}
<div id="flash-messages" class="fixed top-[24px] left-1/2 -translate-x-1/2 z-[60]
                                    w-[min(600px,calc(100vw-32px))] pointer-events-none">
    <div class="w-full">
        @php
            $flashType = session('error') ? 'error' : (session('success') ? 'success' : null);
            $flashMessage = $flashType ? session($flashType) : null;
            $isRemovalMessage = is_string($flashMessage) && preg_match('/удал|снят/i', $flashMessage);
        @endphp
        @if($flashMessage)
            <div data-flash-message
                 data-flash-type="{{ $isRemovalMessage ? 'removal' : $flashType }}"
                 class="pointer-events-auto relative p-3 pr-10 rounded text-center shadow-lg
                        {{ $isRemovalMessage || $flashType === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}
                        transition-opacity duration-300">
                <span>{{ $flashMessage }}</span>
                <button type="button" data-dismiss-flash aria-label="Закрыть сообщение"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xl leading-none opacity-60 hover:opacity-100 cursor-pointer">
                    &times;
                </button>
            </div>
        @endif
    </div>
</div>

<nav>
    <a href="{{ route('admin.dashboard') }}">Главная</a>
    <a href="{{ route('admin.products.index') }}">Товары</a>
    <a href="{{ route('admin.categories.index') }}">Категории</a>
    <a href="{{ route('admin.orders.index') }}">Заказы</a>
</nav>

@yield('content')

</body>
</html>
