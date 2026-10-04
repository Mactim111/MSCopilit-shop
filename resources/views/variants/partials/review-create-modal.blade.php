<div id="review-create-modal"
     class="fixed inset-0 z-[1000] hidden items-center justify-center overflow-y-auto bg-[#231f20bf] p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="review-create-title"
     data-review-create-modal>
    <div class="relative my-auto flex min-h-[498px] max-h-[calc(100vh-32px)] w-[508px] max-w-full flex-col overflow-hidden rounded-[10px] bg-white px-5 py-8 text-left sm:px-[40px] md:pl-[50px] md:pr-[40px]"
         data-review-form-panel>
        <button type="button"
                data-review-modal-close
                aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>

        <div dir="ltr"
             class="max-h-[calc(100vh-96px)] overflow-y-auto pr-2"
             data-review-scroll-area
             data-review-form-state>
            <h2 id="review-create-title" class="mb-6 pr-8 text-left text-[32px] font-bold leading-tight text-black font-semibold">
                Добавить отзыв
            </h2>

            <div class="mb-4 flex w-full items-start gap-[6px] border-b border-dashed border-[#d6d6d6] pb-4"
                 data-review-product-summary>
                <img data-review-product-image
                     src=""
                     alt=""
                     class="h-[60px] w-[60px] shrink-0 rounded object-contain">
                <p data-review-product-title
                   class="break-words pt-1 text-[15px] font-bold leading-snug text-black font-semibold"></p>
            </div>

            <form method="POST"
                  enctype="multipart/form-data"
                  data-review-create-form
                  novalidate>
                @csrf

                {{-- Рейтинг обязателен; кнопки-звёзды также поддерживают клавиатурное управление. --}}
                <div class="mb-[9px] flex h-10 items-center justify-between border-b border-dashed border-[#d6d6d6]">
                    <span class="text-[13px] text-black font-semibold">
                        Общая оценка<span class="text-[#DC092E]">*</span>
                    </span>
                    <div class="flex items-center" role="radiogroup" aria-label="Общая оценка">
                        @for($rating = 1; $rating <= 5; $rating++)
                            <button type="button"
                                    data-rating-star="{{ $rating }}"
                                    role="radio"
                                    aria-checked="false"
                                    aria-label="{{ $rating }} из 5"
                                    class="mt-[7px] mb-1 flex h-[34px] w-[34px] cursor-pointer items-center justify-center border-0 bg-transparent p-0">
                                @include('products.icons.star-outline')
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" value="" data-review-rating>
                </div>

                <label class="mt-[9px] mb-[5px] flex items-center gap-1 pl-px text-left text-[13px] text-black font-semibold">
                    <span>Добавить видео и фото</span>
                    <span class="group relative inline-flex h-5 w-5 shrink-0 items-center justify-center"
                          tabindex="0"
                          aria-label="Информация о допустимых файлах">
                        @include('products.icons.info-icon')
                        <span role="tooltip"
                              class="pointer-events-none invisible absolute bottom-[calc(100%+8px)] left-[calc(50%+20px)] z-30 w-[min(320px,calc(100vw-100px))] -translate-x-1/2 rounded-[10px] border-[1.25px] border-[#e5e5e5] bg-white py-[6px] pl-[6px] pr-[2px] text-left text-black opacity-0 shadow-[0_-2px_4px_#231f200d,0_4px_8px_#231f201a] transition-opacity group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                            <span class="absolute -bottom-[5px] left-[calc(50%-20px)] h-2 w-2 -translate-x-1/2 rotate-45 border-b border-r border-[#e5e5e5] bg-white"></span>
                            <span class="relative block rounded-[7px] px-[9px] py-[5px]">
                                <ul class="list-disc space-y-1 pl-5 text-[14px] leading-snug">
                                    <li>Изображения в форматах JPG или PNG, размером до 5 МБ каждое.</li>
                                    <li>Видео в форматах AVI, MP4 или HEVC, размером до 100 МБ.</li>
                                </ul>
                            </span>
                        </span>
                    </span>
                </label>

                {{-- Первый слот предназначен для видео, остальные четыре — для изображений. --}}
                <div class="mb-1 flex w-full min-w-0 justify-between gap-2 text-left"
                     data-review-media-slots>
                    <div class="relative h-[54px] w-[54px] shrink-0">
                        <label class="flex h-[54px] w-[54px] cursor-pointer items-center justify-center overflow-hidden"
                               data-media-slot>
                            <input type="file"
                                   name="video"
                                   accept=".avi,.mp4,.hevc,video/x-msvideo,video/mp4,video/hevc"
                                   class="sr-only"
                                   data-media-input
                                   data-media-kind="video">
                            <span class="flex h-full w-full items-center justify-center" data-media-placeholder>
                                @include('products.icons.upload-video')
                            </span>
                            <span class="absolute inset-0 hidden" data-media-preview></span>
                        </label>
                        <button type="button"
                                aria-label="Удалить видео"
                                class="absolute -bottom-0.5 -right-0.5 z-10 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-white/95 text-[16px] leading-none text-[#DC092E] shadow-[0_2px_5px_#231f2033]"
                                data-media-remove>
                            +
                        </button>
                    </div>
                    @for($photo = 0; $photo < 4; $photo++)
                        <div class="relative h-[54px] w-[54px] shrink-0">
                            <label class="flex h-[54px] w-[54px] cursor-pointer items-center justify-center overflow-hidden"
                                   data-media-slot>
                                <input type="file"
                                       name="photos[]"
                                       accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                       class="sr-only"
                                       data-media-input
                                       data-media-kind="image">
                                <span class="flex h-full w-full items-center justify-center" data-media-placeholder>
                                    @include('products.icons.upload-photo')
                                </span>
                                <span class="absolute inset-0 hidden" data-media-preview></span>
                            </label>
                            <button type="button"
                                    aria-label="Удалить фотографию"
                                    class="absolute -bottom-0.5 -right-0.5 z-10 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-white/95 text-[16px] leading-none text-[#DC092E] shadow-[0_2px_5px_#231f2033]"
                                    data-media-remove>
                                +
                            </button>
                        </div>
                    @endfor
                </div>

                <div data-review-field>
                    <label for="review-advantages"
                           class="mt-[9px] mb-[5px] block pl-px text-left text-[13px] text-black font-semibold">
                        Преимущества
                    </label>
                    <textarea id="review-advantages"
                              name="advantages"
                              rows="3"
                              maxlength="5000"
                              class="block min-h-[84px] w-full resize-none rounded-[10px] border border-[#bdbbbc] bg-white px-4 py-[11px] text-[14px] leading-5 text-black outline-none focus:border-[#8c8c8c]"
                              data-review-optional-field></textarea>
                </div>

                <div data-review-field>
                    <label for="review-disadvantages"
                           class="mt-[9px] mb-[5px] block pl-px text-left text-[13px] text-black font-semibold">
                        Недостатки
                    </label>
                    <textarea id="review-disadvantages"
                              name="disadvantages"
                              rows="3"
                              maxlength="5000"
                              class="block min-h-[84px] w-full resize-none rounded-[10px] border border-[#bdbbbc] bg-white px-4 py-[11px] text-[14px] leading-5 text-black outline-none focus:border-[#8c8c8c]"
                              data-review-optional-field></textarea>
                </div>

                <div data-review-field>
                    <label for="review-comment"
                           class="mt-[9px] mb-[5px] block pl-px text-left text-[13px] text-black font-semibold">
                        Комментарий<span class="text-[#DC092E]">*</span>
                    </label>
                    <textarea id="review-comment"
                              name="comment"
                              rows="3"
                              maxlength="10000"
                              required
                              class="block min-h-[84px] w-full resize-none rounded-[10px] border border-[#bdbbbc] bg-white px-4 py-[11px] text-[14px] leading-5 text-black outline-none focus:border-[#8c8c8c]"
                              data-review-comment></textarea>
                </div>

                <p class="mt-2 hidden text-[13px] text-[#DC092E]"
                   role="alert"
                   data-review-form-error></p>

                <button type="submit"
                        disabled
                        class="mt-6 mb-[10px] block w-full cursor-not-allowed rounded-[10px] border border-[#bdbbbc] bg-[#bdbbbc] px-5 py-[10px] text-center text-[15px] font-semibold text-white transition-colors duration-200 disabled:cursor-not-allowed enabled:cursor-pointer enabled:hover:bg-[#a80723] enabled:focus:bg-[#a80723]"
                        data-review-submit>
                    Опубликовать отзыв
                </button>
            </form>

            <p class="mb-[5px] text-[12px] leading-snug text-[#8c8c8c]">
                Спасибо, что решили поделиться опытом!<br>
                Ваш отзыв будет опубликован через некоторое время после проверки модератором.<br>
                Обращаем Ваше внимание, что мы оставляем за собой право не публиковать отзывы.
            </p>

            <div>
                <a href="#"
                   aria-disabled="true"
                   tabindex="-1"
                   class="pointer-events-none text-left text-[15px] text-[#007EFF] transition-all duration-200 hover:text-[#0064cc]">
                    Читать все правила
                </a>
            </div>
        </div>

        <div class="absolute inset-0 z-20 hidden items-center justify-center rounded-[10px] bg-white/80"
             aria-hidden="true"
             data-review-loading>
            <span class="h-12 w-12 animate-spin rounded-full border-[5px] border-[#bdbbbc] border-t-[#8c8c8c]"></span>
        </div>
    </div>

    <div dir="rtl"
         class="relative my-auto hidden min-h-[379px] max-h-[calc(100vh-32px)] w-[498px] max-w-full flex-col overflow-y-auto rounded-[10px] bg-white px-5 py-8 text-left sm:px-[40px] md:pl-[50px] md:pr-[40px]"
         data-review-thanks-panel>
        <button type="button"
                data-review-thanks-close
                aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>

        <div dir="ltr" class="mb-4">
            <div class="flex h-[52px] items-center justify-center [&_svg]:!h-[52px] [&_svg]:!w-[194px]">
                @include('products.icons.my-shop')
            </div>
            <div class="mt-10 text-center">
                <h2 id="review-thanks-title" class="mb-4 text-[28px] font-bold leading-tight text-black">Спасибо!</h2>
                <p class="text-[15px] leading-snug text-black">
                    Благодаря Вам другие покупатели смогут быстрее найти то, что им нужно. Модерация займет до 3 рабочих дней.
                    По срочным вопросам — пишите на 275@5element.by или звоните на 275.
                </p>
            </div>
        </div>

        <div dir="ltr" class="mt-auto w-full">
            <button type="button"
                    data-review-thanks-close
                    class="h-12 w-full cursor-pointer rounded-[10px] border border-[#dc092e] px-[14px] py-3 text-center text-[15px] font-semibold text-[#dc092e] transition-colors duration-200 hover:bg-[#dc092e] hover:text-white focus:bg-[#a80723] focus:text-white focus:font-bold">
                OK
            </button>
        </div>

    </div>
</div>
