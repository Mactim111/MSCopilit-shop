<section class="relative hidden w-[448px] max-w-full rounded-[10px] bg-white px-8 py-8 text-[#231f20] sm:px-10"
         role="dialog"
         aria-modal="true"
         aria-labelledby="auth-login-title"
         data-auth-modal-panel="login">
    <button type="button"
            data-auth-modal-close
            aria-label="Закрыть окно"
            class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
        <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
        </svg>
    </button>

    <h2 id="auth-login-title" class="mb-6 text-center text-[24px] font-bold">Вход в аккаунт</h2>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="_auth_modal" value="login">
        <input type="hidden" name="_auth_return_to" value="{{ request()->getRequestUri() }}">

        <div>
            <label for="auth-login-email" class="mb-1 block text-[15px] font-medium">Email</label>
            <input id="auth-login-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('email') border-[#dc092e] @enderror">
            @error('email')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="auth-login-password" class="mb-1 block text-[15px] font-medium">Пароль</label>
            <input id="auth-login-password" type="password" name="password" required autocomplete="current-password"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <div class="flex items-center justify-between gap-3 text-[14px]">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Запомнить меня</span>
            </label>
            <button type="button" data-auth-modal-trigger="forgot-password" class="cursor-pointer text-[#dc092e] hover:underline">
                Забыли пароль
            </button>
        </div>

        <button type="submit"
                class="h-[48px] w-full cursor-pointer rounded-[10px] bg-[#dc092e] text-[15px] font-semibold text-white transition-colors hover:bg-[#a80723]">
            Войти
        </button>

        <p class="pt-1 text-center text-[14px]">
            Нет аккаунта?
            <button type="button" data-auth-modal-trigger="register" class="cursor-pointer text-[#dc092e] hover:underline">Регистрация</button>
        </p>
    </form>
</section>
