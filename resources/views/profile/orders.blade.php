@extends('layouts.main')

@section('title', 'Мои заказы')

@section('content')

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
    <x-breadcrumbs :items="[
        ['title' => 'Главная', 'url' => route('home')],
        ['title' => 'Личный кабинет', 'url' => route('profile')],
        ['title' => 'Мои заказы'],
    ]" />
</div>


{{-- Основная двухколоночная часть --}}
<div class="max-w-[1500px] mx-auto flex flex-col gap-8 mt-6 mb-[70px] md:flex-row">

        <x-profile-sidebar active="orders" />

        {{-- Правая колонка: фильтры и список заказов пользователя --}}
        <main class="w-full max-w-[1152px] flex flex-col">
            <div class="flex"
                 id="orders-heading">
                <h1 class="h-[42px] flex items-center mb-6 mr-5 text-[26px] font-semibold leading-none text-[#231f20]">
                    Мои заказы
                </h1>

                <div class="mb-5">
                    <nav class="flex flex-wrap p-2.5 mt-[-10px] mx-[-14px] items-center"
                        aria-label="Фильтр заказов" data-order-filter-nav>
                        @foreach([
                            'all' => 'Все',
                            'completed' => 'Завершен',
                            'cancelled' => 'Отменен',
                            'created' => 'Создан',
                            'processing' => 'Комплектация',
                            'ready_for_pickup' => 'Готов к выдаче',
                            'cancellation' => 'Отправлен на отмену',
                        ] as $filterKey => $filterLabel)
                            @php($isActiveFilter = $filter === $filterKey)
                            <div class="p-1.5 {{ $orderCounts[$filterKey] === 0 ? 'hidden' : '' }}">
                                <a href="{{ route('profile.orders', ['filter' => $filterKey]) }}"
                                data-order-filter="{{ $filterKey }}"
                                @if($isActiveFilter) aria-current="page" @endif
                                class="inline-flex h-[36px] items-center justify-center border px-[12px] py-[5px] text-center text-[15px] text-[#231f20] transition-all
                                {{ $isActiveFilter
                                        ? 'rounded-[4px] border-black bg-white font-semibold'
                                        : 'rounded-[4px] border-[#f4f4f4] bg-[#f4f4f4] hover:border-gray-50 hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)]' }}">
                                    {{ $filterLabel }}
                                    <span class="ml-[4px] text-[12px] font-normal text-[#8c8c8c]"
                                        data-order-filter-count="{{ $filterKey }}">{{ $orderCounts[$filterKey] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </nav>
                </div>
            </div>

            <div data-order-filter-results aria-live="polite">
                @include('profile.partials.orders-list')

                <div id="orders-show-more">
                    @include('profile.partials.orders-show-more', ['orders' => $orders])
                </div>

                @if($orders->total() > $orders->perPage())
                    @include('profile.partials.orders-pagination', ['orders' => $orders])
                @endif
            </div>

        </main>

</div>

@include('profile.partials.order-cancellation-modal')

<script>
document.addEventListener('DOMContentLoaded', () => {
    let activeRequest = null;
    const cancellationModal = document.querySelector('[data-order-cancellation-modal]');
    const cancellationConfirmation = cancellationModal?.querySelector('[data-order-cancellation-confirmation]');
    const cancellationSuccess = cancellationModal?.querySelector('[data-order-cancellation-success]');
    let activeCancellationForm = null;
    let cancellationSubmitted = false;
    let cancellationInFlight = false;

    const closeCancellationModal = () => {
        if (cancellationInFlight) return;

        cancellationModal?.classList.add('hidden');
        cancellationModal?.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        if (cancellationSubmitted) {
            window.location.reload();
            return;
        }
        activeCancellationForm?.querySelector('button[type="submit"]')?.focus();
        activeCancellationForm = null;
    };

    const openCancellationModal = form => {
        activeCancellationForm = form;
        cancellationSubmitted = false;
        cancellationConfirmation?.classList.remove('hidden');
        cancellationSuccess?.classList.add('hidden');
        cancellationModal?.classList.remove('hidden');
        cancellationModal?.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        cancellationModal?.querySelector('[data-order-cancellation-dismiss]')?.focus();
    };

    document.addEventListener('submit', event => {
        const form = event.target.closest('[data-order-cancellation-form]');
        if (!form) return;

        event.preventDefault();
        openCancellationModal(form);
    });

    cancellationModal?.addEventListener('click', async event => {
        if (event.target === cancellationModal
            || event.target.closest('[data-order-cancellation-close], [data-order-cancellation-dismiss]')) {
            closeCancellationModal();
            return;
        }

        if (event.target.closest('[data-order-cancellation-acknowledge]')) {
            closeCancellationModal();
            return;
        }

        if (!event.target.closest('[data-order-cancellation-confirm]') || !activeCancellationForm) return;

        const confirmButton = cancellationModal.querySelector('[data-order-cancellation-confirm]');
        confirmButton.disabled = true;
        cancellationInFlight = true;

        try {
            const response = await fetch(activeCancellationForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': activeCancellationForm.querySelector('input[name="_token"]').value,
                },
                credentials: 'same-origin',
            });
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                throw new Error('Сервер вернул неожиданный ответ при отправке запроса.');
            }

            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.message || 'Не удалось отправить запрос на отмену заказа.');
            }

            cancellationSubmitted = true;
            cancellationConfirmation?.classList.add('hidden');
            cancellationSuccess?.classList.remove('hidden');
            cancellationModal.querySelector('[data-order-cancellation-acknowledge]')?.focus();
        } catch (error) {
            if (typeof window.showFlashMessage === 'function') {
                window.showFlashMessage(error.message || 'Не удалось отправить запрос на отмену заказа.', 'error');
            } else {
                window.alert(error.message || 'Не удалось отправить запрос на отмену заказа.');
            }
        } finally {
            confirmButton.disabled = false;
            cancellationInFlight = false;
        }
    });

    cancellationModal?.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeCancellationModal();
    });

    const loadOrdersPage = async (url, {pushHistory = true} = {}) => {
        activeRequest?.abort();
        const controller = new AbortController();
        activeRequest = controller;
        const results = document.querySelector('[data-order-filter-results]');
        const nav = document.querySelector('[data-order-filter-nav]');
        if (!results || !nav) return;

        results.setAttribute('aria-busy', 'true');
        nav.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin',
                signal: controller.signal,
            });
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('text/html')) {
                throw new Error('Сервер вернул неожиданный ответ при загрузке заказов.');
            }
            if (!response.ok) {
                throw new Error('Не удалось загрузить заказы.');
            }

            const html = await response.text();
            if (controller.signal.aborted) return;

            const parsedPage = new DOMParser().parseFromString(html, 'text/html');
            const nextResults = parsedPage.querySelector('[data-order-filter-results]');
            const nextNav = parsedPage.querySelector('[data-order-filter-nav]');
            if (!nextResults || !nextNav) {
                throw new Error('В ответе сервера отсутствует список заказов или фильтры.');
            }

            results.replaceWith(nextResults);
            nav.replaceWith(nextNav);

            if (pushHistory) {
                window.history.pushState({}, '', url);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                if (typeof window.showFlashMessage === 'function') {
                    window.showFlashMessage(error.message || 'Не удалось загрузить заказы.', 'error');
                } else {
                    window.alert(error.message || 'Не удалось загрузить заказы.');
                }
            }
        } finally {
            if (activeRequest === controller) {
                activeRequest = null;
                document.querySelector('[data-order-filter-results]')?.removeAttribute('aria-busy');
                document.querySelector('[data-order-filter-nav]')?.removeAttribute('aria-busy');
            }
        }
    };

    document.addEventListener('click', event => {
        const filterLink = event.target.closest('a[data-order-filter]');
        const pageLink = event.target.closest('#orders-pagination a');
        const showMoreButton = event.target.closest('[data-orders-show-more]');

        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        if (filterLink || pageLink) {
            event.preventDefault();
            loadOrdersPage((filterLink || pageLink).href);
            return;
        }

        if (showMoreButton) {
            event.preventDefault();
            const wrapper = showMoreButton.closest('#orders-show-more-wrapper');
            if (!wrapper) return;

            const currentPage = Number(wrapper.dataset.currentPage);
            const lastPage = Number(wrapper.dataset.lastPage);
            if (currentPage >= lastPage) return;

            const nextPage = new URL(window.location.href);
            nextPage.searchParams.set('page', String(currentPage + 1));
            loadOrdersPage(nextPage.toString());
        }
    });

    window.addEventListener('popstate', () => {
        loadOrdersPage(window.location.href, {pushHistory: false});
    });
});
</script>

@endsection
