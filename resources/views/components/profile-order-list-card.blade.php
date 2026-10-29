@props(['order'])

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

<article class="order-card flex w-full flex-col rounded-[10px] border border-gray-100 bg-white px-6 py-6 shadow-[0_2px_8px_rgba(0,0,0,0.20)] md:px-8">
    <div class="order-card__row flex justify-between gap-4 pb-3 text-left">
        <div class="order-card__column title flex min-w-0 gap-6 md:gap-[82px]">
            <div class="order-card__title flex w-full min-w-0 flex-col md:w-[287px]">
                @if($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                    <div class="title-pay paid mb-1 flex items-center gap-1 text-left">
                        @include('products.icons.order-paid')
                        <div class="ml-1 text-[15px] font-semibold text-[#70be71]">Оплачен</div>
                    </div>
                @endif

                <a href="{{ route('profile.order', $order) }}"
                   class="title-number mb-2 w-full text-[20px] font-semibold text-[#007eff]">
                    Заказ №{{ $order->id }}
                </a>

                <div class="title-desc flex flex-wrap gap-x-[45px] gap-y-1 text-[16px]">
                    <div class="title-desc__date">{{ $order->created_at->format('d.m.Y') }}</div>
                    <div class="title-desc__price">
                        {{ $whole }}<span class="text-sm">.</span><span class="text-[16px]">{{ $fraction }}</span><i class="nbrb-icon">BYN</i>
                    </div>
                </div>
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

    <div class="order-card__row flex flex-col justify-between gap-4 border-t border-dashed border-[#e5e5e5] pt-3 md:flex-row">
        <div class="order-card__column flex flex-col gap-4 text-left sm:flex-row sm:gap-[82px]">
            <div class="order-card__info w-full text-left md:w-[287px]">
                <div class="info__title mb-[6px] text-[14px] font-semibold">Доставка курьером по адресу</div>
                <div class="info__text w-full text-[13px]">
                    {{ $order->address ?: 'Адрес доставки не указан' }}
                </div>
            </div>

            <div class="order-card__info w-full text-left md:w-[253px]">
                @if($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                    <div class="info__title mb-[6px] text-[14px] font-semibold">Оплата онлайн</div>
                    <div class="info__text text-[13px]">Картой рассрочки или банковской картой на сайте</div>
                @else
                    <div class="info__title mb-[6px] text-[14px] font-semibold">Оплата при получении:</div>
                    <div class="info__text text-[13px]">Наличными или банковской картой</div>
                @endif
            </div>
        </div>

        @if($previewItems->isNotEmpty())
            <div class="order-card__previews mb-[-12px] flex shrink-0 flex-wrap justify-end gap-3 pb-6">
                @foreach($previewItems as $item)
                    @php($variant = $item->variant)
                    <div class="h-[70px] w-[70px]">
                        <img src="{{ $variant?->images->first()?->url ?? $fallbackImage }}"
                             alt="{{ $item->title }}"
                             title="{{ $item->title }}"
                             class="h-full w-full object-contain">
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</article>
