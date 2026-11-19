<section tabindex="-1"
         role="dialog"
         aria-modal="true"
         aria-labelledby="auth-verify-title"
         class="relative hidden w-[448px] max-w-full rounded-[10px] bg-white px-8 py-8 text-[#231f20] outline-none sm:px-10"
         data-auth-modal-panel="verify-email">
    <button type="button"
            data-auth-modal-close
            aria-label="Закрыть окно"
            class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
        <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
        </svg>
    </button>

    <h2 id="auth-verify-title" class="mb-5 pr-8 text-center text-[24px] font-bold">Подтвердите email</h2>
    <p class="mb-5 text-center text-[15px] leading-6 text-[#231f20]">
        Мы отправили письмо с подтверждением на ваш email. Перейдите по ссылке в письме, чтобы активировать аккаунт.
    </p>
    <form method="POST" action="{{ route('verification.send') }}" class="text-center">
        @csrf
        <button type="submit"
                class="h-[48px] w-full cursor-pointer rounded-[10px] bg-[#dc092e] text-[15px] font-semibold text-white transition-colors hover:bg-[#a80723]">
            Отправить повторно
        </button>
    </form>
</section>
