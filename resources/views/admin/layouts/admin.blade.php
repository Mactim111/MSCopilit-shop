<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Админ-панель')</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside id="sidebar"
            class="bg-gray-900 text-gray-100 w-64 transition-all duration-300 flex flex-col relative">

            <!-- Логотип -->
            <div class="p-6 text-xl font-bold tracking-wide">
                <span id="logo-full">Админ-панель</span>
                <span id="logo-mini" class="hidden">A</span>
            </div>

            <!-- Меню -->
            <nav class="flex-1 px-3 space-y-1">

                <a href="{{ route('admin.dashboard') }}"
                    class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    @include('admin.svg.home')
                    <span class="menu-text">Главная</span>
                </a>

                <a href="{{ route('admin.products.index') }}"
                    class="menu-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    @include('admin.svg.package')
                    <span class="menu-text">Товары</span>
                </a>

                <a href="#" class="menu-item">
                    @include('admin.svg.layers')
                    <span class="menu-text">Товарные предложения</span>
                </a>

                <a href="{{ route('admin.categories.index') }}"
                    class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    @include('admin.svg.folder')
                    <span class="menu-text">Категории</span>
                </a>

                <a href="{{ route('admin.orders.index') }}"
                    class="menu-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    @include('admin.svg.cart')
                    <span class="menu-text">Заказы</span>
                </a>

                <a href="#" class="menu-item">
                    @include('admin.svg.users')
                    <span class="menu-text">Пользователи</span>
                </a>

            </nav>

            <!-- Разделитель -->
            <div class="border-t border-gray-700 my-2"></div>

            <!-- Вернуться в магазин -->
            <div class="p-3">
                <a href="{{ route('home') }}" class="menu-item">
                    @include('admin.svg.arrow-left')
                    <span class="menu-text">Вернуться в магазин</span>
                </a>
            </div>

        </aside>

        <!-- КНОПКА СВОРАЧИВАНИЯ -->
        <button id="toggleSidebar"
            class="fixed z-50 bg-gray-800 text-white w-10 h-10 rounded-full shadow
                   flex items-center justify-center hover:bg-gray-700 transition"
            style="bottom: 33vh; left: calc(16rem - 20px);">
            @include('admin.svg.chevron-left')
        </button>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col">

            <!-- HEADER -->
            <header class="bg-white shadow px-6 py-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-800">
                    @yield('header-title', 'Раздел')
                </h1>

                <!-- Аватар -->
                <details class="relative">
                    <summary class="list-none cursor-pointer flex items-center  [&::-webkit-details-marker]:hidden">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-10 h-10 rounded-full object-cover border">
                    </summary>

                    <div class="absolute right-0 mt-3 w-56 bg-white shadow-lg rounded-lg border py-2 z-50">

                        <div class="px-4 py-2 border-b">
                            <p class="font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-600">{{ auth()->user()->email }}</p>
                        </div>

                        <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100 transition">
                            <i data-lucide="settings"></i>
                            Настройки
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-100 transition text-left">
                                <i data-lucide="log-out"></i>
                                Выход
                            </button>
                        </form>

                    </div>
                </details>

            </header>

            @if(session('success'))
            <div id="flash-message"
                class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-[9999] transition-opacity duration-500">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div id="flash-message"
                class="fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-[9999] transition-opacity duration-500">
                {{ session('error') }}
            </div>
            @endif



            <!-- CONTENT -->
            <main class="p-6">
                @yield('content')
            </main>

        </div>

    </div>

    <script>
        // Закрытие dropdown при клике вне <details>
        document.addEventListener('click', function(e) {
            const details = document.querySelector('details');

            if (!details) return;

            // если клик вне details — закрываем
            if (!details.contains(e.target)) {
                details.removeAttribute('open');
            }
        });
    
        // --- Flash message auto-hide (session messages) ---
        setTimeout(() => {
            const el = document.getElementById('flash-message');
            if (el) {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }
        }, 3000);

        // --- JS toast for fetch actions ---
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed left-1/2 top-4 -translate-x-1/2 
                px-6 py-3 rounded-lg shadow-lg z-[9999] text-white 
                ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}
                transition-opacity duration-500`;
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }


        // --- Sidebar toggle logic ---
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const logoFull = document.getElementById('logo-full');
        const logoMini = document.getElementById('logo-mini');

        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');

            if (collapsed) {
                logoFull.classList.add('hidden');
                logoMini.classList.remove('hidden');
                toggleBtn.style.left = 'calc(5rem - 20px)';
                toggleBtn.innerHTML = `{!! trim(preg_replace('/\s+/', ' ', view('admin.svg.chevron-right')->render())) !!}`;
            } else {
                logoMini.classList.add('hidden');
                logoFull.classList.remove('hidden');
                toggleBtn.style.left = 'calc(16rem - 20px)';
                toggleBtn.innerHTML = `{!! trim(preg_replace('/\s+/', ' ', view('admin.svg.chevron-left')->render())) !!}`;
            }
        });
    </script>


</body>

</html>