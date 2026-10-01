import './bootstrap';
import '../css/app.css';

import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

Swiper.use([Navigation, Pagination, Autoplay]);

document.addEventListener('DOMContentLoaded', () => {

    // Длительность показа flash-сообщения в миллисекундах.
    // Чтобы изменить время, поменяйте значение 4000 (например, 6000 = 6 секунд).
    const flashMessageDuration = 4000;

    /*
     * Единая логика flash-сообщений layout: пользователь может закрыть
     * сообщение вручную, а если этого не сделал — оно исчезает автоматически.
     */
    document.querySelectorAll('[data-flash-message]').forEach((message) => {
        setupFlashMessage(message);
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
        const showMore = event.target.closest('[data-show-more], [data-favorites-show-more]');

        if (showMore) {
            event.preventDefault();
            event.stopPropagation();

            if (typeof window.loadPage !== 'function') {
                return;
            }

            const wrapper = showMore.closest('#show-more-wrapper, #favorites-show-more-wrapper');

            if (!wrapper) {
                return;
            }

            const currentPage = Number(wrapper.dataset.currentPage);
            const lastPage = Number(wrapper.dataset.lastPage);

            if (currentPage >= lastPage) {
                return;
            }

            const url = new URL(window.location.href);
            url.searchParams.set('page', currentPage + 1);
            await window.loadPage(url.toString());
            return;
        }

        const button = event.target.closest('.favorite-toggle, [data-favorite-remove]');

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

            if (button.hasAttribute('data-favorite-remove')) {
                button.closest('[data-favorite-card]')?.remove();
                updateFavoritesPageCount(payload.count);

                /*
                 * На странице избранного сервер также формирует заголовок
                 * со счетчиками подкатегорий. После удаления карточки нужно
                 * заменить этот блок, чтобы обновить счетчики и empty state.
                 */
                if (document.getElementById('favorites-heading')
                    && typeof window.loadFavoritesPage === 'function') {
                    const favoritesUrl = new URL(window.location.href);
                    favoritesUrl.searchParams.delete('page');
                    await window.loadFavoritesPage(favoritesUrl.toString());
                }
            }

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

    function updateFavoritesPageCount(count) {
        const counter = document.getElementById('favorites-page-count');

        if (!counter) {
            return;
        }

        counter.textContent = Number(count) || 0;
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

        const removalMessage = /удал|снят/i.test(message);
        const flashType = removalMessage ? 'removal' : type;
        const current = container.querySelector('[data-flash-message]');

        if (current?.querySelector('span')?.textContent === message) {
            setupFlashMessage(current);
            return;
        }

        current?.remove();

        const flash = document.createElement('div');
        const classes = flashType === 'removal' || type === 'error'
            ? 'bg-red-100 text-red-700'
            : 'bg-green-100 text-green-700';

        flash.dataset.flashMessage = '';
        flash.dataset.flashType = flashType;
        flash.className = `pointer-events-auto relative p-3 pr-10 ${classes} rounded text-center shadow-lg transition-opacity duration-300`;
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
        container.replaceChildren(flash);
        setupFlashMessage(flash);
    }

    function setupFlashMessage(flash) {
        const close = () => {
            if (!flash.isConnected) {
                return;
            }

            flash.classList.add('opacity-0');
            window.setTimeout(() => flash.remove(), 300);
        };

        flash.querySelector('[data-dismiss-flash]').addEventListener('click', close);
        window.clearTimeout(Number(flash.dataset.timeoutId));
        flash.dataset.timeoutId = String(window.setTimeout(close, flashMessageDuration));
    }

    // Эти функции используются страницей избранного для синхронизации
    // счетчиков после AJAX-фильтрации и удаления подкатегории.
    window.updateFavoriteCount = updateFavoriteCount;
    window.updateFavoritesPageCount = updateFavoritesPageCount;
    window.showFlashMessage = showFlashMessage;

    // Универсальная модалка создания отзыва открывается и из заказа, и со страницы варианта.
    const reviewModal = document.querySelector('[data-review-create-modal]');

    if (reviewModal) {
        const formPanel = reviewModal.querySelector('[data-review-form-panel]');
        const formState = reviewModal.querySelector('[data-review-form-state]');
        const thanksPanel = reviewModal.querySelector('[data-review-thanks-panel]');
        const form = reviewModal.querySelector('[data-review-create-form]');
        const ratingInput = reviewModal.querySelector('[data-review-rating]');
        const commentInput = reviewModal.querySelector('[data-review-comment]');
        const submitButton = reviewModal.querySelector('[data-review-submit]');
        const loadingOverlay = reviewModal.querySelector('[data-review-loading]');
        const errorMessage = reviewModal.querySelector('[data-review-form-error]');
        const starButtons = [...reviewModal.querySelectorAll('[data-rating-star]')];
        let selectedRating = 0;
        let isSubmitting = false;
        let wasSubmitted = false;
        let previousFocus = null;

        const showFormError = message => {
            errorMessage.textContent = message;
            errorMessage.classList.toggle('hidden', !message);
        };

        const updateSubmitState = () => {
            const isValid = selectedRating > 0 && commentInput.value.trim().length > 0;
            submitButton.disabled = !isValid || isSubmitting;
            submitButton.classList.toggle('cursor-not-allowed', submitButton.disabled);
            submitButton.classList.toggle('border-[#bdbbbc]', submitButton.disabled);
            submitButton.classList.toggle('bg-[#bdbbbc]', submitButton.disabled);
            submitButton.classList.toggle('cursor-pointer', !submitButton.disabled);
            submitButton.classList.toggle('border-[#DC092E]', !submitButton.disabled);
            submitButton.classList.toggle('bg-[#DC092E]', !submitButton.disabled);
        };

        const setRating = rating => {
            selectedRating = Math.max(0, Math.min(5, rating));
            ratingInput.value = selectedRating ? String(selectedRating) : '';

            starButtons.forEach((button, index) => {
                const active = index < selectedRating;
                button.setAttribute('aria-checked', String(index + 1 === selectedRating));
                button.classList.toggle('border-[#DC092E]', !active);
                button.classList.toggle('bg-white', !active);
                button.classList.toggle('border-[#ffb000]', active);
                button.classList.toggle('bg-[#ffb000]', active);

                const star = button.querySelector('svg');
                star.classList.toggle('fill-white', !active);
                star.classList.toggle('stroke-[#DC092E]', !active);
                star.classList.toggle('fill-[#ffb000]', active);
                star.classList.toggle('stroke-[#ffb000]', active);
            });

            updateSubmitState();
        };

        const clearMediaPreview = input => {
            const slot = input.closest('[data-media-slot]').parentElement;
            const preview = slot.querySelector('[data-media-preview]');
            const placeholder = slot.querySelector('[data-media-placeholder]');
            const removeButton = slot.querySelector('[data-media-remove]');

            if (input.dataset.previewUrl) {
                URL.revokeObjectURL(input.dataset.previewUrl);
                delete input.dataset.previewUrl;
            }

            input.value = '';
            preview.replaceChildren();
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            removeButton.textContent = '+';
            removeButton.setAttribute(
                'aria-label',
                input.dataset.mediaKind === 'video' ? 'Добавить видео' : 'Добавить фотографию'
            );
        };

        const resetForm = () => {
            form.reset();
            showFormError('');
            setRating(0);
            reviewModal.querySelectorAll('[data-media-input]').forEach(clearMediaPreview);
            submitButton.disabled = true;
            isSubmitting = false;
            wasSubmitted = false;
            loadingOverlay.classList.add('hidden');
            loadingOverlay.classList.remove('flex');
            formPanel.setAttribute('aria-busy', 'false');
        };

        const closeModal = () => {
            if (isSubmitting) return;

            reviewModal.classList.add('hidden');
            reviewModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');

            if (wasSubmitted) {
                window.location.reload();
                return;
            }

            resetForm();
            formPanel.classList.remove('hidden');
            formState.classList.remove('hidden');
            thanksPanel.classList.add('hidden');
            thanksPanel.classList.remove('flex');
            reviewModal.setAttribute('aria-labelledby', 'review-create-title');
            previousFocus?.focus();
        };

        const openModal = trigger => {
            if (!form || !formState || !thanksPanel || !formPanel || !submitButton) return;

            resetForm();
            previousFocus = trigger;
            form.action = trigger.dataset.formAction;

            const title = reviewModal.querySelector('[data-review-product-title]');
            const image = reviewModal.querySelector('[data-review-product-image]');
            title.textContent = trigger.dataset.productTitle || '';
            image.src = trigger.dataset.productImage || '';
            image.alt = trigger.dataset.productTitle || '';
            image.classList.toggle('hidden', !trigger.dataset.productImage);

            formState.classList.remove('hidden');
            formPanel.classList.remove('hidden');
            thanksPanel.classList.add('hidden');
            thanksPanel.classList.remove('flex');
            reviewModal.setAttribute('aria-labelledby', 'review-create-title');
            reviewModal.classList.remove('hidden');
            reviewModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            starButtons[0]?.focus();
        };

        document.addEventListener('click', event => {
            const trigger = event.target.closest('[data-review-form="create"]');
            if (trigger) {
                event.preventDefault();
                openModal(trigger);
                return;
            }

            if (event.target.closest('[data-review-modal-close], [data-review-thanks-close]')) {
                closeModal();
                return;
            }

            if (event.target === reviewModal) {
                closeModal();
                return;
            }

            const starButton = event.target.closest('[data-rating-star]');
            if (starButton) {
                const rating = Number(starButton.dataset.ratingStar);
                setRating(rating);
            }

            const removeButton = event.target.closest('[data-media-remove]');
            if (removeButton) {
                const slot = removeButton.closest('[data-media-slot]').parentElement;
                const input = slot.querySelector('[data-media-input]');
                if (input.files.length) {
                    clearMediaPreview(input);
                    showFormError('');
                    updateSubmitState();
                } else {
                    input.click();
                }
            }
        });

        reviewModal.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                closeModal();
                return;
            }

            const starButton = event.target.closest('[data-rating-star]');
            if (!starButton || !['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();

            const current = Number(starButton.dataset.ratingStar);
            const next = event.key === 'Home'
                ? 1
                : event.key === 'End'
                    ? 5
                    : Math.max(1, Math.min(5, current + (event.key === 'ArrowRight' ? 1 : -1)));
            starButtons[next - 1].focus();
            setRating(next);
        });

        commentInput.addEventListener('input', updateSubmitState);

        reviewModal.querySelectorAll('[data-media-input]').forEach(input => {
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (!file) return;

                const isVideo = input.dataset.mediaKind === 'video';
                const extension = file.name.split('.').pop().toLowerCase();
                const acceptedExtensions = isVideo
                    ? ['avi', 'mp4', 'hevc']
                    : ['jpg', 'jpeg', 'png'];
                const maxSize = (isVideo ? 100 : 5) * 1024 * 1024;

                if (!acceptedExtensions.includes(extension) || file.size > maxSize) {
                    clearMediaPreview(input);
                    showFormError(isVideo
                        ? 'Выберите видео в формате AVI, MP4 или HEVC размером не более 100 МБ.'
                        : 'Выберите изображение JPG или PNG размером не более 5 МБ.');
                    return;
                }

                showFormError('');
                const slot = input.closest('[data-media-slot]').parentElement;
                const preview = slot.querySelector('[data-media-preview]');
                const placeholder = slot.querySelector('[data-media-placeholder]');
                const removeButton = slot.querySelector('[data-media-remove]');
                if (input.dataset.previewUrl) {
                    URL.revokeObjectURL(input.dataset.previewUrl);
                }
                const previewUrl = URL.createObjectURL(file);
                input.dataset.previewUrl = previewUrl;

                const mediaPreview = document.createElement(isVideo ? 'video' : 'img');
                mediaPreview.className = 'h-full w-full object-cover';
                mediaPreview.src = previewUrl;
                if (isVideo) {
                    mediaPreview.muted = true;
                    mediaPreview.playsInline = true;
                } else {
                    mediaPreview.alt = 'Предпросмотр фотографии';
                }

                preview.replaceChildren(mediaPreview);
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                removeButton.textContent = '−';
                removeButton.setAttribute('aria-label', isVideo ? 'Удалить видео' : 'Удалить фотографию');
            });
        });

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (submitButton.disabled || isSubmitting) return;

            showFormError('');
            isSubmitting = true;
            submitButton.disabled = true;
            loadingOverlay.classList.remove('hidden');
            loadingOverlay.classList.add('flex');
            formPanel.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin',
                    body: new FormData(form),
                });
                const contentType = response.headers.get('content-type') || '';

                if (!contentType.includes('application/json')) {
                    throw new Error('Сервер вернул неожиданный ответ. Проверьте размер файлов и попробуйте ещё раз.');
                }

                const payload = await response.json();
                if (!response.ok) {
                    const validationError = Object.values(payload.errors || {}).flat()[0];
                    throw new Error(validationError || payload.message || 'Не удалось отправить отзыв.');
                }

                isSubmitting = false;
                wasSubmitted = true;
                loadingOverlay.classList.add('hidden');
                loadingOverlay.classList.remove('flex');
                formPanel.classList.add('hidden');
                formState.classList.add('hidden');
                thanksPanel.classList.remove('hidden');
                thanksPanel.classList.add('flex');
                reviewModal.setAttribute('aria-labelledby', 'review-thanks-title');
                thanksPanel.querySelector('h2').id = 'review-thanks-title';
                thanksPanel.querySelector('[data-review-thanks-close]').focus();
            } catch (error) {
                showFormError(error.message || 'Не удалось отправить отзыв.');
                isSubmitting = false;
                loadingOverlay.classList.add('hidden');
                loadingOverlay.classList.remove('flex');
                formPanel.setAttribute('aria-busy', 'false');
                updateSubmitState();
            }
        });
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
