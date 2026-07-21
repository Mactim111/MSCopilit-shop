@extends('admin.layout')

@section('content')
    <h1>Заказы</h1>

    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Покупатель</th>
            <th>Email</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th></th>
        </tr>

        @foreach($orders as $o)
            <tr>
                <td>{{ $o->id }}</td>
                <td>{{ $o->name }}</td>
                <td>{{ $o->email }}</td>
                <td>{{ $o->total }} ₽</td>
                <td>{{ $o->status }}</td>
                <td><a href="{{ route('admin.orders.show', $o) }}">Открыть</a></td>
            </tr>
        @endforeach
    </table>

    {{ $orders->links() }}
@endsection
