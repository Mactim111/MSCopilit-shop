@extends('admin.layouts.admin')

@section('header-title', 'Заказы')

@section('content')
@php
    $filters = [
        'all' => 'Все',
        'created' => 'Новые',
        'processing' => 'На комплектации',
        'ready_for_pickup' => 'Готов к выдаче',
        'in_transit' => 'Передан в доставку',
        'cancellation' => 'Отправлен на отмену',
        'cancelled' => 'Отменён',
        'completed' => 'Завершён',
    ];
    $sortableColumns = [
        'id' => 'Номер заказа',
        'customer' => 'Покупатель',
        'email' => 'Email',
        'total' => 'Сумма',
    ];
    $defaultDirections = [
        'id' => 'desc',
        'customer' => 'asc',
        'email' => 'asc',
        'total' => 'desc',
        'created_at' => 'desc',
        'completed_at' => 'desc',
    ];
@endphp

<div class="mx-auto w-full max-w-[1300px]">
    <div class="sticky top-0 z-30 -mx-6 mb-5 border-b border-[#e5e5e5] bg-white px-6 pb-3 pt-1">
        <h1 class="mb-4 text-[26px] font-semibold leading-none text-[#231f20]">Заказы</h1>

        <nav class="flex flex-wrap" aria-label="Фильтр заказов">
            @foreach($filters as $filterKey => $filterLabel)
                @php
                    $isActiveFilter = $filter === $filterKey;
                @endphp
                <div class="p-1.5 {{ $orderCounts[$filterKey] === 0 ? 'hidden' : '' }}">
                    <a href="{{ route('admin.orders.index', ['filter' => $filterKey, 'sort' => $sort, 'direction' => $direction]) }}"
                       @if($isActiveFilter) aria-current="page" @endif
                       class="inline-flex h-[36px] items-center justify-center rounded-[4px] border px-[12px] py-[5px] text-center text-[15px] text-[#231f20] transition-all
                            {{ $isActiveFilter
                                ? 'border-black bg-white'
                                : 'border-[#f4f4f4] bg-[#f4f4f4] hover:border-gray-50 hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)]' }}">
                        {{ $filterLabel }}
                        <span class="ml-1 text-[12px] font-normal text-[#8c8c8c]">{{ $orderCounts[$filterKey] }}</span>
                    </a>
                </div>
            @endforeach
        </nav>
    </div>

    <div class="overflow-x-auto rounded-[10px] border border-[#e5e5e5] bg-white">
        <table class="w-full min-w-[980px] border-collapse text-left">
            <thead class="bg-[#f4f4f4]">
                <tr class="border-b border-[#e5e5e5]">
                    @foreach($sortableColumns as $field => $label)
                        @php
                            $nextDirection = $sort === $field
                                ? ($direction === 'asc' ? 'desc' : 'asc')
                                : $defaultDirections[$field];
                            $sortUrl = route('admin.orders.index', [
                                'filter' => $filter,
                                'sort' => $field,
                                'direction' => $nextDirection,
                            ]);
                        @endphp
                        <th class="whitespace-nowrap px-4 py-3 text-[14px] font-semibold text-[#231f20]"
                            aria-sort="{{ $sort === $field ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                            <a href="{{ $sortUrl }}" class="transition-colors hover:text-[#007eff]">
                                {{ $label }}
                                @if($sort === $field)
                                    <span aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                    @endforeach
                    <th class="whitespace-nowrap px-4 py-3 text-[14px] font-semibold text-[#231f20]">Статус</th>
                    @foreach(['created_at' => 'Создан', 'completed_at' => 'Завершён'] as $field => $label)
                        @php
                            $nextDirection = $sort === $field
                                ? ($direction === 'asc' ? 'desc' : 'asc')
                                : $defaultDirections[$field];
                            $sortUrl = route('admin.orders.index', [
                                'filter' => $filter,
                                'sort' => $field,
                                'direction' => $nextDirection,
                            ]);
                        @endphp
                        <th class="whitespace-nowrap px-4 py-3 text-[14px] font-semibold text-[#231f20]"
                            aria-sort="{{ $sort === $field ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                            <a href="{{ $sortUrl }}" class="transition-colors hover:text-[#007eff]">
                                {{ $label }}
                                @if($sort === $field)
                                    <span aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                    @endforeach
                    <th class="whitespace-nowrap px-4 py-3 text-[14px] font-semibold text-[#231f20]">Действие</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e5e5e5] text-[15px] text-black">
                @forelse($orders as $order)
                    @php
                        $statusClass = match ($order->profile_status) {
                            \App\Models\Order::ORDER_COMPLETED => 'text-[#8bc34a]',
                            \App\Models\Order::ORDER_CANCELLED => 'text-[#979797]',
                            default => 'text-[#2c9cdd]',
                        };
                    @endphp
                    <tr class="transition-colors hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="text-[15px] text-[#007eff] transition-colors hover:text-[#0064cc]">
                                №{{ $order->id }}
                            </a>
                            <span class="text-[12px] text-[#8c8c8c]">(id: {{ $order->id }})</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            {{ trim($order->name.' '.$order->last_name) ?: '—' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $order->email }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            {{ number_format((float) $order->total, 2, '.', ' ') }} <i class="nbrb-icon">BYN</i>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 {{ $statusClass }}">{{ $order->profile_status_label }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $order->created_at?->format('d.m.Y') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $order->completed_at?->format('d.m.Y') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="text-[15px] text-[#007eff] transition-colors hover:text-[#0064cc]">
                                Открыть
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-[15px] text-[#8c8c8c]">
                            Заказы не найдены.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $orders->links() }}
    </div>
</div>
@endsection
