{{-- Модальное окно для поля изменения номера телефона --}}

<div class="popup-modal">
    <div class="flex w-[415px] h-[312px]px-[50px] py-8 rounded-[10px] bg-white">
        <svg class="icon popup-modal__close-icon"
            style="width: 31px; height: 31px;">
            <use xlink:href="/local/templates/html/assets/images/sprite.svg?v=1792606639#close-modal"></use>
        </svg>
        <div class="popup-modal_body flex flex-col gap-6">
            <div class="popup-modal__title text-6 font-bold">Привязать новый телефон</div>
            <div class="popup-modal__description">
                <div data-v-b50d4100="" class="change-contact-modal__form"><!---->
                    <div data-v-b50d4100="" class="change-contact-modal__input">
                        <label data-v-b50d4100="" for="phone" class="change-contact-modal__label">
                            <span data-v-b50d4100="" class="text-[#231F20] text-[15px]">
                                Телефон <span data-v-b50d4100="" class="required font-bold text-[#dc092e]"> * </span>
                            </span>
                        </label>
                        <div data-v-2b0d276e="" data-v-b50d4100="" class="" name="phone"><!---->
                            <div data-v-2b0d276e="" class="phone-input-wrapper"><!---->
                                <input data-v-2b0d276e="" data-maska="+375 (X) ##-##-##" autocomplete="tel" inputmode="tel" class="inp rounded-[10px] border border-[#bdbcbd] 
                                    placeholder:text-[#bdbcbd] text-[14px] text-left text-[#231F20] outline-none focus:border-[#231F20] 
                                    transition-colors peer-[:not(:placeholder-shown)]:bg-[#e8f0fe]
                                    w-full h-[50px]" 
                                    id="phone" type="text" name="phone" placeholder="+375 (__) ___-__-__">
                            </div><!---->
                        </div><!---->
                    </div><!----><!---->
                </div>
            </div>
            <div class="popup-modal__footer">
                <div data-v-b50d4100="" class="change-contact-modal__footer mt-2">
                    <button data-v-b50d4100="" type="submit" disabled 
                    class="btn btn-primary change-contact-modal__btn h-[50px] w-full rounded-[10px] border border-[#bdbbbc] bg-[#bdbbbc] text-center 
                        text-[15px] text-white font-semibold transition-colors duration-200 disabled:cursor-not-allowed enabled:cursor-pointer enabled:hover:bg-[#a80723]"> 
                        ОК 
                    </button>
                    <div data-v-b50d4100="" class="change-contact-modal__extra mt-6 text-[14px] text-[#231F20]">
                        <span data-v-b50d4100="" class="required font-bold text-[#dc092e]">*</span>
                        Поля обязательные для заполнения 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

---------------------------------------------------------------------------------------------------------------------------------

{{-- Модальное окно для поля изменения адреса Email --}}

<div class="popup-modal">
    <div class="flex w-[415px] h-[312px]px-[50px] py-8 rounded-[10px] bg-white">
        <svg class="icon popup-modal__close-icon"
            style="width: 31px; height: 31px;">
            <use xlink:href="/local/templates/html/assets/images/sprite.svg?v=1792606639#close-modal"></use>
        </svg>
        <div class="popup-modal_body flex flex-col gap-6">
            <div class="popup-modal__title text-6 font-bold">Привязать Email</div>
            <div class="popup-modal__description">
                <div data-v-b50d4100="" class="change-contact-modal__form"><!---->
                    <div data-v-b50d4100="" class="change-contact-modal__input">
                        <label data-v-b50d4100="" for="email" class="change-contact-modal__label">
                            <span data-v-b50d4100="" class="text-[#231F20] text-[15px]">
                                Email <span data-v-b50d4100="" class="required font-bold text-[#dc092e]"> * </span>
                            </span>
                        </label>
                        <div data-v-2b0d276e="" data-v-b50d4100="" class="" name="email"><!---->
                            <div data-v-2b0d276e="" class="email-input-wrapper"><!---->
                                <input data-v-2b0d276e="" data-maska="+375 (X) ##-##-##" autocomplete="email" inputmode="email" class="inp rounded-[10px] border border-[#bdbcbd] 
                                    bg-white text-[14px] text-left text-[#231F20] outline-none focus:border-[#231F20] 
                                    transition-colors valid:bg-[#e8f0fe]
                                    w-full h-[50px]" 
                                    id="email" type="email" name="email">
                            </div><!---->
                        </div><!---->
                    </div><!----><!---->
                </div>
            </div>
            <div class="popup-modal__footer">
                <div data-v-b50d4100="" class="change-contact-modal__footer mt-2">
                    <button data-v-b50d4100="" type="submit" disabled 
                    class="btn btn-primary change-contact-modal__btn h-[50px] w-full rounded-[10px] border border-[#bdbbbc] bg-[#bdbbbc] text-center 
                        text-[15px] text-white font-semibold transition-colors duration-200 disabled:cursor-not-allowed enabled:cursor-pointer enabled:hover:bg-[#a80723]"> 
                        ОК 
                    </button>
                    <div data-v-b50d4100="" class="change-contact-modal__extra mt-6 text-[14px] text-[#231F20]">
                        <span data-v-b50d4100="" class="required font-bold text-[#dc092e]">*</span>
                        Поля обязательные для заполнения 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

