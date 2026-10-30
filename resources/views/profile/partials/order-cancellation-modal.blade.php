<div data-order-cancellation-modal
     class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/60 px-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="order-cancellation-title">
    <div class="relative w-full max-w-[426px] rounded-[10px] bg-white px-8 py-10 text-center shadow-xl">
        <button type="button"
                data-order-cancellation-close
                aria-label="Закрыть окно"
                class="absolute right-4 top-3 cursor-pointer text-[28px] leading-none text-[#231f20]">
            &times;
        </button>

        <div data-order-cancellation-confirmation>
            <p id="order-cancellation-title" class="mb-8 text-[15px] text-[#231f20]">
                Вы действительно хотите отправить запрос на отмену заказа?
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <button type="button"
                        data-order-cancellation-dismiss
                        class="min-h-[50px] rounded-[10px] border border-[#dc092e] px-6 text-[15px] text-[#dc092e] hover:bg-[#a80723] hover:border-[#a80723] 
                        hover:font-semibold hover:text-white transition-colors duration-200">
                    Нет, спасибо
                </button>
                <button type="button"
                        data-order-cancellation-confirm
                        class="min-h-[50px] rounded-[10px] border border-[#dc092e] px-6 text-[15px] text-[#dc092e] hover:bg-[#a80723] hover:border-[#a80723]
                        hover:font-semibold hover:text-white transition-colors duration-200">
                    Да, отправить запрос
                </button>
            </div>
        </div>

        <div data-order-cancellation-success class="hidden">
            <p class="mb-8 text-[24x] font-bold text-[#231f20]">
                Запрос на отмену отправлен. После подтверждения менеджером статус заказа изменится на «Отменён».
            </p>
            <button type="button"
                    data-order-cancellation-acknowledge
                    class="min-h-[48px] w-full rounded-[10px] bg-[#dc092e] hover:bg-[#a80723] text-[15px] font-semibold text-white transition-colors duration-200">
                OK
            </button>
        </div>
    </div>
</div>
