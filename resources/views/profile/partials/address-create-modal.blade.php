@php($addressCanPersist = $addressCanPersist ?? true)

<div id="address-create-modal"
     class="fixed inset-0 z-[1000] hidden items-center justify-center overflow-y-auto bg-[#231f20bf] p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="address-create-title"
     data-address-create-modal>
    <div class="relative my-auto flex max-h-[calc(100vh-32px)] w-[515px] max-w-full flex-col overflow-y-auto rounded-[10px] bg-white pt-16 pb-10 pl-6 pr-6 text-left sm:pl-12.5 sm:pr-10"
         data-address-form-panel>
        <button type="button"
                data-address-modal-close
                aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>

        <h2 id="address-create-title" class="mb-6 pr-8 text-center text-[34px] font-bold leading-tight text-black">
            Адрес доставки
        </h2>

        <form method="POST"
              action="{{ route('profile.address.store') }}"
              data-address-form
              data-address-can-persist="{{ $addressCanPersist ? 'true' : 'false' }}">
            @csrf
            <input type="hidden" name="_method" value="" data-address-method>
            <input type="hidden" name="is_default" value="0" data-address-default-value>

            <div class="flex flex-col gap-4">
                <div>
                    <label for="address-city" class="mb-1 block text-[13px] font-semibold text-[#231f20]">
                        Населенный пункт <span class="text-[#dc092e] font-semibold">*</span>
                    </label>
                    <div class="flex h-[44px] items-center rounded-[10px] border border-[#bdbcbd] px-4 focus-within:border-[#231f20]"
                         data-address-input-wrap>
                        <input id="address-city"
                               name="city"
                               type="text"
                               value="{{ old('city') }}"
                               maxlength="255"
                               pattern=".*\S.*"
                               required
                               data-address-required
                               class="w-full border-0 bg-transparent p-0 text-[15px] text-[#231f20] outline-none focus:border-0 focus:ring-0"
                               autocomplete="address-level2">
                    </div>
                </div>

                <div>
                    <label for="address-street" class="mb-1 block text-[13px] font-semibold text-[#231f20]">
                        Улица <span class="text-[#dc092e] font-semibold">*</span>
                    </label>
                    <div class="flex h-[44px] items-center rounded-[10px] border border-[#bdbcbd] px-4 focus-within:border-[#231f20]"
                         data-address-input-wrap>
                        <input id="address-street"
                               name="street"
                               type="text"
                               value="{{ old('street') }}"
                               maxlength="255"
                               pattern=".*\S.*"
                               required
                               data-address-required
                               class="w-full border-0 bg-transparent p-0 text-[15px] text-[#231f20] outline-none focus:border-0 focus:ring-0"
                               autocomplete="address-line1">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <label for="address-house" class="mb-1 block text-[13px] font-semibold text-[#231f20]">
                            Дом/Корпус <span class="text-[#dc092e] font-semibold">*</span>
                        </label>
                        <div class="flex h-[44px] items-center rounded-[10px] border border-[#bdbcbd] px-4 focus-within:border-[#231f20]"
                            data-address-input-wrap>
                            <input id="address-house"
                                name="house"
                                type="text"
                                value="{{ old('house') }}"
                                maxlength="255"
                                pattern=".*\S.*"
                                required
                                data-address-required
                                class="w-full border-0 bg-transparent p-0 text-[15px] text-[#231f20] outline-none focus:border-0 focus:ring-0"
                                autocomplete="address-line2">
                        </div>
                    </div>

                    <div>
                        <label for="address-apartment" class="mb-1 block text-[13px] font-semibold text-[#231f20]">
                            Квартира
                        </label>
                        <div class="flex h-[44px] items-center rounded-[10px] border border-[#bdbcbd] px-4 focus-within:border-[#231f20]"
                             data-address-input-wrap>
                            <input id="address-apartment"
                                name="apartment"
                                type="text"
                                value="{{ old('apartment') }}"
                                maxlength="255"
                                data-address-optional
                                class="w-full border-0 bg-transparent p-0 text-[15px] text-[#231f20] outline-none focus:border-0 focus:ring-0">
                        </div>
                    </div>
                    <div>
                        <label for="address-entrance" class="mb-1 block text-[13px] font-semibold text-[#231f20]">
                            Подъезд
                        </label>
                        <div class="flex h-[44px] items-center rounded-[10px] border border-[#bdbcbd] px-4 focus-within:border-[#231f20]"
                             data-address-input-wrap>
                            <input id="address-entrance"
                                name="entrance"
                                type="text"
                                value="{{ old('entrance') }}"
                                maxlength="255"
                                data-address-optional
                                class="w-full border-0 bg-transparent p-0 text-[15px] text-[#231f20] outline-none focus:border-0 focus:ring-0">
                        </div>
                    </div>
                    <div>
                        <label for="address-floor" class="mb-1 block text-[13px] font-semibold text-[#231f20]">
                            Этаж
                        </label>
                        <div class="flex h-[44px] items-center rounded-[10px] border border-[#bdbcbd] px-4 focus-within:border-[#231f20]"
                             data-address-input-wrap>
                            <input id="address-floor"
                                name="floor"
                                type="text"
                                value="{{ old('floor') }}"
                                maxlength="255"
                                data-address-optional
                                class="w-full border-0 bg-transparent p-0 text-[15px] text-[#231f20] outline-none focus:border-0 focus:ring-0">
                        </div>
                    </div>
                </div>

                @if($addressCanPersist)
                    <label class="flex cursor-pointer items-center gap-2 text-[15px] text-[#231f20]">
                        <input type="checkbox"
                               class="m-0 shrink-0"
                               data-address-default-checkbox>
                        Сделать адресом по умолчанию
                    </label>
                @endif

                <button type="submit"
                        disabled
                        data-address-save
                        class="block h-12 w-full mt-6 cursor-not-allowed rounded-[10px] border border-[#bdbbbc] bg-[#bdbbbc] text-center text-[15px] font-semibold text-white transition-colors duration-200 hover:border-[#a80723] hover:bg-[#a80723] disabled:cursor-not-allowed disabled:border-[#bdbbbc] disabled:bg-[#bdbbbc] disabled:hover:border-[#bdbbbc] disabled:hover:bg-[#bdbbbc]">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>
