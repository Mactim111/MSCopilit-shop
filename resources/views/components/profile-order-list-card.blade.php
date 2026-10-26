@props(['order'])

@php
    $variant = $order->items->first()?->variant;
    $variantImage = $variant?->images->first()?->url
        ?? asset('storage/assets/img/photos_will_appear_soon.jpg');
    $orderStatuses = [
        \App\Models\Order::ORDER_CANCELLATION => [
            'label' => 'Отправлен на отмену',
            'classes' => 'border-[#2c9cdd] bg-[#2c9cdd]',
        ],
        \App\Models\Order::ORDER_COMPLETED => [
            'label' => 'Завершён',
            'classes' => 'border-[#8bc34a] bg-[#8bc34a]',
        ],
        \App\Models\Order::ORDER_CREATED => [
            'label' => 'Создан',
            'classes' => 'border-[#2c9cdd] bg-[#2c9cdd]',
        ],
        \App\Models\Order::ORDER_CANCELLED => [
            'label' => 'Отменен',
            'classes' => 'border-[#979797] bg-[#979797]',
        ],
    ];
    $orderStatus = $orderStatuses[$order->profile_status];
@endphp

<article class="cabinet-orders__card w-full rounded-[10px] border border-gray-100 bg-white shadow-[0_2px_8px_rgba(0,0,0,0.20)] px-6 py-6 md:px-8">
    <div class="flex flex-col">
        <header class="mb-4 flex items-start justify-between gap-4 border-b border-dashed border-[#e5e5e5] pb-4">
            <div class="flex min-w-0 items-start gap-4">



            </div>
        </header>   
    </div>
</article>