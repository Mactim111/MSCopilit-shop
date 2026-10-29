@extends('layouts.main')

@section('title', 'Заказ №' . $order->id)

@section('content')
    @php
        $profileStatus = $order->profile_status;
        $statusPresentation = [
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
        ][$profileStatus];
        $totalQuantity = $order->items->sum('quantity');
        $goodsTotal = $order->items->sum(fn ($item) => (float) $item->subtotal);
        $canRequestCancellation = ! $order->isFinalized()
            && $order->cancellation_requested_at === null;
        $fallbackImage = asset('storage/assets/img/photos_will_appear_soon.jpg');
    @endphp

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
            <a href="{{ route('profile.orders') }}"
                class="mb-1 inline-block text-[15px] text-[#007eff] transition-colors hover:text-[#0064cc]">
                К списку заказов
            </a>

            <header class="mb-[22px] flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                    <h1 class="text-left text-[26px] font-semibold leading-tight">
                        Заказ №{{ $order->id }} от {{ $order->created_at->format('d.m.Y') }}
                    </h1>
                    <span class="h-fit whitespace-nowrap rounded-[4px] px-2 py-[3px] text-center text-[12px] font-semibold text-white {{ $statusPresentation['classes'] }}">
                        {{ $statusPresentation['label'] }}
                    </span>
                </div>

                @if($canRequestCancellation)
                    <form action="{{ route('profile.orders.request-cancellation', $order) }}"
                            method="POST"
                            data-order-cancellation-form>
                        @csrf
                        <button type="submit"
                                class="cursor-pointer text-[16px] text-[#dc092e] transition-colors hover:text-[#a80723]">
                            Отменить
                        </button>
                    </form>
                @endif
            </header>

            <section class="flex flex-col overflow-hidden rounded-[10px] border border-[#e5e5e5] bg-white lg:flex-row">
                <div class="min-w-[574px] min-w-0 px-5 py-5 sm:px-[38px] lg:w-[40%] lg:shrink-0">
                    <div class="w-full flex flex-col gap-4 border-b border-dashed border-[#e5e5e5] py-4">
                        @foreach($order->items as $item)
                            @php
                                $variant = $item->variant;
                                $review = $variant ? $reviewsByVariant->get($variant->id) : null;
                                $canReviewItem = $order->status === \App\Models\Order::STATUS_COMPLETED
                                    && $variant !== null;
                                $itemPrice = number_format((float) $item->price, 2, '.', '');
                                [$itemWhole, $itemFraction] = explode('.', $itemPrice);
                            @endphp

                            <div class="card-section__product flex min-w-0 items-center gap-3">
                                <div class="h-[60px] w-[60px] shrink-0">
                                    @if($variant)
                                        <a href="{{ route('catalog.variant', $variant) }}">
                                            <img src="{{ $variant->mainImage() }}"
                                                    alt="{{ $item->title }}"
                                                    class="h-full w-full object-contain">
                                        </a>
                                    @else
                                        <img src="{{ $fallbackImage }}"
                                                alt="{{ $item->title }}"
                                                class="h-full w-full object-contain">
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1 text-left">
                                    @if($variant)
                                        <a href="{{ route('catalog.variant', $variant) }}"
                                            class="mb-2 block text-[15px] leading-snug text-[#231f20] hover:text-[#007eff]">
                                            {{ $item->title }}
                                        </a>
                                    @else
                                        <p class="mb-2 text-[15px] leading-snug">{{ $item->title }}</p>
                                    @endif

                                    @if($canReviewItem)
                                        @if($review)
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                                @if($review->addition === null)
                                                    <a href="{{ route('catalog.variant.reviews', $variant) }}"
                                                        data-review-form="addition"
                                                        data-review-id="{{ $review->id }}"
                                                        data-form-action="{{ route('reviews.addition', $review) }}"
                                                        data-product-title="{{ $variant->title }}"
                                                        data-product-image="{{ $variant->mainImage() }}"
                                                        class="text-[13px] text-[#007eff] transition-colors hover:text-[#0064cc]">
                                                        Дополнить
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
                                                            class="cursor-pointer text-[13px] text-[#dc092e] hover:text-[#a80723]">
                                                        Удалить
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <a href="{{ route('catalog.variant.reviews', $variant) }}"
                                                data-review-form="create"
                                                data-form-action="{{ route('reviews.store', $variant) }}"
                                                data-product-title="{{ $variant->title }}"
                                                data-product-image="{{ $variant->mainImage() }}"
                                                class="text-[13px] text-[#007eff] transition-colors hover:text-[#0064cc]">
                                                Добавить отзыв
                                            </a>
                                        @endif
                                    @endif
                                </div>

                                <div class="flex flex-col shrink-0 self-start text-right">
                                    <div class="whitespace-nowrap text-[18px] font-semibold">
                                        {{ $itemWhole }}<span class="text-[18px]">.{{ $itemFraction }}</span><i class="nbrb-icon">BYN</i>
                                    </div>
                                    <div class="text-[12px]">x{{ $item->quantity }} шт.</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-4 border-b border-dashed border-[#e5e5e5] py-4">
                        <div class="info-row flex items-center justify-between gap-4">
                            <div class="info-row__title text-[15px]">
                                Стоимость товаров
                                <span class="ml-1 text-[12px] text-[#8c8c8c]">{{ $totalQuantity }}</span>
                            </div>
                            <div class="whitespace-nowrap text-[18px] font-semibold">
                                {{ number_format($goodsTotal, 2, '.', '') }}<i class="nbrb-icon">BYN</i>
                            </div>
                        </div>
                        <div class="info-row flex items-center justify-between gap-4">
                            <div class="text-[15px]">Доставка</div>
                            <div class="text-[18px] font-semibold">Бесплатно</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-4 text-[18px] font-semibold">
                        <div>Итого к оплате</div>
                        <div class="whitespace-nowrap">
                            {{ number_format((float) $order->total, 2, '.', '') }}<i class="nbrb-icon">BYN</i>
                        </div>
                    </div>
                </div>

                <div class="hidden w-px self-stretch bg-[#e5e5e5] lg:block"></div>
                <div class="h-px w-full bg-[#e5e5e5] lg:hidden"></div>

                <div class="flex flex-1 flex-col gap-[19px] px-5 py-6 text-left sm:px-[38px] sm:py-[29px]">
                    <div class="info-block">
                        <div class="mb-[6px] text-[16px] font-semibold">Доставка курьером по адресу</div>
                        <div class="text-[14px]">{{ $order->address ?: 'Адрес доставки не указан' }}</div>
                    </div>

                    <div class="info-block">
                        @if($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                            <div class="mb-[6px] text-[16px] font-semibold">Оплата онлайн:</div>
                            <div class="text-[14px]">Картой рассрочки или банковской картой на сайте</div>
                        @else
                            <div class="mb-[6px] text-[16px] font-semibold">Оплата при получении:</div>
                            <div class="text-[14px]">Наличными или банковской картой</div>
                        @endif
                    </div>

                    <div class="info-block">
                        <div class="mb-[6px] text-[16px] font-semibold">Получатель:</div>
                        <div class="text-[14px]">{{ $order->name }} {{ $order->phone }}</div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

    @include('variants.partials.review-create-modal')
    @include('variants.partials.review-addition-modal')
    @include('variants.partials.review-action-result-modal')
    @include('profile.partials.order-cancellation-modal')
@endsection
