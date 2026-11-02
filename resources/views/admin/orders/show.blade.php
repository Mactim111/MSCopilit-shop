@extends('admin.layouts.admin')

@section('header-title', 'Просмотр заказа')

@section('content')
<div class="ml-4"><br>
    <h1 class="text-[18px] font-bold">Заказ №{{ $order->id }}</h1>
    <br>
    <p><b>Имя:</b> {{ $order->name }}</p>
    <p><b>Фамилия:</b> {{ $order->last_name ?: '—' }}</p>
    <p><b>Email:</b> {{ $order->email }}</p>
    <p><b>Телефон:</b> {{ $order->phone }}</p>
    <p><b>Адрес:</b> {{ $order->address }}</p>
    <br>

    <h3 class="text-[18px] font-bold">Товары</h3>

    <table border="1" cellpadding="5">
        <tr>
            <th class="text-[15px] font-bold">Название</th>
            <th class="text-[15px] font-bold">Цена</th>
            <th class="text-[15px] font-bold">Кол-во</th>
            <th class="text-[15px] font-bold">Сумма</th>
        </tr>

        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->title }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->subtotal }}</td>
            </tr>
        @endforeach
    </table><br>

    <h3 class="text-[18px] font-bold">Этап заказа</h3>
    <p class="text-[15px] font-semibold text-blue-800">
        @switch($order->status)
            @case(\App\Models\Order::STATUS_NEW) Создан @break
            @case(\App\Models\Order::STATUS_PROCESSING) Комплектация @break
            @case(\App\Models\Order::STATUS_READY_FOR_PICKUP) Готов к выдаче @break
            @case(\App\Models\Order::STATUS_COMPLETED) Завершён @break
            @case(\App\Models\Order::STATUS_CANCELLED) Отменён @break
        @endswitch
    </p>
    <br>

    <p class="text-[18px] font-bold">Состояние оплаты: <span class="text-[15px] text-green-800">{{ $order->payment_status }}</span></p>
    @if($order->cancellation_requested_at)
        <p class="text-[15px] font-semibold text-red-800">Покупатель запросил отмену {{ $order->cancellation_requested_at->format('d.m.Y H:i') }}.</p>
    @endif
    <br>

    @unless($order->isFinalized())
        <h3 class="text-[18px] font-bold">Действия с заказом</h3>
        <div class="text-[15px] font-semibold text-red-800">
        @php
            $availableActions = match ($order->status) {
                \App\Models\Order::STATUS_NEW => [
                    'start_processing' => 'Начать комплектацию',
                    'cancel' => 'Подтвердить отмену',
                ],
                \App\Models\Order::STATUS_PROCESSING => [
                    'mark_ready' => 'Отметить «Готов к выдаче»',
                    'cancel' => 'Подтвердить отмену',
                ],
                \App\Models\Order::STATUS_READY_FOR_PICKUP => [
                    'complete' => 'Подтвердить получение покупателем',
                    'cancel' => 'Подтвердить отмену',
                ],
                default => [],
            };
        @endphp

        @foreach($availableActions as $action => $label)
            <form action="{{ route('admin.orders.transition', $order) }}"
                  method="POST"
                  class="inline-block"
                  @if(in_array($action, ['complete', 'cancel'], true))
                      onsubmit="return confirm('{{ $action === 'complete' ? 'Подтвердить фактическую выдачу заказа? Остатки будут списаны.' : 'Подтвердить отмену заказа? Резерв будет освобождён.' }}')"
                  @endif>
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="{{ $action }}">
                <button type="submit" class="block h-[40px] mt-5 mr-5 rounded-lg border border-red-600 bg-white px-14 py-2 text-[15px] font-semibold text-red-600 
                    hover:bg-red-600 hover:text-white text-center">{{ $label }}</button>
            </form>
        @endforeach

        @if($order->cancellation_requested_at)
            <form action="{{ route('admin.orders.transition', $order) }}"
                  method="POST"
                  class="inline-block"
                  onsubmit="return confirm('Отклонить запрос покупателя на отмену? Заказ продолжит обрабатываться.')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="reject_cancellation">
                <button type="submit" class="block h-[40px] mt-5 rounded-lg border border-red-600 bg-white px-14 py-2 text-[15px] font-semibold text-red-600 
                    hover:bg-red-600 hover:text-white">
                    Отклонить запрос отмены
                </button>
            </form>
        
        @endif
        </div>
    @else
        <p>Заказ финализирован; изменить его статус через админ-панель нельзя.</p>
    @endunless

</div>
@endsection
