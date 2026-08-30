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
    @if($bannerTop)
        @include('components.banner-top', ['banner' => $bannerTop])
    @endif

    {{-- Header (sticky, но ниже баннера) --}}
    <div class="w-full bg-white sticky top-0 z-50">
        <div class="max-w-[1500px] mx-auto">
            @include('components.header')
        </div>
    </div>

     {{-- Слайдер из заголовков популярных подкатегорий --}}
    <div id="category-slider-wrapper" class="transition-transform duration-200">
        <x-category-slider />
    </div>

    {{-- Sticky border line --}}
    <div class="w-full sticky top-[72px] z-40 bg-white h-[10px] border-b-white shadow-lg shadow-[0_6px_20px_-4px_rgba(0,0,0,0.32)]">
    </div>

    {{-- Контентная часть — был серый фон - изменили на белый! --}}
    <main class="min-h-screen">
        <div class="max-w-[1500px] mx-auto">

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

        // константа для ИСЧЕЗНОВЕНИЯ CATEGORY-SLIDER
        const sliderWrapper = document.getElementById('category-slider-wrapper');

        // Элементы верстки верха страницы
        const banner = document.getElementById('top-banner');
        const header = document.querySelector('header');

        // Кнопка Каталог + Mega Menu
        const panels = document.querySelectorAll('.mega-panel');
        const leftButtons = document.querySelectorAll('[data-group]');
        const toggleBtn = document.getElementById('catalog-toggle');
        const megaMenu = document.getElementById('mega-menu');
        const iconOpen = document.getElementById('catalog-icon-open');
        const iconClose = document.getElementById('catalog-icon-close');
        
        // вычисляем высоту верхнего баннера, и с учетом высоты header, устанавливаем правильное смещение от верха страницы для mega-menu,
        // ТЕПЕРЬ! открывается строго под! баннером + header
        const bannerHeight = banner ? banner.offsetHeight : 0;
        const headerHeight = header.offsetHeight;
        const offset = bannerHeight + headerHeight;  // = 150

        // Устанавливаем top и height для mega-menu
        megaMenu.style.top = offset + 'px';
        megaMenu.style.height = `calc(100vh - ${offset}px)`;

        const showPanel = (name) => {
            panels.forEach(p => {
                p.classList.toggle('hidden', p.dataset.panel !== name);
            });
        };

        const openMenu = () => {
            megaMenu.classList.remove('hidden');
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');

            const borderLine = document.getElementById('sticky-border-line');
            if (sliderWrapper) sliderWrapper.classList.add('-translate-y-full');
            if (borderLine) borderLine.classList.add('opacity-0');

            showPanel('actions');
        };

        const closeMenu = () => {
            megaMenu.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');

            const borderLine = document.getElementById('sticky-border-line');
            if (sliderWrapper) sliderWrapper.classList.remove('-translate-y-full');
            if (borderLine) borderLine.classList.remove('opacity-0');
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

        // Закрытие по клику вне
        document.addEventListener('click', (e) => {
    if (
        !megaMenu.contains(e.target) &&
        !e.target.closest('#catalog-toggle')
    ) {
        closeMenu();
    }
});


        

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

    });

    // ЛОГИКА ИСЧЕЗНОВЕНИЯ CATEGORY-SLIDER
    document.addEventListener('scroll', () => {
        const sliderWrapper = document.getElementById('category-slider-wrapper');
        if (!sliderWrapper) return;

        if (window.scrollY > 10) {
            sliderWrapper.classList.add('-translate-y-full');
        } else {
            sliderWrapper.classList.remove('-translate-y-full');
        }
        
    });

</script>

</body>

</html>
