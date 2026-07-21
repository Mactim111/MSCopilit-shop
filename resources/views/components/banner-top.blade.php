<div id="top-banner"
     class="relative w-full h-[60px] sm:h-[70px] md:h-[78px] overflow-hidden bg-white z-[60]">

{{-- Картинка как фон --}}
    <img src="{{ asset('storage/slider/11.jpg') }}"
         class="absolute inset-0 w-full h-full object-cover object-center"
         alt="Top Banner">

    {{-- Кнопка закрытия --}}
    <button id="close-banner"
            class="absolute top-1.5 right-2 z-[70] bg-white/90 hover:bg-white text-gray-700 w-7 h-7 flex items-center justify-center rounded-full shadow-md transition">
        ✕
    </button>


</div>

<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#close-banner');
        if (!btn) return;

        const banner = document.getElementById('top-banner');
        if (!banner) return;

        banner.style.transition = 'opacity 0.3s ease';
        banner.style.opacity = '0';

        setTimeout(() => {
            banner.remove();
            window.scrollTo({ top: 0, behavior: 'instant' });
        }, 300);
    });

</script>
