<div id="review-action-result-modal"
     class="fixed inset-0 z-[1020] hidden items-center justify-center bg-[#231f20bf] px-4 py-4"
     role="dialog"
     aria-modal="true"
     aria-live="assertive"
     data-review-action-result-modal>
    <div class="relative flex h-[264px] w-[290px] max-w-full flex-col justify-center rounded-[10px] bg-white py-8 pl-[50px] pr-10 text-left"
         data-review-action-result-panel>
        <button type="button"
                data-review-result-close
                aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>

        <div class="flex w-full flex-col gap-4">
            <p class="mb-4 text-center text-[15px] leading-[18px] text-black"
               data-review-result-message></p>
            <button type="button"
                    data-review-result-close
                    class="h-12 w-full cursor-pointer rounded-[10px] border border-[#dc092e] px-[14px] py-3 text-center text-[15px] font-semibold text-[#dc092e] transition-colors duration-200 hover:bg-[#dc092e] hover:text-white focus:bg-[#a80723] focus:text-white focus:font-bold">
                OK
            </button>
        </div>
    </div>
</div>
