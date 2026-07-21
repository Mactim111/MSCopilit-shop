<div id="gallery-modal"
     class="fixed inset-0 z-[999] hidden flex min-h-screen overflow-hidden">

    <!-- Левая колонка -->
    <div class="flex-1 bg-[#231f20bf]"></div>

    <!-- Центральная колонка -->
    <div class="w-[1500px] bg-white relative mx-auto flex flex-col">

        <!-- Кнопка закрытия -->
        <button id="close-modal"
                class="absolute top-[12px] right-[12px] z-[70]
                       text-gray-700 w-[32px] h-[32px]
                       flex items-center justify-center cursor-pointer">
            ✕
        </button>

        <!-- Верхний блок -->
        <div class="h-[780px] pt-[32px] flex items-start justify-center relative">

            <!-- Большая картинка -->
            <img id="modal-main-image"
                 src="{{ $activeImage }}"
                 class="h-[748px] object-contain">

            <!-- Кнопка назад -->
            <button 
                class="js-modal-prev absolute left-[40px] top-1/2 -translate-y-1/2
                       w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                       flex items-center justify-center cursor-pointer z-20">
                <span class="text-red-600">
                    @include('products.icons.chevron-left-thin')
                </span>
            </button>

            <!-- Кнопка вперед -->
            <button 
                class="js-modal-next absolute right-[40px] top-1/2 -translate-y-1/2
                       w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                       flex items-center justify-center cursor-pointer z-20">
                <span class="text-red-600">
                    @include('products.icons.chevron-right-thin')
                </span>
            </button>

        </div>

        <!-- Нижний блок -->
        <div class="h-[140px] flex items-center justify-center">

            <!-- Дочерний блок 60px -->
            <div class="h-[60px] flex items-center justify-center gap-[12px]">

                @foreach ($images as $img)
                    <img src="{{ $img->url }}"
                         class="w-[60px] h-[60px] object-cover cursor-pointer
                                js-modal-thumb
                                {{ $img->id === $activeId ? '' : 'grayscale' }}"
                         data-id="{{ $img->id }}">
                @endforeach

            </div>
        </div>

    </div>

    <!-- Правая колонка -->
    <div class="flex-1 bg-[#231f20bf]"></div>

</div>
