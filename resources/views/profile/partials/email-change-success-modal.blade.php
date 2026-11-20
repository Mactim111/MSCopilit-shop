<div class="fixed inset-0 z-[1300] {{ session('email_change_success') ? 'flex' : 'hidden' }} items-center justify-center bg-[#231f20bf] px-4 py-4"
        role="dialog" aria-modal="true" aria-labelledby="email-change-success-title"
        aria-hidden="{{ session('email_change_success') ? 'false' : 'true' }}"
        data-email-change-success-modal>
    <div class="relative w-full max-w-[432px] rounded-[10px] bg-white px-6 py-8 text-left sm:px-[50px]">
        <button type="button" data-email-change-success-close aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>
        <h2 id="email-change-success-title" class="mb-4 pr-8 text-[24px] font-bold text-[#231f20]">
            Спасибо!
        </h2>
        <p class="mb-8 text-[15px] text-[#231f20]">
            Вы успешно привязали новый Email
        </p>
        <button type="button" data-email-change-success-close
                class="h-[48px] w-full cursor-pointer rounded-[10px] border border-[#dc092e] bg-[#dc092e] px-[14px] py-3 text-center text-[15px] font-semibold text-white transition-colors duration-200 hover:border-[#a80723] hover:bg-[#a80723] focus:border-[#dc092e] focus:bg-[#a80723]">
            Продолжить
        </button>
    </div>
</div>
