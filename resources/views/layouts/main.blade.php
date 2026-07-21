<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Интернет‑магазин')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css">

    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
    </script>
</head>

<body class="bg-white text-gray-900">


    {{-- Баннер (sticky, выше header) --}}
    @include('components.banner-top')

    {{-- Header (sticky, но ниже баннера) --}}
    <div class="w-full bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-[1500px] mx-auto">
            @include('components.header')
        </div>
    </div>

    {{-- Вторая строка навигации --}}
    <div class="w-full bg-white border-b border-gray-200">
        <div class="max-w-[1500px] mx-auto h-[42px] flex items-center gap-6 overflow-x-auto whitespace-nowrap">

            <a href="/sales" class="text-red-600 font-semibold hover:text-red-700 text-[14px]">
                Все акции
            </a>

            @foreach($categoriesHit as $cat)
            <a href="{{ route('catalog.category', [$cat->parent->slug, $cat->slug]) }}"
                class="text-[#231F20] hover:text-red-600 transition text-[14px] font-semibold">
                {{ $cat->title }}
            </a>
            @endforeach


        </div>
    </div>


    {{-- Контентная часть — серый фон --}}
    <main class="min-h-screen bg-gray-100">
        <div class="max-w-[1500px] mx-auto py-10">

            @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded text-center">
                {{ session('success') }}
            </div>
            @endif

            @yield('content')

        </div>
    </main>

    {{-- Footer на всю ширину --}}
    @include('components.footer')

    <x-scroll-top />

    <script>
        // Закрытие dropdown аватара
        document.addEventListener('click', function(e) {
            const details = document.querySelector('details');
            if (!details) return;
            if (!details.contains(e.target)) {
                details.removeAttribute('open');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('catalog-toggle');
            const megaMenu = document.getElementById('mega-menu');
            const iconOpen = document.getElementById('catalog-icon-open');
            const iconClose = document.getElementById('catalog-icon-close');

            const panels = document.querySelectorAll('.mega-panel');
            const leftButtons = document.querySelectorAll('[data-group]');

            const openMenu = () => {
                megaMenu.classList.remove('hidden');
                iconOpen.classList.add('hidden');
                iconClose.classList.remove('hidden');

                // по умолчанию — Акции
                showPanel('actions');
            };

            const closeMenu = () => {
                megaMenu.classList.add('hidden');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
            };

            const showPanel = (name) => {
                panels.forEach(p => {
                    p.classList.toggle('hidden', p.dataset.panel !== name);
                });
            };

            toggleBtn?.addEventListener('click', () => {
                if (megaMenu.classList.contains('hidden')) {
                    openMenu();
                } else {
                    closeMenu();
                }
            });

            leftButtons.forEach(btn => {
                btn.addEventListener('mouseenter', () => {
                    const group = btn.dataset.group;
                    showPanel(group);
                });
            });

            // Показать все / Скрыть
            document.querySelectorAll('[data-toggle-more]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.toggleMore;
                    const moreBlock = document.querySelector(`[data-more-list="${id}"]`);
                    if (moreBlock) {
                        moreBlock.classList.remove('hidden');
                        btn.classList.add('hidden');
                    }
                });
            });

            document.querySelectorAll('[data-toggle-less]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.toggleLess;
                    const moreBlock = document.querySelector(`[data-more-list="${id}"]`);
                    const showMoreBtn = document.querySelector(`[data-toggle-more="${id}"]`);
                    if (moreBlock && showMoreBtn) {
                        moreBlock.classList.add('hidden');
                        showMoreBtn.classList.remove('hidden');
                    }
                });
            });

            // Закрытие по клику вне
            document.addEventListener('click', (e) => {
                if (!megaMenu.contains(e.target) && !toggleBtn.contains(e.target)) {
                    closeMenu();
                }
            });

            if (iconClose) {
                iconClose.addEventListener('click', () => {
                    closeMenu();
                });
            }

            /* ---------------------------------------------------
               КНОПКА "ВВЕРХ" — ПОЯВЛЕНИЕ / ИСЧЕЗНОВЕНИЕ / СКРОЛЛ
               --------------------------------------------------- */

            const scrollBtn = document.getElementById('scrollTopBtn');

            if (scrollBtn) {

                // Порог появления — 20% высоты окна
                // const showAt = window.innerHeight * 0.2;
                const showAt = window.innerHeight * 0.9;

                window.addEventListener('scroll', () => {
                    if (window.scrollY > showAt) {
                        scrollBtn.style.opacity = '1';
                        scrollBtn.style.pointerEvents = 'auto';
                    } else {
                        scrollBtn.style.opacity = '0';
                        scrollBtn.style.pointerEvents = 'none';
                    }
                });

                scrollBtn.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }

        });
    </script>


</body>

</html>
