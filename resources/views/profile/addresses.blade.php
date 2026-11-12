@extends('layouts.main')

@section('title', 'Адрес доставки')

@section('content')
<div class="mx-auto flex max-w-[1500px] items-center py-[24px] text-[13px] text-[#7b7979]">
    <x-breadcrumbs :items="[
        ['title' => 'Главная', 'url' => route('home')],
        ['title' => 'Личный кабинет', 'url' => route('profile')],
        ['title' => 'Адрес доставки'],
    ]" />
</div>

<div class="mx-auto mt-6 mb-[70px] flex max-w-[1500px] flex-col gap-6 md:flex-row">
    <x-profile-sidebar active="addresses" />

    <main class="flex w-full max-w-[1152px] flex-col">
        <div class="flex" id="addresses-heading">
            <h1 class="mb-6 mr-5 flex h-[42px] items-center text-[26px] font-semibold leading-none text-[#231f20]">
                Адрес доставки
            </h1>
        </div>
        @include('profile.partials.addresses-list')
    </main>
</div>

@include('profile.partials.address-create-modal')

<div class="fixed inset-0 z-[1100] hidden items-center justify-center bg-[#231f20bf] px-4 py-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="address-delete-title"
     data-address-delete-modal>
    <div class="relative flex min-h-[225px] w-[390px] max-w-full flex-col justify-center rounded-[10px] bg-white py-8 pl-6 pr-6 text-left sm:pl-12.5 sm:pr-10">
        <div class="flex flex-col gap-6">
            <h2 id="address-delete-title" class="text-[24px] font-bold text-black">Удалить адрес?</h2>
            <p class="mb-[6px] text-[15px] text-black">Вы действительно хотите удалить этот адрес доставки?</p>
            <div class="flex gap-[10px]">
                <button type="button"
                        data-address-delete-cancel
                        class="h-10 w-1/2 cursor-pointer rounded-[8px] border border-[#dc092e] text-center text-[15px] font-semibold text-[#dc092e] transition-colors focus:bg-[#dc092e] focus:text-white">
                    Нет
                </button>
                <button type="button"
                        data-address-delete-confirm
                        class="h-10 w-1/2 cursor-pointer rounded-[8px] border border-[#dc092e] text-center text-[15px] font-semibold text-[#dc092e] transition-colors focus:bg-[#dc092e] focus:text-white">
                    Да
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-[1200] hidden items-center justify-center bg-[#231f20bf] px-4 py-4"
     role="dialog"
     aria-modal="true"
     aria-live="polite"
     aria-labelledby="address-result-message"
     data-address-result-modal>
    <div class="relative flex h-[188px] w-[432px] max-w-full flex-col justify-center rounded-[10px] bg-white py-8 pl-6 pr-6 sm:pl-[50px] sm:pr-10">
        <button type="button"
                data-address-result-close
                aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>
        <div class="flex w-full flex-col gap-4">
            <p id="address-result-message" class="mb-4 text-center text-[24px] font-bold text-[#231f20]">
                Данные успешно обновлены
            </p>
            <button type="button"
                    data-address-result-close
                    class="h-[48px] w-full cursor-pointer rounded-[10px] border border-[#dc092e] bg-[#dc092e] px-[14px] py-3 text-center text-[15px] font-semibold text-white transition-colors duration-200 hover:bg-[#a80723]">
                OK
            </button>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const addressModal = document.querySelector('[data-address-create-modal]');
    const addressForm = addressModal?.querySelector('[data-address-form]');
    const saveButton = addressModal?.querySelector('[data-address-save]');
    const methodInput = addressModal?.querySelector('[data-address-method]');
    const defaultCheckbox = addressModal?.querySelector('[data-address-default-checkbox]');
    const defaultValue = addressModal?.querySelector('[data-address-default-value]');
    const deleteModal = document.querySelector('[data-address-delete-modal]');
    const resultModal = document.querySelector('[data-address-result-modal]');
    let activeTrigger = null;
    let pendingDeleteForm = null;
    let confirmedDefaultRadio = document.querySelector('[data-address-default-radio]:checked');
    let isSubmitting = false;
    let revealRequiredErrors = false;

    const syncScrollLock = () => {
        const hasOpenModal = [addressModal, deleteModal, resultModal]
            .some(modal => modal && !modal.classList.contains('hidden'));
        document.body.classList.toggle('overflow-hidden', hasOpenModal);
    };

    const showModal = modal => {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        syncScrollLock();
    };

    const hideModal = modal => {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        syncScrollLock();
    };

    const showResult = (message, isError = false) => {
        const resultMessage = resultModal?.querySelector('#address-result-message');
        if (resultMessage) {
            resultMessage.textContent = message;
            resultMessage.classList.toggle('text-[24px]', !isError);
            resultMessage.classList.toggle('font-bold', !isError);
            resultMessage.classList.toggle('text-[15px]', isError);
        }
        showModal(resultModal);
        resultModal?.querySelector('[data-address-result-close]')?.focus();
    };

    const updateFormState = () => {
        if (!addressForm || !saveButton || !defaultCheckbox || !defaultValue) return;

        defaultValue.value = defaultCheckbox.checked ? '1' : '0';
        const addressFields = [...addressForm.querySelectorAll('[data-address-required], [data-address-optional]')];
        addressFields.forEach(field => {
            const shouldShowError = !field.validity.valid
                && (
                    revealRequiredErrors
                    || field.dataset.addressTouched === 'true'
                    || field.getAttribute('aria-invalid') === 'true'
                );
            field.closest('[data-address-input-wrap]')?.classList.toggle('is-invalid', shouldShowError);
        });

        const isValid = [...addressForm.elements].every(field => !field.willValidate || field.validity.valid);
        saveButton.disabled = !isValid || isSubmitting;
        saveButton.classList.toggle('cursor-not-allowed', saveButton.disabled);
        saveButton.classList.toggle('border-[#bdbbbc]', saveButton.disabled);
        saveButton.classList.toggle('bg-[#bdbbbc]', saveButton.disabled);
        saveButton.classList.toggle('cursor-pointer', !saveButton.disabled);
        saveButton.classList.toggle('border-[#dc092e]', !saveButton.disabled);
        saveButton.classList.toggle('bg-[#dc092e]', !saveButton.disabled);
    };

    const openAddressForm = (trigger, address = null) => {
        if (!addressForm || !methodInput || !defaultCheckbox || !defaultValue) return;

        activeTrigger = trigger;
        revealRequiredErrors = false;
        addressForm.reset();
        addressForm.querySelectorAll('[data-address-required], [data-address-optional]').forEach(field => {
            field.setCustomValidity('');
            field.removeAttribute('aria-invalid');
            delete field.dataset.addressTouched;
            field.closest('[data-address-input-wrap]')?.classList.remove('is-invalid');
        });

        if (address) {
            addressForm.action = trigger.dataset.addressUpdateUrl;
            methodInput.value = 'PUT';
            for (const field of addressForm.querySelectorAll('[name]')) {
                if (Object.hasOwn(address, field.name)) field.value = address[field.name] ?? '';
            }
            defaultCheckbox.checked = trigger.dataset.isDefault === '1';
            defaultCheckbox.disabled = false;
        } else {
            addressForm.action = addressForm.dataset.storeUrl;
            methodInput.value = '';
            defaultCheckbox.checked = false;
            defaultCheckbox.disabled = false;
        }

        updateFormState();
        showModal(addressModal);
        addressModal.querySelector('[name="city"]')?.focus();
    };

    const applyUpdatedAddresses = payload => {
        const addressesList = document.getElementById('addresses-list');
        if (payload.addresses_html && addressesList) {
            addressesList.outerHTML = payload.addresses_html;
        }
        confirmedDefaultRadio = document.querySelector('[data-address-default-radio]:checked');
    };

    const sendForm = async form => {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: new FormData(form),
        });
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            throw new Error('Сервер вернул неожиданный ответ. Повторите попытку.');
        }

        const payload = await response.json();
        if (!response.ok) {
            if (form === addressForm && response.status === 422) {
                const errors = payload.errors || {};
                revealRequiredErrors = true;
                Object.keys(errors).forEach(name => {
                    const field = addressForm.elements.namedItem(name);
                    if (!(field instanceof HTMLInputElement)) return;
                    field.setCustomValidity(' ');
                    field.setAttribute('aria-invalid', 'true');
                    field.closest('[data-address-input-wrap]')?.classList.add('is-invalid');
                });
                updateFormState();
                return null;
            }
            throw new Error(payload.message || 'Не удалось выполнить действие с адресом.');
        }

        applyUpdatedAddresses(payload);
        return payload;
    };

    const submitSimpleAction = async form => {
        if (isSubmitting) return;
        isSubmitting = true;
        try {
            const payload = await sendForm(form);
            if (payload) {
                hideModal(deleteModal);
                showResult(payload.message || 'Данные успешно обновлены');
            } else {
                showResult('Не удалось выполнить действие. Проверьте данные и повторите попытку.', true);
                if (form.matches('[data-address-default-form]')) {
                    document.querySelectorAll('[data-address-default-radio]').forEach(radio => {
                        radio.checked = radio === confirmedDefaultRadio;
                    });
                }
            }
        } catch (error) {
            showResult(error.message || 'Не удалось выполнить действие с адресом.', true);
            if (form.matches('[data-address-default-form]')) {
                document.querySelectorAll('[data-address-default-radio]').forEach(radio => {
                    radio.checked = radio === confirmedDefaultRadio;
                });
            }
        } finally {
            isSubmitting = false;
        }
    };

    addressForm?.querySelectorAll('[data-address-required], [data-address-optional]').forEach(field => {
        field.addEventListener('focus', () => {
            if (field.hasAttribute('data-address-optional')) revealRequiredErrors = true;
            updateFormState();
        });
        field.addEventListener('blur', () => {
            if (field.hasAttribute('data-address-required')) field.dataset.addressTouched = 'true';
            updateFormState();
        });
        field.addEventListener('input', () => {
            field.setCustomValidity('');
            field.removeAttribute('aria-invalid');
            updateFormState();
        });
    });

    defaultCheckbox?.addEventListener('change', updateFormState);

    document.addEventListener('click', event => {
        const addTrigger = event.target.closest('[data-address-open]');
        if (addTrigger) {
            openAddressForm(addTrigger);
            return;
        }

        const editTrigger = event.target.closest('[data-address-edit]');
        if (editTrigger) {
            openAddressForm(editTrigger, {
                city: editTrigger.dataset.city,
                street: editTrigger.dataset.street,
                house: editTrigger.dataset.house,
                apartment: editTrigger.dataset.apartment,
                entrance: editTrigger.dataset.entrance,
                floor: editTrigger.dataset.floor,
            });
            return;
        }

        if (event.target.closest('[data-address-modal-close]') || event.target === addressModal) {
            if (!isSubmitting) {
                hideModal(addressModal);
                activeTrigger?.focus();
            }
            return;
        }

        if (event.target.closest('[data-address-delete-cancel]') || event.target === deleteModal) {
            pendingDeleteForm = null;
            hideModal(deleteModal);
            return;
        }

        if (event.target.closest('[data-address-delete-confirm]')) {
            const form = pendingDeleteForm;
            pendingDeleteForm = null;
            hideModal(deleteModal);
            if (form) submitSimpleAction(form);
            return;
        }

        if (event.target.closest('[data-address-result-close]') || event.target === resultModal) {
            hideModal(resultModal);
            activeTrigger?.focus();
        }
    });

    document.addEventListener('change', event => {
        const radio = event.target.closest('[data-address-default-radio]');
        if (!radio) return;

        if (isSubmitting) {
            radio.checked = radio === confirmedDefaultRadio;
            return;
        }
        radio.closest('form')?.requestSubmit();
    });

    document.addEventListener('submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        if (form.matches('[data-address-delete-form]')) {
            event.preventDefault();
            if (isSubmitting) return;
            pendingDeleteForm = form;
            showModal(deleteModal);
            deleteModal?.querySelector('[data-address-delete-cancel]')?.focus();
            return;
        }

        if (form.matches('[data-address-default-form]')) {
            event.preventDefault();
            const selectedRadio = form.querySelector('[data-address-default-radio]');
            if (selectedRadio) {
                await submitSimpleAction(form);
            }
            return;
        }

        if (form !== addressForm) return;
        event.preventDefault();
        if (isSubmitting || !addressForm.checkValidity()) return;

        isSubmitting = true;
        updateFormState();
        try {
            const payload = await sendForm(addressForm);
            if (payload) {
                hideModal(addressModal);
                showResult(payload.message || 'Данные успешно обновлены');
            }
        } catch (error) {
            hideModal(addressModal);
            showResult(error.message || 'Не удалось сохранить адрес. Повторите попытку.', true);
        } finally {
            isSubmitting = false;
            updateFormState();
        }
    });

    [addressModal, deleteModal, resultModal].forEach(modal => {
        modal?.addEventListener('keydown', event => {
            if (event.key !== 'Escape' || isSubmitting) return;
            hideModal(modal);
            if (modal === deleteModal) pendingDeleteForm = null;
            activeTrigger?.focus();
        });
    });

    addressForm.dataset.storeUrl = addressForm.action;
    updateFormState();
});
</script>
