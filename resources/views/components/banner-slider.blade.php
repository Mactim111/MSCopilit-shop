<div class="swiper mySwiper w-full h-[300px] md:h-[450px] rounded-xl overflow-hidden shadow-lg">

    <div class="swiper-wrapper">

        <div class="swiper-slide">
            <img src="{{ asset('storage/slider/1.jpg') }}" class="w-full h-full object-cover" alt="Banner 1">
        </div>

        <div class="swiper-slide">
            <img src="{{ asset('storage/slider/2.jpg') }}" class="w-full h-full object-cover" alt="Banner 2">
        </div>

        <div class="swiper-slide">
            <img src="{{ asset('storage/slider/3.jpg') }}" class="w-full h-full object-cover" alt="Banner 3">
        </div>

        <div class="swiper-slide">
            <img src="{{ asset('storage/slider/4.jpg') }}" class="w-full h-full object-cover" alt="Banner 4">
        </div>

        <div class="swiper-slide">
            <img src="{{ asset('storage/slider/5.jpg') }}" class="w-full h-full object-cover" alt="Banner 5">
        </div>

    </div>

    <!-- Навигация -->
    <div class="swiper-button-next text-white"></div>
    <div class="swiper-button-prev text-white"></div>

    <!-- Пагинация -->
    <div class="swiper-pagination"></div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        new Swiper(".mySwiper", {
            loop: true,
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            effect: "slide",
            speed: 600,
        });
    });
</script>

