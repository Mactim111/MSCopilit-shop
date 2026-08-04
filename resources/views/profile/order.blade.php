@extends('layouts.main')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8">

        <!-- Заголовок -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Заказ №{{ $order->id }}</h1>
            <p class="text-gray-600 mt-1">Оформлен: {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>

        <!-- Карточка статуса -->
        <div class="bg-white shadow rounded-xl p-6 mb-8 border border-gray-100">
            <h2 class="text-xl font-semibold mb-4">Статус заказа</h2>

            <div class="flex items-center gap-3">
            <span class="px-4 py-2 rounded-full text-sm font-medium
                @if($order->status === 'new') bg-blue-100 text-blue-700
                @elseif($order->status === 'paid') bg-green-100 text-green-700
                @elseif($order->status === 'canceled') bg-red-100 text-red-700
                @else bg-gray-100 text-gray-700 @endif">
                {{ ucfirst($order->status) }}
            </span>
            </div>
        </div>

        <!-- Товары -->
        <div class="bg-white shadow rounded-xl p-6 border border-gray-100">
            <h2 class="text-xl font-semibold mb-6">Товары в заказе</h2>

            <div class="space-y-6">
                @foreach($order->items as $item)
                    <div class="flex items-center gap-6 pb-6 border-b last:border-b-0">

                        <!-- Фото товара -->
                        <div class="w-24 h-24 flex-shrink-0">
                            <img src="{{ $item->variant->mainImage() }}"
                                 class="w-full h-full object-cover rounded-lg shadow-sm">
                        </div>

                        <!-- Информация -->
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $item->variant->title }}
                            </h3>

                            <p class="text-gray-600 mt-1">
                                Количество: <span class="font-medium">{{ $item->quantity }}</span>
                            </p>

                            <p class="text-gray-600">
                                Цена за шт.:
                                <span class="font-medium">{!! $item->variant->formattedPrice() !!}</span>
                            </p>
                        </div>

                        <!-- Сумма -->
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">
                                @php
                                    $subtotal = $item->price * $item->quantity;
                                    $subtotalFormatted = number_format($subtotal, 2, '.', ' ');
                                    [$whole, $fraction] = explode('.', $subtotalFormatted);
                                @endphp
                                {!! $whole . '<span class="text-sm">.</span><span class="text-sm">' . $fraction . '</span> <i class="nbrb-icon">BYN</i>' !!}
                            </p>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <!-- Итог -->
        <div class="bg-white shadow rounded-xl p-6 mt-8 border border-gray-100">
            <h2 class="text-xl font-semibold mb-4">Итог заказа</h2>

            <div class="flex justify-between text-lg font-medium text-gray-900">
                <span>Сумма заказа:</span>
                @php
                    $totalFormatted = number_format($order->total, 2, '.', ' ');
                    [$whole, $fraction] = explode('.', $totalFormatted);
                @endphp
                <span>{!! $whole . '<span class="text-sm">.</span><span class="text-sm">' . $fraction . '</span> <i class="nbrb-icon">BYN</i>' !!}</span>
            </div>
        </div>

        <!-- Кнопка назад -->
        <div class="mt-8">
            <a href="{{ route('profile.orders') }}"
               class="inline-block px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition">
                ← Вернуться к списку заказов
            </a>
        </div>

    </div>
@endsection
