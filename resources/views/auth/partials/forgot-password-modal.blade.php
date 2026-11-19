<section class="relative hidden w-[448px] max-w-full rounded-[10px] bg-white px-8 py-8 text-[#231f20] sm:px-10"
         role="dialog"
         aria-modal="true"
         aria-labelledby="auth-forgot-title"
         data-auth-modal-panel="forgot-password">
    <button type="button"
            data-auth-modal-close
            aria-label="Закрыть окно"
            class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
        <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
        </svg>
    </button>

    <h2 id="auth-forgot-title" class="mb-6 text-center text-[24px] font-bold">Восстановление пароля</h2>
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="_auth_modal" value="forgot-password">
        <input type="hidden" name="_auth_return_to" value="{{ request()->getRequestUri() }}">
        <div>
            <label for="auth-forgot-email" class="mb-1 block text-[15px] font-medium">Ваш Email</label>
            <input id="auth-forgot-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('email') border-[#dc092e] @enderror">
            @error('email')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>
        <button type="submit"
                class="h-[48px] w-full cursor-pointer rounded-[10px] bg-[#dc092e] text-[15px] font-semibold text-white transition-colors hover:bg-[#a80723]">
            Отправить ссылку
        </button>
        <p class="text-center text-[14px]">
            <button type="button" data-auth-modal-trigger="login" class="cursor-pointer text-[#dc092e] hover:underline">Назад ко входу</button>
        </p>
    </form>
</section>
