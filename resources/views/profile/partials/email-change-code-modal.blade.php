@php
    $emailChangeCodeModalOpen = !$errors->has('email') && (
        session('email_change_step') === 'code'
        || ($emailChangeVerification && $emailChangeVerification->expires_at->isFuture())
    );
@endphp

<div class="fixed inset-0 z-[1300] {{ $emailChangeCodeModalOpen ? 'flex' : 'hidden' }} items-center justify-center bg-[#231f20bf] px-4 py-4"
        role="dialog" aria-modal="true" aria-labelledby="email-change-code-title"
        aria-hidden="{{ $emailChangeCodeModalOpen ? 'false' : 'true' }}"
        data-email-change-code-modal
        data-open-on-load="{{ $emailChangeCodeModalOpen ? 'true' : 'false' }}"
        data-resend-at="{{ $emailChangeVerification?->resend_available_at?->timestamp ?? 0 }}"
        data-attempts-exhausted="{{ ($emailChangeVerification?->attempts ?? 0) >= 5 ? 'true' : 'false' }}"
        data-resend-email="{{ $emailChangeVerification?->email ?? '' }}">
    <div class="relative w-full max-w-[512px] rounded-[10px] bg-white px-6 py-8 text-left sm:px-[50px]">
        <button type="button" data-email-change-code-close aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>
        <form action="{{ route('profile.email.verify') }}" method="POST" novalidate
                class="flex flex-col">
            @csrf
            <h2 id="email-change-code-title" class="mb-6 pr-8 text-[24px] font-bold text-[#231f20]">
                Введите код из email
            </h2>
            <p class="mb-6 flex flex-nowrap items-center gap-x-1 whitespace-nowrap text-[15px] text-[#231f20]">
                <span>Отправили на</span>
                <span class="font-medium">{{ $emailChangeVerification?->email }}</span>
                <button type="button" data-email-change-edit
                        class="cursor-pointer text-[#007eff] transition-colors hover:text-[#0064cc]">
                    Изменить
                </button>
            </p>
            <div class="mb-4 px-[72px]">
                <div class="border-b border-[#bdbcbd] focus-within:border-[#231f20]"
                        data-email-code-wrap>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                            maxlength="4" pattern="[0-9]{4}" required
                            value="{{ old('code') }}"
                            aria-label="Четырёхзначный код из письма"
                            class="h-[74px] w-full border-0 bg-transparent py-[14px] text-center text-[42px] font-normal text-[#231f20] caret-[#231f20] outline-none placeholder:text-transparent"
                            data-email-change-code>
                </div>
                <p class="mt-2 hidden text-[15px] leading-5 text-[#dc092e]"
                        role="alert" data-email-code-error>{{ $errors->first('code') }}</p>
            </div>
            <div class="mb-6 text-center text-[15px] leading-5 text-[#231f20]">
                <p>Нет кода? Проверьте папку спам</p>
                <div class="mt-1" aria-live="polite">
                    <span class="text-[#231f20]" data-email-code-countdown>
                        Запросить новый код через <span data-email-code-countdown-value></span>
                    </span>
                    <button type="button"
                            class="hidden cursor-pointer text-[#007eff] transition-colors hover:text-[#0064cc]"
                            data-email-code-resend>
                        Запросить новый код
                    </button>
                </div>
            </div>
            <button type="submit" disabled
                    class="h-[50px] w-full cursor-not-allowed rounded-[10px] border border-[#bdbbbc] bg-[#bdbbbc] text-center text-[15px] font-semibold text-white transition-colors duration-200 disabled:cursor-not-allowed enabled:cursor-pointer enabled:hover:bg-[#a80723]"
                    data-contact-submit data-email-code-submit>
                Подтвердить
            </button>
        </form>
    </div>
</div>
