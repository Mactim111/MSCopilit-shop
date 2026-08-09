@if($banner)
<div id="top-banner"
     class="relative w-full h-[60px] sm:h-[70px] md:h-[78px] overflow-hidden bg-white z-[60]">

    <img src="{{ asset($banner->path) }}"
         alt="{{ $banner->title }}"
         class="absolute inset-0 w-full h-full object-cover object-center">

    <button id="banner-top-close"
            class="absolute top-4 right-5 z-[70]
                   bg-white/90 hover:bg-white text-red-600 hover:text-red-700
                   w-7 h-7 flex items-center justify-center
                   rounded-full shadow-[0_2px_6px_rgba(0,0,0,0.25)]
                    hover:shadow-[0_4px_12px_rgba(0,0,0,0.35)]
                    transition">
        ✕
    </button>
</div>

{{-- Модалка — теперь absolute --}}
<div id="banner-confirm"
     class="absolute w-[278px] h-[92px]
            bg-white border border-gray-200 rounded-xl shadow-xl
            px-[20px] py-[15px] hidden z-[9999]">

    <div class="text-center text-[14px] mb-[9px] font-semibold text-[#231F20]">
        Хотите скрыть баннер?
    </div>

    <div class="flex justify-center gap-[10px]">
        <button id="banner-hide"
                class="w-[110px] py-1 border border-gray-400 rounded-lg text-[14px]">
            Скрыть
        </button>

        <button id="banner-cancel"
                class="w-[110px] py-1 border border-gray-400 rounded-lg text-[14px]">
            Не скрывать
        </button>
    </div>
</div>
@endif
