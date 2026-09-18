import './bootstrap';
import '../css/app.css';

import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

Swiper.use([Navigation, Pagination, Autoplay]);

document.addEventListener('DOMContentLoaded', () => {

    /*
     * Единая логика flash-сообщений layout: пользователь может закрыть
     * сообщение вручную, а если этого не сделал — оно исчезает автоматически.
     */
    document.querySelectorAll('[data-flash-message]').forEach((message) => {
        const close = () => {
            message.classList.add('opacity-0');
            window.setTimeout(() => message.remove(), 300);
        };

        message.querySelector('[data-dismiss-flash]')?.addEventListener('click', close);
        window.setTimeout(close, 6000);
    });

    /*
     * AJAX-добавление товара из всех трёх карточек:
     * слайдера, списка вариантов и страницы варианта товара.
     * Маршрут остаётся тем же, поэтому обычная серверная логика корзины
     * продолжает работать и без JavaScript.
     */
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('.js-cart-add-form');

        if (!form) {
            return;
        }

        event.preventDefault();

        const button = form.querySelector('button[type="submit"], button:not([type])');

        if (!button || button.disabled) {
            return;
        }

        const originalText = button.textContent.trim();
        button.disabled = true;
        button.textContent = 'Добавление...';

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            /*
             * Даже при Accept: application/json сервер может вернуть HTML
             * при временной PHP/Laravel-ошибке или redirect. Не пытаемся
             * разбирать такой ответ как JSON, чтобы не показывать пользователю
             * техническую ошибку «Unexpected token '<'».
             */
            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                throw new Error('Сервер вернул неожиданный ответ. Попробуйте повторить действие.');
            }

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Не удалось добавить товар в корзину');
            }

            updateCartCount(payload.count);
            showFlashMessage(payload.message || 'Товар добавлен в корзину', 'success');

            const link = document.createElement('a');
            link.href = form.dataset.cartUrl || '/cart';
            link.textContent = 'В корзине';
            // Используем классы серверной ссылки «В корзине», а не классы
            // красной кнопки «В корзину», чтобы состояние сразу выглядело одинаково.
            link.className = form.dataset.cartLinkClass || 'block text-center';
            form.replaceWith(link);
        } catch (error) {
            button.disabled = false;
            button.textContent = originalText;
            window.alert(error.message);
        }
    });

    /*
     * AJAX-переключение избранного во всех карточках вариантов.
     * Обработчик находится отдельно от корзины, поэтому работает
     * независимо от предыдущих действий пользователя.
     */
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('.favorite-toggle');

        if (!button || button.disabled || !button.dataset.favoriteUrl) {
            return;
        }

        event.preventDefault();
        button.disabled = true;

        try {
            const response = await fetch(button.dataset.favoriteUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }

            /*
             * Гостевой запрос проходит через auth middleware и после redirect
             * возвращает HTML страницы входа. Перенаправляем пользователя на
             * неё явно, а HTML других ошибок не пытаемся разбирать как JSON.
             */
            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                if (response.redirected && new URL(response.url).pathname === '/login') {
                    window.location.href = response.url;
                    return;
                }

                throw new Error('Сервер вернул неожиданный ответ. Попробуйте повторить действие.');
            }

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Не удалось изменить избранное');
            }

            button.innerHTML = payload.icon;
            button.setAttribute('aria-pressed', payload.is_favorite ? 'true' : 'false');
            updateFavoriteCount(payload.count);
            showFlashMessage(payload.message, 'success');
        } catch (error) {
            showFlashMessage(error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });

    function updateCartCount(count) {
        const badge = document.getElementById('cart-count-badge');

        if (!badge) {
            return;
        }

        const normalizedCount = Number(count) || 0;
        badge.textContent = normalizedCount > 99 ? '99+' : normalizedCount;
        badge.classList.toggle('hidden', normalizedCount === 0);
    }

    function updateFavoriteCount(count) {
        const badge = document.getElementById('favorite-count-badge');

        if (!badge) {
            return;
        }

        const normalizedCount = Number(count) || 0;
        badge.textContent = normalizedCount > 99 ? '99+' : normalizedCount;
        badge.classList.toggle('hidden', normalizedCount === 0);
    }

    /*
     * AJAX-запрос не выполняет redirect и поэтому не может показать
     * session('success') через layout. Создаём такой же flash-блок на странице
     * прямо после успешного ответа сервера.
     */
    function showFlashMessage(message, type = 'success') {
        const container = document.querySelector('#flash-messages > div');

        if (!container) {
            return;
        }

        const flash = document.createElement('div');
        const classes = type === 'error'
            ? 'bg-red-100 text-red-700'
            : 'bg-green-100 text-green-700';

        flash.dataset.flashMessage = '';
        flash.className = `relative mb-4 p-3 pr-10 ${classes} rounded text-center transition-opacity duration-300`;
        flash.innerHTML = `
            <span></span>
            <button type="button"
                    data-dismiss-flash
                    aria-label="Закрыть сообщение"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xl leading-none opacity-60 hover:opacity-100">
                &times;
            </button>
        `;
        flash.querySelector('span').textContent = message;
        container.prepend(flash);

        const close = () => {
            flash.classList.add('opacity-0');
            window.setTimeout(() => flash.remove(), 300);
        };

        flash.querySelector('[data-dismiss-flash]').addEventListener('click', close);
        window.setTimeout(close, 6000);
    }

// -------------------------------------------------------------------------
    // ИНИЦИАЛИЗАЦИЯ ВСЕХ SWIPER-СЛАЙДЕРОВ
    // -------------------------------------------------------------------------
    // Этот блок автоматически находит все элементы с классом .js-swiper 
    // и настраивает их на основе data-атрибутов в верстке.
    document.querySelectorAll('.js-swiper').forEach(swiperEl => {

        // Ищем элементы управления внутри родительского контейнера, 
        // чтобы слайдеры на одной странице не конфликтовали друг с другом.
        const container = swiperEl.parentElement;
        const next = container.querySelector('.js-swiper-next');
        const prev = container.querySelector('.js-swiper-prev');
        const pagination = container.querySelector('.js-swiper-pagination');

        new Swiper(swiperEl, {
            // Подключаем необходимые модули
            modules: [Navigation, Pagination, Autoplay],

            // Количество отображаемых слайдов: берем из data-slides или ставим 8 по умолчанию
            slidesPerView: swiperEl.dataset.slides === 'auto' ? 'auto' : Number(swiperEl.dataset.slides ?? 8),
            
            // Количество пролистываемых слайдов за один раз (секциями)
            slidesPerGroup: Number(swiperEl.dataset.group ?? 1),
            
            // Расстояние между слайдами (в пикселях)
            spaceBetween: Number(swiperEl.dataset.space ?? 12.5),
            
            // Бесконечный цикл прокрутки
            loop: swiperEl.dataset.loop === 'true',
            
            // Изменение курсора на "руку" при наведении
            grabCursor: swiperEl.dataset.grab === 'true',

            // Улучшение отрисовки: Swiper будет следить за прогрессом видимости слайдов
            watchSlidesProgress: true, 
            
            // Если слайдов меньше, чем нужно для заполнения ряда, они будут отцентрированы
            centerInsufficientSlides: true, 

            // Настройка автопролистывания
            autoplay: swiperEl.dataset.autoplay === 'true'
                ? { 
                    delay: 4000, // Установлен интервал 4 секунды (4000мс)
                    disableOnInteraction: false // Не останавливать автоплей после кликов пользователя
                  }
                : false,

            // Настройка стрелок "Вперед/Назад"
            navigation: swiperEl.dataset.navigation === 'true'
                ? { nextEl: next, prevEl: prev }
                : false,

            // Настройка пагинации (полосок под слайдером)
            pagination: swiperEl.dataset.pagination === 'true'
                ? { 
                    el: pagination, 
                    clickable: true // Позволяет кликать по полоскам для перехода
                  }
                : false,
        });
    });

    // -----------------------------
    // КАСТОМНОЕ МОДАЛЬНОЕ ОКНО ДЛЯ ЗАКРЫТИЯ TOP-BANNER
    // -----------------------------
    const banner = document.getElementById('top-banner');
    const closeBtn = document.getElementById('banner-top-close');
    const modal = document.getElementById('banner-confirm');
    const hideBtn = document.getElementById('banner-hide');
    const cancelBtn = document.getElementById('banner-cancel');

    // Если баннера нет — ничего не делаем
    if (banner && closeBtn && modal) {

        // Открыть модалку
        closeBtn.addEventListener('click', () => {

            const rect = closeBtn.getBoundingClientRect();
            const modalWidth = 278; // ширина окна

            // ПРАВАЯ ГРАНИЦА МОДАЛКИ = ПРАВАЯ ГРАНИЦА КНОПКИ
            modal.style.left = (rect.right - modalWidth - 10) + "px";

            // МОДАЛКА ПОД КНОПКОЙ, СМЕЩЕНИЕ 20px
            modal.style.top = (rect.bottom + 10) + "px";

            modal.classList.remove('hidden');
        });

        // Скрыть баннер
        hideBtn.addEventListener('click', () => {
            banner.style.transition = 'opacity 0.3s ease';
            banner.style.opacity = '0';
            setTimeout(() => banner.remove(), 300);
            modal.style.display = 'none';
        });

        // Отмена
        cancelBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

});
