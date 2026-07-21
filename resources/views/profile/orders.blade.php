@extends('layouts.main')

@section('title', 'Мои заказы')

@section('content')

    <div class="max-w-4xl mx-auto px-6 py-10">

        <div class="flex justify-center gap-4 mb-8">

            <a href="{{ route('profile') }}"
               class="px-5 py-2 rounded-lg border transition
              {{ request()->routeIs('profile')
                    ? 'bg-gray-900 text-white border-gray-900'
                    : 'bg-white text-gray-800 border-gray-300 hover:bg-gray-50' }}">
                Профиль
            </a>

            <a href="{{ route('profile.orders') }}"
               class="px-5 py-2 rounded-lg border transition
              {{ request()->routeIs('profile.orders')
                    ? 'bg-gray-900 text-white border-gray-900'
                    : 'bg-white text-gray-800 border-gray-300 hover:bg-gray-50' }}">
                Мои заказы
            </a>

        </div>

        <div class="max-w-4xl mx-auto px-6 py-10">
            <h1 class="text-center text-3xl font-bold mb-8">Заказы</h1>

            <div class="bg-white shadow rounded-xl divide-y">
                @foreach($orders as $order)
                    <a href="{{ route('profile.order', $order) }}"
                       class="block p-6 hover:bg-gray-50 transition">
                        <div class="flex justify-between">
                            <div>
                                <div class="font-semibold text-lg">Заказ #{{ $order->id }}</div>
                                <div
                                    class="text-gray-600 text-sm">{{ $order->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                            <div class="font-semibold">
                                @php
                                    $totalFormatted = number_format($order->total, 2, '.', ' ');
                                    [$whole, $fraction] = explode('.', $totalFormatted);
                                @endphp
                                {!! $whole . '<span class="text-sm">.</span><span class="text-sm">' . $fraction . '</span> <i class="nbrb-icon">BYN</i>' !!}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        </div>
    </div>
@endsection
