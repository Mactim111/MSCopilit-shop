<section class="relative hidden w-[448px] max-w-full rounded-[10px] bg-white px-8 py-8 text-[#231f20] sm:px-10"
         role="dialog"
         aria-modal="true"
         aria-labelledby="auth-reset-title"
         data-auth-modal-panel="reset-password">
    <button type="button"
            data-auth-modal-close
            aria-label="Закрыть окно"
            class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
        <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
        </svg>
    </button>

    <h2 id="auth-reset-title" class="mb-6 text-center text-[24px] font-bold">Новый пароль</h2>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="_auth_modal" value="reset-password">
        <input type="hidden" name="_auth_return_to" value="{{ request()->getRequestUri() }}">
        <input type="hidden" name="token" value="{{ old('token', session('auth_modal_token', '')) }}">

        <div>
            <label for="auth-reset-email" class="mb-1 block text-[15px] font-medium">Email</label>
            <input id="auth-reset-email" type="email" name="email" value="{{ old('email', session('auth_modal_email', '')) }}" required autocomplete="email"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('email') border-[#dc092e] @enderror">
            @error('email')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="auth-reset-password" class="mb-1 block text-[15px] font-medium">Новый пароль</label>
            <input id="auth-reset-password" type="password" name="password" required autocomplete="new-password"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('password') border-[#dc092e] @enderror">
            @error('password')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="auth-reset-password-confirmation" class="mb-1 block text-[15px] font-medium">Повторите пароль</label>
            <input id="auth-reset-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <button type="submit"
                class="h-[48px] w-full cursor-pointer rounded-[10px] bg-[#dc092e] text-[15px] font-semibold text-white transition-colors hover:bg-[#a80723]">
            Сохранить пароль
        </button>
    </form>
</section>
