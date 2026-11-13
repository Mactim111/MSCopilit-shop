@extends('layouts.main')

@section('title', 'Оформление заказа')

@section('content')
<h1 class="mx-auto text-3xl font-bold mt-6">Оформление заказа</h1>
<div class="mx-auto flex max-w-[1500px] flex-col gap-8 py-6 lg:flex-row lg:items-start">
    {{-- Левая колонка: поля ввода-изменения личных данных, адреса и способа оплаты для пользователя --}}
    <main class="min-w-0 w-full flex-1 flex flex-col">

        

        <form action="{{ route('orders.store') }}" method="POST" class="space-y-10" data-checkout-form>
            @csrf
            {{-- ПЕРЕДАЕМ ВЫБРАННЫЕ ID В СТОРИ --}}
            @foreach($selectedIds as $id)
                <input type="hidden" name="items[]" value="{{ $id }}">
            @endforeach

            {{-- Контактные данные --}}
            <div class="border border-[#e5e5e5] rounded-[10px] p-6">
                <h2 class="text-xl font-semibold mb-4">Контактные данные</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-medium mb-1">Имя</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}"
                            class="w-full p-1 border-gray-300 rounded-[6px] border @error('name') border-red-500 @enderror px-3">
                        @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}"
                            class="w-full p-1 border-gray-300 rounded-[6px] border @error('email') border-red-500 @enderror px-3">
                        @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Фамилия</label>
                        <input type="text" name="last_name" value="{{ old('last_name', auth()->user()?->last_name) }}"
                            class="w-full p-1 border-gray-300 rounded-[6px] border @error('last_name') border-red-500 @enderror px-3">
                        @error('last_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Телефон</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone) }}"
                            class="w-full p-1 border-gray-300 rounded-[6px] border @error('phone') border-red-500 @enderror px-3">
                        @error('phone')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <section class="rounded-[10px] border border-[#e5e5e5] p-6">
                <h2 class="mb-4 text-xl font-semibold">Выберите способ доставки</h2>

                @php
                    $deliveryMethod = old('delivery_method', \App\Models\Order::DELIVERY_PICKUP);
                    $selectedAddressId = old('address_id', $defaultAddressId);
                    $selectedPaymentMethod = old('payment_method', \App\Models\Order::PAYMENT_CASH_ON_DELIVERY);
                    $selectedPaymentSummary = $selectedPaymentMethod === \App\Models\Order::PAYMENT_ONLINE_CARD
                        ? 'Онлайн банковской картой Visa, MasterCard, Белкарт и др.'
                        : 'При получении наличными или банковской картой';
                    $canSaveAddresses = auth()->check() && auth()->user()->hasVerifiedEmail();
                @endphp

                <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-lg border border-[#e5e5e5] p-3 hover:bg-gray-50">
                    <input class="peer sr-only" type="radio"
                           name="delivery_method"
                           value="{{ \App\Models\Order::DELIVERY_PICKUP }}"
                           data-delivery-method
                           data-delivery-fee="0"
                           required
                           @checked($deliveryMethod === \App\Models\Order::DELIVERY_PICKUP)>
                    <span class="relative mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block"></span>
                    <span>
                        <span class="block text-[15px] font-semibold">Самовывоз из пункта выдачи</span>
                        <span class="text-sm text-gray-600">{{ \App\Models\Order::PICKUP_ADDRESS }}. Бесплатно</span>
                    </span>
                </label>

                <div class="rounded-lg border border-[#e5e5e5] p-3 hover:bg-gray-50"
                     data-courier-card>
                    <label class="flex cursor-pointer items-start gap-3">
                        <input class="peer sr-only" type="radio"
                               name="delivery_method"
                               value="{{ \App\Models\Order::DELIVERY_COURIER }}"
                               data-delivery-method
                               data-delivery-fee="{{ $courierDeliveryPrice }}"
                               @checked($deliveryMethod === \App\Models\Order::DELIVERY_COURIER)>
                        <span class="relative mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-[#e5e5e5] bg-white after:hidden after:h-[6px] after:w-[6px] after:rounded-full after:bg-white peer-checked:border-[#dc092e] peer-checked:bg-[#dc092e] peer-checked:after:block"></span>
                        <span class="flex flex-wrap items-baseline gap-x-1 text-[15px] font-semibold">
                            Доставка курьером по указанному адресу —
                            <span class="whitespace-nowrap">{{ number_format($courierDeliveryPrice, 2, '.', ' ') }}<i class="nbrb-icon">BYN</i></span>
                        </span>
                    </label>

                    <div class="ml-4 mt-4 space-y-4" data-courier-addresses>
                        @if($canSaveAddresses)
                            <div @if($addresses->isEmpty()) hidden @endif data-saved-address-section>
                                <label for="checkout-address-select" class="sr-only">Адрес доставки</label>
                                <select id="checkout-address-select"
                                        name="address_id"
                                        data-address-select
                                        class="h-[44px] w-full cursor-pointer rounded-[8px] border border-[#d6d6d6] bg-white px-3 text-[15px] text-[#231f20] focus:border-[#231f20] focus:outline-none"
                                        >
                                    <option value="" @selected(blank($selectedAddressId))>Выберите адрес доставки</option>
                                    @foreach($addresses as $addr)
                                        <option value="{{ $addr->id }}"
                                                @selected((string) $selectedAddressId === (string) $addr->id)>
                                            {{ $addr->fullAddress() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div @if(old('guest_address.city')) hidden @endif data-guest-address-prompt
                                 class="text-[15px] text-[#231f20]">
                                Выберите адрес доставки
                            </div>
                            <div hidden data-guest-address-display class="text-[15px] text-[#231f20]">
                                <span data-guest-address-text></span>
                                <button type="button" data-guest-address-edit
                                        class="ml-2 cursor-pointer text-[#007eff] hover:text-[#0064cc]">
                                    Изменить
                                </button>
                            </div>
                            @foreach(['city', 'street', 'house', 'apartment', 'entrance', 'floor'] as $field)
                                <input type="hidden"
                                       name="guest_address[{{ $field }}]"
                                       value="{{ old('guest_address.' . $field) }}"
                                       data-guest-address-field="{{ $field }}">
                            @endforeach
                        @endif

                        <button type="button"
                                data-checkout-add-address
                                class="group flex cursor-pointer items-center text-[15px] text-[#007eff] transition-colors hover:text-[#0064cc]">
                            <span class="mr-2 inline-flex h-4 w-4 shrink-0 items-center justify-center transition-colors group-hover:text-[#0064cc]">
                                @include('products.plus-circle')
                            </span>
                            <span data-add-address-label>Добавить новый адрес</span>
                        </button>
                        <p class="hidden text-sm text-red-600" data-checkout-address-error aria-live="polite">
                            Выберите или добавьте адрес доставки.
                        </p>
                    </div>
                </div>

                @error('delivery_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('address_id')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('guest_address')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('guest_address.city')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('guest_address.street')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('guest_address.house')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </section>

            <section class="rounded-[10px] border border-[#e5e5e5] p-6">
                <h2 class="mb-4 text-xl font-semibold">Выберите способ оплаты</h2>

                @foreach([
                    \App\Models\Order::PAYMENT_CASH_ON_DELIVERY => ['При получении', 'Наличными или банковской картой'],
                    \App\Models\Order::PAYMENT_ONLINE_CARD => ['Онлайн', 'Банковской картой Visa, MasterCard, Белкарт и др.'],
                ] as $method => [$label, $description])
                    <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-lg border border-[#e5e5e5] p-3 hover:bg-gray-50">
                        <input type="radio"
                               name="payment_method"
                               value="{{ $method }}"
                               data-payment-method
                               data-payment-summary="{{ $method === \App\Models\Order::PAYMENT_ONLINE_CARD ? 'Онлайн банковской картой Visa, MasterCard, Белкарт и др.' : 'При получении наличными или банковской картой' }}"
                               class="mt-1"
                               @if($loop->first) required @endif
                               @checked(old('payment_method', \App\Models\Order::PAYMENT_CASH_ON_DELIVERY) === $method)>
                        <span>
                            <span class="block text-[15px] font-semibold">{{ $label }}</span>
                            <span class="text-sm text-gray-600">{{ $description }}</span>
                        </span>
                    </label>
                @endforeach
                @error('payment_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </section>


            {{-- Кнопка --}}
            <button type="submit"
                    class="w-full md:w-auto bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition-colors duration-300 cursor-pointer">
                Подтвердить заказ
            </button>

        </form>

        @include('profile.partials.address-create-modal', [
            'addressCanPersist' => $canSaveAddresses,
        ])

        <div class="fixed inset-0 z-[1200] hidden items-center justify-center bg-[#231f20bf] px-4 py-4"
             role="dialog"
             aria-modal="true"
             aria-live="polite"
             aria-labelledby="checkout-address-result-message"
             data-checkout-address-result>
            <div class="relative flex min-h-[188px] w-[432px] max-w-full flex-col justify-center rounded-[10px] bg-white py-8 pl-6 pr-6 sm:pl-[50px] sm:pr-10">
                <button type="button"
                        data-checkout-address-result-close
                        aria-label="Закрыть окно"
                        class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
                    <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </button>
                <div class="flex w-full flex-col gap-4">
                    <p id="checkout-address-result-message"
                       class="mb-4 text-center text-[24px] font-bold text-[#231f20]"
                       data-checkout-address-result-message>Данные успешно обновлены</p>
                    <button type="button"
                            data-checkout-address-result-close
                            class="h-[48px] w-full cursor-pointer rounded-[10px] border border-[#dc092e] bg-[#dc092e] px-[14px] py-3 text-center text-[15px] font-semibold text-white transition-colors hover:bg-[#a80723]">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </main>

    @php
        $initialDeliveryFee = $deliveryMethod === \App\Models\Order::DELIVERY_COURIER
            ? $courierDeliveryPrice
            : 0;
    @endphp
    <aside class="w-full shrink-0 lg:sticky lg:top-6 lg:w-[360px]" data-checkout-summary
           data-goods-total="{{ $total }}"
           data-courier-fee="{{ $courierDeliveryPrice }}">
        <section class="rounded-[10px] border border-[#e5e5e5] bg-white p-5">
            <div class="border-b border-[#e5e5e5] py-4">
                <div class="mb-3 flex items-center justify-between gap-3 text-[15px] font-semibold">
                    <span class="text-[16px] font-semibold">Ваш заказ</span>
                    <span class="text-right text-sm font-normal text-gray-500">{{ $totalQuantity }} шт.</span>
                </div>

                <div class="space-y-3">
                    @foreach($items as $item)
                        @php
                            $lineTotal = (float) $item->variant->price * $item->quantity;
                        @endphp
                        <div class="flex justify-between gap-3 text-[13px]">
                            <div class="min-w-0">
                                <a href="{{ route('catalog.variant', $item->variant) }}"
                                   class="text-[#231f20] hover:text-[#007eff]">
                                    {{ $item->variant->title }}
                                </a>
                                @if($item->quantity > 1)
                                    <div class="text-xs text-gray-500">x{{ $item->quantity }} шт.</div>
                                @endif
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="whitespace-nowrap">
                                    {{ number_format($lineTotal, 2, '.', ' ') }}<i class="nbrb-icon">BYN</i>
                                </div>
                                @if($item->quantity > 1)
                                    <div class="text-xs text-gray-500 whitespace-nowrap">
                                        {{ number_format((float) $item->variant->price, 2, '.', ' ') }}<i class="nbrb-icon">BYN</i>/шт.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-3 border-b border-[#e5e5e5] py-4 text-[14px]">
                <div class="flex justify-between gap-3">
                    <span class="text-gray-600" data-summary-delivery-label>
                        {{ $deliveryMethod === \App\Models\Order::DELIVERY_PICKUP ? 'Самовывоз из пункта выдачи' : 'Доставка курьером по адресу' }}
                    </span>
                    <span class="max-w-[65%] shrink-0 text-right" data-summary-delivery-price>
                        @if($initialDeliveryFee === 0)
                            Бесплатно
                        @else
                            <span class="whitespace-nowrap">{{ number_format($initialDeliveryFee, 2, '.', ' ') }}<i class="nbrb-icon">BYN</i></span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="border-b border-[#e5e5e5] py-4 text-[14px]" data-summary-payment>
                {{ $selectedPaymentSummary }}
            </div>

            <div class="flex items-center justify-between gap-3 pt-4 text-[16px] font-semibold">
                <span>Итого к оплате</span>
                <span class="whitespace-nowrap" data-summary-total>
                    {{ number_format((float) $total + $initialDeliveryFee, 2, '.', ' ') }}<i class="nbrb-icon">BYN</i>
                </span>
            </div>
        </section>
    </aside>
</div>
@endsection
