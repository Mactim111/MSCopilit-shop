import './bootstrap';
import '../css/app.css';

import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

Swiper.use([Navigation, Pagination]);

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-swiper').forEach(swiperEl => {

        const container = swiperEl.parentElement;

        const next = container.querySelector('.js-swiper-next');
        const prev = container.querySelector('.js-swiper-prev');
        const pagination = container.querySelector('.js-swiper-pagination');

        new Swiper(swiperEl, {
            modules: [Navigation, Pagination],

            slidesPerView: swiperEl.dataset.slides ?? 'auto',
            spaceBetween: Number(swiperEl.dataset.space ?? 12.5),
            loop: swiperEl.dataset.loop === 'true',
            grabCursor: swiperEl.dataset.grab === 'true',

            navigation: swiperEl.dataset.navigation === 'true'
                ? { nextEl: next, prevEl: prev }
                : false,

            pagination: swiperEl.dataset.pagination === 'true'
                ? { el: pagination, clickable: true }
                : false,
        });
    });
});





