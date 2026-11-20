<section class="relative my-auto hidden max-h-[calc(100vh-32px)] w-[448px] max-w-full overflow-y-auto rounded-[10px] bg-white px-7 py-8 text-[#231f20] sm:px-8"
         role="dialog"
         aria-modal="true"
         aria-labelledby="auth-register-title"
         data-auth-modal-panel="register">
    <button type="button"
            data-auth-modal-close
            aria-label="Закрыть окно"
            class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
        <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
        </svg>
    </button>

    <h2 id="auth-register-title" class="mb-5 text-center text-[24px] font-bold">Регистрация</h2>
    <form method="POST" action="{{ route('register') }}" class="space-y-3" novalidate data-auth-validation-form>
        @csrf
        <input type="hidden" name="_auth_modal" value="register">
        <input type="hidden" name="_auth_return_to" value="{{ session('auth_return_to', request()->getRequestUri()) }}">

        <div>
            <label for="auth-register-name" class="mb-1 block text-[15px] font-medium">Имя <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-name" type="text" name="name" value="{{ old('name') }}" maxlength="255" required autocomplete="given-name"
                   data-auth-field @if($errors->has('name')) data-auth-server-invalid @endif
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <div>
            <label for="auth-register-last-name" class="mb-1 block text-[15px] font-medium">Фамилия</label>
            <input id="auth-register-last-name" type="text" name="last_name" value="{{ old('last_name') }}" maxlength="255" autocomplete="family-name"
                   data-auth-field @if($errors->has('last_name')) data-auth-server-invalid @endif
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <fieldset>
            <legend class="mb-1 text-[15px] font-medium">Пол <span class="text-[#dc092e]">*</span></legend>
            <div class="flex h-[47px] items-center gap-4" role="radiogroup" aria-label="Пол"
                 data-auth-field data-auth-radio-group @if($errors->has('gender')) data-auth-server-invalid @endif>
                <label class="flex h-full cursor-pointer items-center gap-2 text-[15px] text-[#231f20]">
                    <input class="peer sr-only" type="radio" name="gender" value="male" @checked(old('gender', 'male') === 'male') required>
                    <span data-auth-radio-indicator class="relative flex h-5 w-5 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block peer-focus-visible:border-[#231f20]"></span>
                    Мужской
                </label>
                <label class="flex h-full cursor-pointer items-center gap-2 text-[15px] text-[#231f20]">
                    <input class="peer sr-only" type="radio" name="gender" value="female" @checked(old('gender') === 'female') required>
                    <span data-auth-radio-indicator class="relative flex h-5 w-5 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block peer-focus-visible:border-[#231f20]"></span>
                    Женский
                </label>
            </div>
        </fieldset>

        <div>
            <label for="auth-register-birth-date" class="mb-1 block text-[15px] font-medium">Дата рождения</label>
            <input id="auth-register-birth-date" type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ now()->toDateString() }}"
                   data-auth-field @if($errors->has('birth_date')) data-auth-server-invalid @endif
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <div>
            <label for="auth-register-phone" class="mb-1 block text-[15px] font-medium">Телефон <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="50" required autocomplete="tel" placeholder="+375..."
                   data-auth-field @if($errors->has('phone')) data-auth-server-invalid @endif
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <div>
            <label for="auth-register-email" class="mb-1 block text-[15px] font-medium">Email <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   data-auth-field @if($errors->has('email')) data-auth-server-invalid @endif
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <div>
            <label for="auth-register-password" class="mb-1 block text-[15px] font-medium">Пароль <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-password" type="password" name="password" required minlength="8" autocomplete="new-password"
                   data-auth-field data-auth-password
                   @if($errors->has('password')) data-auth-server-invalid @endif
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <div>
            <label for="auth-register-password-confirmation" class="mb-1 block text-[15px] font-medium">Повторите пароль <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   data-auth-field data-auth-password-confirmation
                   @if($errors->has('password')) data-auth-server-invalid @endif
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <button type="submit"
                disabled
                data-auth-submit
                class="h-[48px] w-full cursor-not-allowed rounded-[10px] border border-[#bdbbbc] bg-[#bdbbbc] text-[15px] font-semibold text-white transition-colors duration-200 disabled:cursor-not-allowed disabled:border-[#bdbbbc] disabled:bg-[#bdbbbc] disabled:hover:border-[#bdbbbc] disabled:hover:bg-[#bdbbbc] enabled:cursor-pointer enabled:border-[#dc092e] enabled:bg-[#dc092e] enabled:hover:border-[#a80723] enabled:hover:bg-[#a80723]">
            Создать аккаунт
        </button>

        <p class="pt-1 text-center text-[14px]">
            Уже есть аккаунт?
            <button type="button" data-auth-modal-trigger="login" class="cursor-pointer text-[#dc092e] hover:underline">Войти</button>
        </p>
    </form>
</section>
