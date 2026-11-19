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
    <form method="POST" action="{{ route('register') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="_auth_modal" value="register">
        <input type="hidden" name="_auth_return_to" value="{{ request()->getRequestUri() }}">

        <div>
            <label for="auth-register-name" class="mb-1 block text-[15px] font-medium">Имя <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="given-name"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('name') border-[#dc092e] @enderror">
            @error('name')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="auth-register-last-name" class="mb-1 block text-[15px] font-medium">Фамилия</label>
            <input id="auth-register-last-name" type="text" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('last_name') border-[#dc092e] @enderror">
            @error('last_name')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <fieldset>
            <legend class="mb-1 text-[15px] font-medium">Пол <span class="text-[#dc092e]">*</span></legend>
            <div class="flex h-[47px] items-center gap-4" role="radiogroup" aria-label="Пол">
                <label class="flex h-full cursor-pointer items-center gap-2 text-[15px] text-[#231f20]">
                    <input class="peer sr-only" type="radio" name="gender" value="male" @checked(old('gender', 'male') === 'male') required>
                    <span class="relative flex h-5 w-5 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block"></span>
                    Мужской
                </label>
                <label class="flex h-full cursor-pointer items-center gap-2 text-[15px] text-[#231f20]">
                    <input class="peer sr-only" type="radio" name="gender" value="female" @checked(old('gender') === 'female') required>
                    <span class="relative flex h-5 w-5 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block"></span>
                    Женский
                </label>
            </div>
            @error('gender')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </fieldset>

        <div>
            <label for="auth-register-birth-date" class="mb-1 block text-[15px] font-medium">Дата рождения</label>
            <input id="auth-register-birth-date" type="date" name="birth_date" value="{{ old('birth_date') }}"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('birth_date') border-[#dc092e] @enderror">
            @error('birth_date')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="auth-register-phone" class="mb-1 block text-[15px] font-medium">Телефон <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+375..."
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('phone') border-[#dc092e] @enderror">
            @error('phone')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="auth-register-email" class="mb-1 block text-[15px] font-medium">Email <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('email') border-[#dc092e] @enderror">
            @error('email')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="auth-register-password" class="mb-1 block text-[15px] font-medium">Пароль <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-password" type="password" name="password" required autocomplete="new-password"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20] @error('password') border-[#dc092e] @enderror">
            @error('password')<p class="mt-1 text-[13px] text-[#dc092e]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="auth-register-password-confirmation" class="mb-1 block text-[15px] font-medium">Повторите пароль <span class="text-[#dc092e]">*</span></label>
            <input id="auth-register-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="h-[42px] w-full rounded-[10px] border border-[#bdbcbd] px-3 text-[15px] outline-none focus:border-[#231f20]">
        </div>

        <button type="submit"
                class="h-[48px] w-full cursor-pointer rounded-[10px] bg-[#dc092e] text-[15px] font-semibold text-white transition-colors hover:bg-[#a80723]">
            Создать аккаунт
        </button>

        <p class="pt-1 text-center text-[14px]">
            Уже есть аккаунт?
            <button type="button" data-auth-modal-trigger="login" class="cursor-pointer text-[#dc092e] hover:underline">Войти</button>
        </p>
    </form>
</section>
