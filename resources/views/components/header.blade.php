<header class="bg-white">
    <div class="max-w-[1500px] mx-auto h-[72px] flex items-center justify-between gap-6">

        {{-- ЛОГОТИП --}}
        <a href="/" class="text-3xl font-bold text-red-600 whitespace-nowrap">
            MyShop
        </a>

        {{-- ЦЕНТР: КАТАЛОГ + ПОИСК --}}
        <div class="flex items-center flex-1 gap-4">

            {{-- Кнопка Каталог --}}
            <!-- <a href="/catalog"
               class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2 whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-5 h-5 stroke-black fill-white">
                    <path d="M0 96C0 78.3 14.3 64 32 64l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 128C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32l384 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 288c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32L32 448c-17.7 0-32-14.3-32-32s14.3-32 32-32l384 0c17.7 0 32 14.3 32 32z"/>
                </svg>
                <span class="text-[16px]">Каталог</span>
            </a> -->

            <button id="catalog-toggle"
                class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-md cursor-pointer">
                <span id="catalog-icon-open">☰</span>
                <span id="catalog-icon-close" class="hidden">✕</span>
                <span>Каталог</span>
            </button>

            @include('components.mega-menu')


            {{-- Поиск --}}
            <form method="GET" action="{{ route('home') }}" class="flex items-center flex-1 h-[42px]">
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Поиск товаров..."
                    class="w-full px-4 border-b border-t border-l border-gray-300 rounded-l-lg h-full">

                <button class="px-6 bg-red-600 text-white rounded-r-lg h-full hover:bg-red-700 transition">
                    Найти
                </button>
            </form>

        </div>

        {{-- ПРАВЫЕ ИКОНКИ --}}
        <div class="flex items-center gap-6">

            {{-- Избранное --}}
            <a href="#" class="hover:text-red-600 flex flex-col items-center ">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 stroke-black fill-white" viewBox="0 0 24 24">
                    <path stroke-width="2" d="M12 21s-6.7-4.35-10-9.14C-1.5 7.5 1.5 2 6.5 2c2.54 0 4.5 2 5.5 3 1-1 3-3 5.5-3C22.5 2 25.5 7.5 22 11.86 18.7 16.65 12 21 12 21z" />
                </svg>
                <span class="text-[12px]">Избранное</span>
            </a>

            {{-- Профиль --}}
            @auth
            <details class="relative">
                <summary class="list-none cursor-pointer flex flex-col items-center  [&::-webkit-details-marker]:hidden">
                    <img src="{{ auth()->user()->avatar_url }}"
                        class="w-6 h-6 rounded-full object-cover border border-gray-300">
                    <span class="text-[12px]">{{ auth()->user()->name }}</span>
                </summary>

                <div class="absolute right-0 mt-3 w-56 bg-white shadow-lg rounded-lg border border-gray-200 py-2 z-50">

                    <div class="px-4 py-2 border-b border-gray-200">
                        <p class="font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-gray-600">{{ auth()->user()->email }}</p>
                    </div>

                    <a href="{{ route('profile') }}"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100 transition">
                        <i data-lucide="settings"></i>
                        Профиль
                    </a>

                    <a href="{{ route('profile.orders') }}"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100 transition">
                        <i data-lucide="settings"></i>
                        Мои заказы
                    </a>

                    <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100 transition">
                        <i data-lucide="settings"></i>
                        Избранное
                    </a>

                    <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100 transition">
                        <i data-lucide="settings"></i>
                        Мои отзывы
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
            @else
            <a href="/login" class="hover:text-red-600 flex flex-col items-center ">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-width="1.5" stroke="currentColor"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <span class="text-[12px]">Войти</span>
            </a>
            @endauth

            {{-- Корзина --}}
            <a href="/cart" class="hover:text-red-600 flex items-center  flex-col">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-cart3 text-xl font-semibold" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l.84 4.479 9.144-.459L13.89 4zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                </svg>
                <span class="text-[10px]">Корзина</span>
            </a>

        </div>

    </div>
</header>
