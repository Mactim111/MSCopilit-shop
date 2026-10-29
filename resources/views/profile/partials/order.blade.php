@php
    $status = $order->profile_status;
    $statuses = [
        \App\Models\Order::ORDER_CANCELLATION => [
            'label' => 'Отправлен на отмену',
            'classes' => 'bg-[#2c9cdd]',
        ],
        \App\Models\Order::ORDER_COMPLETED => [
            'label' => 'Завершён',
            'classes' => 'bg-[#8bc34a]',
        ],
        \App\Models\Order::ORDER_CREATED => [
            'label' => 'Создан',
            'classes' => 'bg-[#2c9cdd]',
        ],
        \App\Models\Order::ORDER_PROCESSING => [
            'label' => 'Комплектация',
            'classes' => 'bg-[#2c9cdd]',
        ],
        \App\Models\Order::ORDER_READY_FOR_PICKUP => [
            'label' => 'Готов к выдаче',
            'classes' => 'bg-[#2c9cdd]',
        ],
        \App\Models\Order::ORDER_CANCELLED => [
            'label' => 'Отменён',
            'classes' => 'bg-[#979797]',
        ],
    ];
    $statusPresentation = $statuses[$status];
    $canRequestCancellation = ! $order->isFinalized()
        && $order->cancellation_requested_at === null;
    $totalFormatted = number_format((float) $order->total, 2, '.', '');
    [$whole, $fraction] = explode('.', $totalFormatted);
    $previewItems = $order->items->take(5);
    $fallbackImage = asset('storage/assets/img/photos_will_appear_soon.jpg');
@endphp

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

<div class="order-detail flex flex-col">
    <a class="cursor-pointer text-[15px] text-[#007EFF] transition-all duration-200 hover:text-[#0064cc]" href="{{ route('profile.orders') }}">
        К списку заказов
    </a>
    <div class="order-detail__wrapper">
        <div class="order-detail__title w-full mb-[22px] flex justify-between">
            <div class="flex gap-[24px]">
                <div class="title-number text-left text-[26px] font-semibold">
                    Заказ №{{ $order->id }} от {{ $order->created_at->format('d.m.Y') }}
                </div>
                <div class="order-card__status h-fit whitespace-nowrap rounded-[4px] px-2 py-[3px] text-center text-[12px] font-semibold text-white {{ $statusPresentation['classes'] }}">
                    {{ $statusPresentation['label'] }}
                </div>
            </div>
            @if($canRequestCancellation)
                <form action="{{ route('profile.orders.request-cancellation', $order) }}"
                    method="POST"
                    data-order-cancellation-form
                    class="order-actions shrink-0">
                    @csrf
                    <button type="submit"
                            class="cursor-pointer pt-[10px] pl-2 text-left text-[16px] text-[#DC092E] transition-all duration-200 hover:text-[#a80723] md:pl-[25px]">
                        Отменить заказ
                    </button>
                </form>
            @endif
        </div>

        <div class="order-detail__card w-[576px] flex border border-[#e5e5e5] rounded-[10px]">
            <div class="card-section products flex flex-col py-[13px] px-[38px]">
                <div class="card-section__products flex flex-col border-b border-dashed border-[#e5e5e5] py-4 gap-4">
                    @foreach($order->items as $item)
                        @php
                            $variant = $item->variant;
                            $review = $variant
                                ? $reviewsByVariant->get($variant->id)
                                : null;
                            $canReviewOrderItem = $order->status === \App\Models\Order::STATUS_COMPLETED
                                && $variant !== null;
                        @endphp
                        <div class="card-section__product flex gap-x-[12px]">
                            <!-- Фото товара -->
                            <div class="w-15 h-15 flex-shrink-0">
                                @if($variant)
                                    <a href="{{ route('catalog.variant', $variant->slug) }}">
                                        <img src="{{ $variant->mainImage() }}"
                                            alt="{{ $variant->title }}"
                                            class="w-full h-full">
                                    </a>
                                @endif
                            </div>
                            <div class="card-section__product-column name flex flex-col text-left">
                                @if($variant)
                                    <a href="{{ route('catalog.variant', $variant->slug) }}" class="text-[15px] mb-3">
                                        {{ $variant->title }}
                                    </a>
                                @else
                                    <div class="text-[15px] mb-3">{{ $item->title }}</div>
                                @endif
                                @if($canReviewOrderItem)
                                    @if($review)
                                        <div class="mt-1 flex items-center justify-between">
                                            @if($review->addition === null)
                                                <a href="{{ route('catalog.variant.reviews', $variant) }}"
                                                data-review-form="addition"
                                                data-review-id="{{ $review->id }}"
                                                data-form-action="{{ route('reviews.addition', $review) }}"
                                                data-product-title="{{ $variant->title }}"
                                                data-product-image="{{ $variant->mainImage() }}"
                                                class="text-[13px] text-[#007eff] transition-all duration-200 hover:text-[#0064cc]">
                                                    Дополнить отзыв
                                                </a>
                                            @else
                                                <span class="text-[13px] text-gray-500">Отзыв дополнен</span>
                                            @endif

                                            <form action="{{ route('reviews.destroy', $review) }}"
                                                method="POST"
                                                data-review-delete-form>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="cursor-pointer text-[13px] text-[#DC092E] hover:text-[#a80723]">
                                                    Удалить отзыв
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <a href="{{ route('catalog.variant.reviews', $variant) }}"
                                        data-review-form="create"
                                        data-form-action="{{ route('reviews.store', $variant) }}"
                                        data-product-title="{{ $variant->title }}"
                                        data-product-image="{{ $variant->mainImage() }}"
                                        class="text-[13px] text-[#007eff] transition-all duration-200 hover:text-[#0064cc]">
                                            Добавить отзыв
                                        </a>
                                    @endif
                                @endif
                            </div>
                            <div class="card-section__product-column price flex flex-col text-right">
                                <div class="card-section__product-price">
                                    <span class="font-medium text-xl">
                                        @if($variant)
                                            {!! $variant->formattedPrice(18, 18) !!}
                                        @else
                                            {{ number_format($item->price, 2, '.', '') }}
                                        @endif
                                    </span><i class="nbrb-icon">BYN</i>
                                </div>
                                <div class="text-[12px]">x{{ $item->quantity }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card-section__info flex flex-col min-w-[576px] py-4 gap-4 border-b border-dashed border-[#e5e5e5]">
                    <div class="flex justify-between info-row">
                        <div class="info-row__title text-15px">
                             Стоимость товаров 
                             <span class="quantity ml-1 text-[12px] text-[#8c8c8c]">5</span>
                        </div>
                        <div class="info-row__bold text-xl font-semibold">{!! $item->formattedSubtotal(18, 18) !!}</div>
                    </div>
                    <div class="info-row flex justify-between">
                        <div class="info-row__title text-15px"> Доставка </div>
                        <div class="info-row__bold text-18px font-semibold">Бесплатно</div>
                    </div>
                </div>
                <div class="card-section__total info-row flex flex-col py-4 justify-between text-[18px] font-semibold">
                    <div> Итого к оплате </div>
                    <div>{!! $item->formattedSubtotal(18, 18) !!}</div>
                </div>
            </div>
            
            <div class="divider h-full bg-[#e5e5e5]"></div>

            <div class="card-section description flex flex-col py-[29px] px-[38px] gap-[19px] text-left">
                <div class="info-block">
                    <div class="info__title mb-[6px] text-[14px] font-semibold">Доставка курьером по адресу</div>
                <div class="info__text w-full text-[13px]">
                    {{ $order->address ?: 'Адрес доставки не указан' }}
                </div>
                    {{-- <div class="info-block__title mb-[6px] text-[18px] font-semibold"> После получения Viber/SMS-уведомления </div>
                    <div class="info-block__text text-[14px]"> Можно забрать 30.10.2026 c 11:00 до 20:00</div>
                    <div class="info-block__text text-[14px]">Брест, ул. Московская, 210 (ТЦ «Корона», 1 этаж) 11:00 - 20:00(Пн - Пт), 10:00 - 20:00(Сб - Вс)</div> --}}
                </div>
                @if($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                    <div class="info__title mb-[6px] text-[18px] font-semibold">Оплата онлайн</div>
                    <div class="info__text text-[14px]">Картой рассрочки или банковской картой на сайте</div>
                @else
                    <div class="info__title mb-[6px] text-[18px] font-semibold">Оплата при получении:</div>
                    <div class="info__text text-[14px]">Наличными или банковской картой</div>
                @endif
                <div class="info-block">
                    <div class="info-block__title mb-[6px] text-[18px] font-semibold"> Получатель: </div>
                    <div class="info-block__text text-[14px]">{{ $order->name }} {{ $order->phone }}</div>
                </div>
            </div>
        </div>
    </div>
</div>