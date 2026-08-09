import './bootstrap';
import '../css/app.css';

import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

Swiper.use([Navigation, Pagination, Autoplay]);

document.addEventListener('DOMContentLoaded', () => {

    // -----------------------------
    // ИНИЦИАЛИЗАЦИЯ ВСЕХ SWIPER-СЛАЙДЕРОВ
    // -----------------------------
    document.querySelectorAll('.js-swiper').forEach(swiperEl => {

        const container = swiperEl.parentElement;

        const next = container.querySelector('.js-swiper-next');
        const prev = container.querySelector('.js-swiper-prev');
        const pagination = container.querySelector('.js-swiper-pagination');

        new Swiper(swiperEl, {
            modules: [Navigation, Pagination, Autoplay],

            slidesPerView: swiperEl.dataset.slides ?? 'auto',
            spaceBetween: Number(swiperEl.dataset.space ?? 12.5),
            loop: swiperEl.dataset.loop === 'true',
            grabCursor: swiperEl.dataset.grab === 'true',

            autoplay: swiperEl.dataset.autoplay === 'true'
                ? { delay: 3000, disableOnInteraction: false }
                : false,

            navigation: swiperEl.dataset.navigation === 'true'
                ? { nextEl: next, prevEl: prev }
                : false,

            pagination: swiperEl.dataset.pagination === 'true'
                ? { el: pagination, clickable: true }
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
