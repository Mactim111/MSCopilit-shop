<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

{{-- Единый fixed-контейнер сообщений для страниц авторизации и регистрации. --}}
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

<div class="w-full max-w-md bg-white shadow-xl rounded-xl p-8">
    <h1 class="text-2xl font-bold text-center mb-6">@yield('title')</h1>

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    @yield('content')
</div>

</body>
</html>
