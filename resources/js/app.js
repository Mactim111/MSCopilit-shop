import './bootstrap';
import '../css/app.css';

import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

Swiper.use([Navigation, Pagination, Autoplay]);

document.addEventListener('DOMContentLoaded', () => {

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
