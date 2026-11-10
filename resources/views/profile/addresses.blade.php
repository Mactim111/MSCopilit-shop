@extends('layouts.main')

@section('title', 'Адрес доставки')

@section('content')

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
    <x-breadcrumbs :items="[
        ['title' => 'Главная', 'url' => route('home')],
        ['title' => 'Личный кабинет', 'url' => route('profile')],
        ['title' => 'Адрес доставки'],
    ]" />
</div>

{{-- Основная двухколоночная часть --}}
<div class="max-w-[1500px] mx-auto flex flex-col gap-6 mt-6 mb-[70px] md:flex-row">

        <x-profile-sidebar active="addresses" />

        {{-- Правая колонка: заголовок с тегами + список вариантов + пагинация --}}
        <main class="w-full max-w-[1152px] flex flex-col">
            <div class="flex" id="addresses-heading">
                <h1 class="h-[42px] flex items-center mb-6 mr-5 text-[26px] font-semibold leading-none text-[#231f20]">
                    Адрес доставки
                </h1>
            </div>

            {{-- Список адресов доставки пользователя. --}}
            @include('profile.partials.addresses-list')
        </main>

</div>

<div class="fixed inset-0 z-[1200] hidden items-center justify-center bg-[#231f20bf] px-4 py-4"
     role="dialog" aria-modal="true" aria-labelledby="address-success-title" data-address-success-modal>
    <div class="relative flex h-[188px] w-[432px] max-w-full flex-col justify-center rounded-[10px] bg-white py-8 pl-[50px] pr-10">
        <button type="button" data-address-success-close aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>
        <div class="flex w-full flex-col gap-4">
            <p id="address-success-title" class="mb-4 text-center text-[24px] font-bold text-[#231f20] text-nowrap">Данные успешно обновлены</p>
            <button type="button" data-address-success-close
                    class="h-[48px] w-full cursor-pointer rounded-[10px] border border-[#dc092e] bg-[#dc092e] px-[14px] py-3 text-center text-[15px] font-semibold text-white transition-colors duration-200 hover:bg-[#a80723]">
                OK
            </button>
        </div>
    </div>
</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const successModal = document.querySelector('[data-address-success-modal]');
    let lastFocusedElement = null;

    const closeSuccessModal = () => {
        successModal?.classList.add('hidden');
        successModal?.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        lastFocusedElement?.focus();
    };

    const showSuccessModal = () => {
        lastFocusedElement = document.activeElement;
        successModal?.classList.remove('hidden');
        successModal?.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        successModal?.querySelector('[data-address-success-close]')?.focus();
    };

    const submitAddressAction = async (form) => {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });
        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            throw new Error('Сервер вернул неожиданный ответ. Попробуйте повторить действие.');
        }

        const payload = await response.json();

        if (!response.ok) {
            const validationError = Object.values(payload.errors || {}).flat()[0];
            throw new Error(validationError || payload.message || 'Не удалось обновить адрес.');
        }

        const addressesList = document.getElementById('addresses-list');
        if (payload.addresses_html && addressesList) {
            addressesList.outerHTML = payload.addresses_html;
        }

        const modalTitle = successModal?.querySelector('#address-success-title');
        if (modalTitle && payload.message) {
            modalTitle.textContent = payload.message;
        }

        showSuccessModal();
    };

    document.addEventListener('change', (event) => {
        const checkbox = event.target.closest('[data-address-default-checkbox]');

        if (checkbox) {
            checkbox.closest('form')?.requestSubmit();
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-address-default-form], [data-address-delete-form]');

        if (!form) {
            return;
        }

        event.preventDefault();

        try {
            await submitAddressAction(form);
        } catch (error) {
            window.alert(error.message);
            if (form.matches('[data-address-default-form]')) {
                const checkbox = form.querySelector('[data-address-default-checkbox]');
                if (checkbox) checkbox.checked = !checkbox.checked;
            }
        }
    });

    successModal?.addEventListener('click', (event) => {
        if (event.target.closest('[data-address-success-close]') || event.target === successModal) {
            closeSuccessModal();
        }
    });

    successModal?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSuccessModal();
        }
    });
});
</script>