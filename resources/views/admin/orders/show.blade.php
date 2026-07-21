@extends('admin.layout')

@section('content')
    <h1>Заказ №{{ $order->id }}</h1>

    <p><b>Имя:</b> {{ $order->name }}</p>
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

    <h3>Обновить статус</h3>

    <form action="{{ route('admin.orders.update', $order) }}" method="POST">
        @csrf @method('PUT')
        <select name="status">
            <option value="new"      @selected($order->status=='new')>Новый</option>
            <option value="paid"     @selected($order->status=='paid')>Оплачен</option>
            <option value="shipped"  @selected($order->status=='shipped')>Отправлен</option>
            <option value="cancelled"@selected($order->status=='cancelled')>Отменён</option>
        </select>
        <button>Сохранить</button>
    </form>

@endsection
