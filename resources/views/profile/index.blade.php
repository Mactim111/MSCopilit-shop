@extends('layouts.main')

@section('title', 'Личные данные')

@section('content')
    <div class="mx-auto w-full max-w-[1500px] px-4 md:px-0">
        <nav aria-label="Хлебные крошки" class="py-6 text-[13px] text-[#7b7979]">
            <a href="{{ route('home') }}" class="hover:text-[#231f20]">Главная</a>
            <span class="mx-1">·</span>
            <span>Личный кабинет</span>
            <span class="mx-1">·</span>
            <span data-profile-breadcrumb>Личные данные</span>
        </nav>

        <div class="mb-[70px] flex flex-col items-start gap-6 md:flex-row">
            <x-profile-sidebar active="personal" :tab-mode="true" />

            <main class="w-full min-w-0 max-w-[1152px] flex-1">
                <section data-profile-panel="personal"
                         class="w-full rounded-[10px] border border-[#e5e5e5] p-5 text-left md:p-8">
                    <h1 class="mb-8 text-left text-[15px] font-semibold text-black">Личные данные</h1>

                    <form action="{{ route('profile.update') }}"
                          method="POST"
                          class="text-left"
                          data-profile-personal-form>
                        @csrf
                        <div class="mb-8 grid grid-cols-1 gap-x-[14px] gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="max-w-[250px]">
                                <label for="profile-name" class="mb-3 block text-[13px] text-black">
                                    Имя <span class="text-[#DC092E]">*</span>
                                </label>
                                <input id="profile-name" name="name" required maxlength="255"
                                       value="{{ old('name', $user->name) }}"
                                       class="h-[36px] w-full rounded-[10px] border border-[#bdbbbc] bg-white px-4 pt-[8px] pb-3 text-left text-[14px] text-black outline-none focus:border-black"
                                       data-profile-editable>
                                <p class="mt-1 hidden text-[12px] text-[#dc092e]" data-profile-field-error="name"></p>
                            </div>

                            <div class="max-w-[250px]">
                                <span class="mb-3 block text-[13px] text-black">
                                    Пол <span class="text-[#DC092E]">*</span>
                                </span>
                                <div class="flex h-[36px] items-center gap-4" role="radiogroup" aria-label="Пол">
                                    <label class="flex cursor-pointer items-center gap-2 text-[15px] text-black">
                                        <input class="peer sr-only" type="radio" name="gender" value="male"
                                               @checked(old('gender', $user->gender) === 'male') required data-profile-editable>
                                        <span class="relative flex h-5 w-5 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block"></span>
                                        Мужской
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 text-[15px] text-black">
                                        <input class="peer sr-only" type="radio" name="gender" value="female"
                                               @checked(old('gender', $user->gender) === 'female') required data-profile-editable>
                                        <span class="relative flex h-5 w-5 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block"></span>
                                        Женский
                                    </label>
                                </div>
                                <p class="mt-1 hidden text-[12px] text-[#dc092e]" data-profile-field-error="gender"></p>
                            </div>

                            <div class="max-w-[250px]">
                                <div class="mb-3 flex items-center justify-between text-[13px] text-black">
                                    <label for="profile-phone">Телефон <span class="text-[#DC092E]">*</span></label>
                                    <a href="#" class="text-[14px] text-[#007EFF] transition-all duration-200 hover:text-[#0064cc]">Изменить</a>
                                </div>
                                <div class="relative">
                                    <input id="profile-phone" value="{{ $user->phone }}" readonly
                                           class="h-[36px] w-full cursor-not-allowed rounded-[10px] border border-[#bdbbbc] bg-white px-4 py-3 pr-9 text-left text-[14px] text-[#9f9d9e] outline-none">
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                                        @include('products.icons.stop')
                                    </span>
                                </div>
                            </div>

                            <div class="max-w-[250px]">
                                <label for="profile-last-name" class="mb-3 block text-[13px] text-black">Фамилия</label>
                                <input id="profile-last-name" name="last_name" maxlength="255"
                                       value="{{ old('last_name', $user->last_name) }}"
                                       class="h-[36px] w-full rounded-[10px] border border-[#bdbbbc] bg-white px-4 pt-[8px] pb-3 text-left text-[14px] text-black outline-none focus:border-black"
                                       data-profile-editable>
                                <p class="mt-1 hidden text-[12px] text-[#dc092e]" data-profile-field-error="last_name"></p>
                            </div>

                            <div class="max-w-[250px]">
                                <div class="mb-3 flex items-center justify-between text-[13px] text-black">
                                    <label for="profile-birth-date">Дата рождения</label>
                                    <button type="button"
                                            class="{{ $user->birth_date ? '' : 'hidden' }} text-[14px] text-[#007EFF] transition-all duration-200 hover:text-[#0064cc]"
                                            data-birthday-clear>
                                        Удалить
                                    </button>
                                </div>
                                <div class="relative">
                                    <input id="profile-birth-date" name="birth_date" readonly
                                           value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"
                                           placeholder="дд.мм.гггг"
                                           class="h-[36px] w-full cursor-pointer rounded-[10px] border border-[#bdbbbc] bg-white px-4 pt-[8px] pb-3 pr-10 text-left text-[14px] text-black placeholder:text-[#9f9d9e] outline-none focus:border-black"
                                           data-birthday-input data-profile-editable>
                                    <span class="pointer-events-none absolute right-[14px] top-1/2 -translate-y-1/2">
                                        @include('products.icons.calendar')
                                    </span>
                                </div>
                                <p class="mt-1 hidden text-[12px] text-[#dc092e]" data-profile-field-error="birth_date"></p>
                            </div>

                            <div class="max-w-[250px]">
                                <div class="mb-3 flex items-center justify-between text-[13px] text-black">
                                    <label for="profile-email">Email <span class="text-[#DC092E]">*</span></label>
                                    <a href="#" class="text-[14px] text-[#007EFF] transition-all duration-200 hover:text-[#0064cc]">Изменить</a>
                                </div>
                                <div class="relative">
                                    <input id="profile-email" value="{{ $user->email }}" readonly
                                           class="h-[36px] w-full cursor-not-allowed rounded-[10px] border border-[#bdbbbc] bg-[#f2f2f2] px-4 py-3 pr-9 text-left text-[14px] text-[#9f9d9e] outline-none">
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                                        @include('products.icons.stop')
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="mb-4 hidden text-[13px] text-[#dc092e]" role="alert" data-profile-form-error></p>
                        <button type="submit" disabled
                                class="h-12 w-[207px] cursor-not-allowed rounded-[10px] border border-[#bdbbbc] bg-[#bdbbbc] text-center text-[15px] font-semibold text-white transition-colors duration-200 disabled:cursor-not-allowed enabled:cursor-pointer enabled:hover:bg-[#a80723]"
                                data-profile-save>
                            Сохранить
                        </button>
                    </form>
                </section>

                @foreach([
                    'bonuses' => 'Мои бонусы',
                    'notifications' => 'Настройки уведомлений',
                    'store' => 'Любимый магазин',
                    'addresses' => 'Адрес доставки',
                    'reviews' => 'Мои отзывы',
                ] as $tab => $title)
                    <section data-profile-panel="{{ $tab }}"
                             class="hidden w-full rounded-[10px] border border-[#e5e5e5] p-8 text-left">
                        <h1 class="mb-4 text-[15px] font-semibold text-black">{{ $title }}</h1>
                        <p class="text-[15px] text-[#8c8c8c]">Раздел будет добавлен позже.</p>
                    </section>
                @endforeach
            </main>
        </div>
    </div>

    <div class="fixed inset-0 z-[1200] hidden items-center justify-center bg-[#231f20bf] px-4 py-4"
         role="dialog" aria-modal="true" aria-labelledby="profile-save-success-title" data-profile-success-modal>
        <div class="relative flex h-[164px] w-[422px] max-w-full flex-col justify-center rounded-[10px] bg-white py-8 pl-[50px] pr-10">
            <button type="button" data-profile-success-close aria-label="Закрыть окно"
                    class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
                <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </button>
            <div class="flex w-full flex-col gap-4">
                <p id="profile-save-success-title" class="mb-4 text-center text-[15px] text-black">Данные успешно обновлены</p>
                <button type="button" data-profile-success-close
                        class="h-12 w-full cursor-pointer rounded-[10px] border border-[#dc092e] px-[14px] py-3 text-center text-[15px] font-semibold text-[#dc092e] transition-colors duration-200 hover:bg-[#dc092e] hover:text-white focus:bg-[#a80723] focus:text-white focus:font-bold">
                    OK
                </button>
            </div>
        </div>
    </div>

    <div class="fixed z-[100] hidden h-[268px] w-[261px] rounded-[3px] border border-[#d6d6d6] bg-white px-2 py-[6px] shadow-sm"
         data-birthday-calendar aria-label="Календарь даты рождения">
        <span class="absolute -top-[7px] left-1/2 hidden h-3 w-3 -translate-x-1/2 rotate-45 border-l border-t border-[#d6d6d6] bg-white"
              data-calendar-arrow="top"></span>
        <span class="absolute -bottom-[7px] left-1/2 hidden h-3 w-3 -translate-x-1/2 rotate-45 border-b border-r border-[#d6d6d6] bg-white"
              data-calendar-arrow="bottom"></span>
        <div class="relative z-[1] bg-white">
            <div class="mb-2 flex h-8 items-center justify-between">
                <button type="button" aria-label="Предыдущий месяц" data-calendar-prev
                        class="flex h-7 w-7 items-center justify-center text-[#8c8c8c] hover:text-black">
                    @include('products.icons.chevron-left-thin')
                </button>
                <div class="flex items-center gap-3 text-[12px]">
                    <span data-calendar-month></span><span data-calendar-year></span>
                </div>
                <button type="button" aria-label="Следующий месяц" data-calendar-next
                        class="flex h-7 w-7 items-center justify-center text-[#8c8c8c] hover:text-black">
                    @include('products.icons.chevron-right-thin')
                </button>
            </div>
            <div class="mb-1 grid grid-cols-7 text-center text-[10px] font-semibold text-[#231f20]">
                @foreach(['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс'] as $weekday)
                    <span class="py-1">{{ $weekday }}</span>
                @endforeach
            </div>
            <div class="grid grid-cols-7 text-center" data-calendar-days></div>
        </div>
    </div>
@endsection
