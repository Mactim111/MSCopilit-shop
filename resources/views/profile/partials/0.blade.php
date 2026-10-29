{{-- структурная верстка страницы просмотра конкретного заказа со статусом Комплектация в профиле юзера --}}

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

        <div class="order-detail__card flex border border-[#e5e5e5] rounded-[10px]">
            <div class="card-section products flex flex-col py-[13px] px-[38px]">
                <div class="card-section__products flex flex-col border-b border-dashed border-[#e5e5e5] px-4 space-y-4">
                    <div class="card-section__product flex gap-x-[12px]">
                        @foreach($order->items as $item)
                            @php
                                $variant = $item->variant;
                                $review = $variant
                                    ? $reviewsByVariant->get($variant->id)
                                    : null;
                                $canReviewOrderItem = $order->status === \App\Models\Order::STATUS_COMPLETED
                                    && $variant !== null;
                            @endphp
                            <div class="card-section__product-column image">
                                <a href="/products/793272-nakopitel-ssd-kingspec-nt-128">
                                    <img class="card-section__product-image"
                                        src="https://images.5element.by/resize/af4/af4be84cec98d63a90821fb3e6760599/246_246_1/b84d2fc2e6f6c823fd622a1f14e71f4a.jpg"
                                        alt="NT-128 Накопитель SSD Kingspec">
                                </a>
                            </div>
                            <div class="card-section__product-column name"><a
                                    href="/products/793272-nakopitel-ssd-kingspec-nt-128"
                                    class="card-section__product-name">NT-128 Накопитель SSD Kingspec</a></div>
                            <div class="card-section__product-column price">
                                <div class="card-section__product-price">
                                    <span class="">95.00</span>
                                    <i class="nbrb-icon nbrb-icon-byn"></i>
                                </div>
                                <div class="card-section__product-quantity"> x 1шт </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-section__info">
                    <div class="info-row">
                        <div class="info-row__title">
                             Стоимость товаров 
                             <span class="quantity">1</span>
                        </div>
                        <div class="info-row__bold">95.00<i class="nbrb-icon nbrb-icon-byn"></i></div>
                    </div>
                    <div class="info-row">
                        <div class="info-row__title"> Доставка </div>
                        <div class="info-row__bold">Бесплатно</div>
                    </div>
                    <div class="info-row">
                        <div class="info-row__title"> Бонусы к начислению </div>
                        <div class="info-row__bold">2.37</div>
                    </div>
                </div>
                <div class="card-section__total info-row">
                    <div class="info-row__bold"> Итого к оплате </div>
                    <div class="info-row__bold">95.00<i class="nbrb-icon nbrb-icon-byn"></i></div>
                </div>
            </div>
            <div class="divider h-full bg-[#e5e5e5]"></div>
            <div class="card-section description">
                <div class="info-block">
                    <div class="info-block__title"> После получения Viber/SMS-уведомления </div>
                    <div class="info-block__text"> Можно забрать 30.10.2026 c 11:00 до 20:00</div>
                    <div class="info-block__text">Брест, ул. Московская, 210 (ТЦ «Корона», 1 этаж)</div>
                </div>
                <div class="info-block">
                    <div class="info-block__title"> Оплата при получении: </div>
                    <div class="info-block__text">Наличными или банковской картой</div>
                </div>
                <div class="info-block">
                    <div class="info-block__title"> Получатель: </div>
                    <div class="info-block__text">Женик +375 (29) 796-53-36</div>
                </div>
            </div>
        </div>
    </div>
</div>

--------------------------------------------------------------------------------------------------------------------------------------------------

{{-- структурная верстка карточки заказа из списка заказов во вкладке Мои заказы в профиле юзера --}}
<div class="order-card flex flex-col w-full rounded-[10px] border border-gray-100 bg-white shadow-[0_2px_8px_rgba(0,0,0,0.20)] px-6 py-6 md:px-8">

    <div class="order-card__row flex justify-between pb-3 text-left">
        <div class="order-card__column title flex gap-[82px]">
            <div class="order-card__title flex flex-col w-[287px]">
                @if ($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                    <div class="title-pay flex mb-1 gap-1 tex-left">
                        @include('products.icons.order-paid')
                        <div class="ml-1 text-[15px] font-semibold text-[#70be71]">Оплачен</div>
                    </div>
                @endif             
                <a href="{{ route('profile.order', $order->id) }}" 
                    class="title-number w-full mb-2 text-[15px] font-semibold text-[#007eff] hover:text-[#0056b3] transition-all duration-200"> 
                    Заказ №{{ $order->id }}
                </a>
                <div class="title-desc flex gap-[45px] text-[16px]">
                    <div class="title-desc__date">{{ $order->created_at->format('d.m.Y H:i') }}</div>
                    <div class="title-desc__price">
                        @php
                            $totalFormatted = number_format($order->total, 2, '.', ' ');
                            [$whole, $fraction] = explode('.', $totalFormatted);
                        @endphp
                        {!! $whole . '<span class="text-sm">.</span><span class="text-sm">' . $fraction . '</span> <i class="nbrb-icon">BYN</i>' !!}
                    </div>
                </div>
            </div>
            <div class="order-card__status text-center text-[12px] font-semibold text-white">{{ $order->profile_status }}</div>
        </div>
        @if (!$order->status === \App\Models\Order::ORDER_COMPLETED || !$order->status === \App\Models\Order::ORDER_CANCELLED || !$order->status === \App\Models\Order::ORDER_CANCELLATION)
            <div class="order-actions">
                <a href="#" class="cursor-pointer text-left text-[16px] text-[#DC092E] hover:text-[#a80723] transition-all duration-200 hover:text-decoration
                    pt-[10px] pl-[25px] pr-[10px] pb-[25px]">
                    Отменить заказ
                </a>
            </div>
        @endif
    </div>

    <div class="order-card__row flex justify-between pt-3 border-b border-dashed border-[#e5e5e5]">
        <div class="order-card__column flex gap-[82px] w-[287px]">
            <div class="order-card__info flex flex-col text-left">
                <div class="info__title mb-[6px] text-[14px] font-semibold">Доставка курьером по адресу</div>
                <div class="info__text w-full text-[13px]">{{ $order->address }}</div>
            </div>
            <div class="order-card__info w-[253px] text-left">
                <div class="info__title mb-[6px] text-[14px] font-semibold"> Оплата при получении: </div>
                <div class="info__text text-[13px]">Наличными или банковской картой</div>
            </div>
        </div>
        <div class="order-card__previews text-right pb-6 mb-[-12px]">
            <div class="w-[70px] h-[70px]">
                <img src="{{ $variant->mainImage() }}" alt="{{ $variant->title }}" class="w-full h-full object-cover">
        </div>
        </div>
    </div>

</div>

------------------------------------------------------------------------------------------------------------------------------------------------------




