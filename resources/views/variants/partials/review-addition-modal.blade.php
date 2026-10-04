<div id="review-addition-modal"
     class="fixed inset-0 z-[1010] hidden items-center justify-center overflow-y-auto bg-[#231f20bf] p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="review-addition-title"
     data-review-addition-modal>
    <div class="relative flex h-[327px] w-[404px] max-w-full flex-col overflow-hidden rounded-[10px] bg-white py-8 pl-[50px] pr-10 text-left">
        <button type="button"
                data-review-addition-close
                aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>

        <div class="flex h-full flex-col gap-6">
            <h2 id="review-addition-title" class="pr-8 text-left text-[20px] font-bold leading-tight text-black">
                Дополнение комментария
            </h2>

            <form method="POST"
                  class="flex min-h-0 flex-1 flex-col"
                  data-review-addition-form>
                @csrf
                @method('PATCH')
                <label for="review-addition-comment"
                       class="mb-[5px] block pl-px text-left text-[13px] font-semibold text-black">
                    Комментарий<span class="text-[#DC092E]">*</span>
                </label>
                <textarea id="review-addition-comment"
                          name="addition"
                          rows="3"
                          maxlength="10000"
                          required
                          class="mb-4 block min-h-0 w-full flex-1 resize-none rounded-[10px] border border-[#8c8c8c] bg-white px-4 pt-[14px] pb-[12px] text-[14px] leading-5 text-black outline-none focus:border-[#555]"
                          data-review-addition-input></textarea>
                <button type="submit"
                        disabled
                        class="block w-full cursor-not-allowed rounded-[10px] border border-[#bdbbbc] bg-[#bdbbbc] px-5 py-2 text-center text-[15px] font-semibold text-white transition-colors duration-200 disabled:cursor-not-allowed enabled:cursor-pointer enabled:hover:bg-[#a80723] enabled:focus:bg-[#a80723]"
                        data-review-addition-submit>
                    Дополнить
                </button>
            </form>
        </div>
    </div>
</div>
