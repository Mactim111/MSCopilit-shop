@extends('admin.layout')

@section('content')
    <h1>Заказ №{{ $order->id }}</h1>

    <p><b>Имя:</b> {{ $order->name }}</p>
    <p><b>Фамилия:</b> {{ $order->last_name ?: '—' }}</p>
    <p><b>Email:</b> {{ $order->email }}</p>
    <p><b>Телефон:</b> {{ $order->phone }}</p>
    <p><b>Адрес:</b> {{ $order->address }}</p>

    <h3>Товары</h3>

    <table border="1" cellpadding="5">
        <tr>
            <th>Название</th>
            <th>Цена</th>
            <th>Кол-во</th>
            <th>Сумма</th>
        </tr>

        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->title }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->subtotal }}</td>
            </tr>
        @endforeach
    </table>

    <h3>Этап заказа</h3>
    <p>
        @switch($order->status)
            @case(\App\Models\Order::STATUS_NEW) Создан @break
            @case(\App\Models\Order::STATUS_PROCESSING) Комплектация @break
            @case(\App\Models\Order::STATUS_READY_FOR_PICKUP) Готов к выдаче @break
            @case(\App\Models\Order::STATUS_COMPLETED) Завершён @break
            @case(\App\Models\Order::STATUS_CANCELLED) Отменён @break
        @endswitch
    </p>

    <p>Состояние оплаты: {{ $order->payment_status }}</p>
    @if($order->cancellation_requested_at)
        <p>Покупатель запросил отмену {{ $order->cancellation_requested_at->format('d.m.Y H:i') }}.</p>
    @endif

    @unless($order->isFinalized())
        <h3>Действия с заказом</h3>
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
                <button type="submit">{{ $label }}</button>
            </form>
        @endforeach
    @else
        <p>Заказ финализирован; изменить его статус через админ-панель нельзя.</p>
    @endunless

@endsection
