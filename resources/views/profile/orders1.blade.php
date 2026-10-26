@extends('layouts.main')

@section('title', 'Мои заказы')

@section('content')
    <div class="w-full px-8 py-10">
        <h1 class="mb-8 text-left text-3xl font-bold">Заказы</h1>

        <div class="divide-y rounded-xl bg-white shadow">
            @forelse($orders as $order)
                <a href="{{ route('profile.order', $order) }}"
                   class="block p-6 transition hover:bg-gray-50">
                    <div class="flex justify-between">
                        <div>
                            <div class="text-lg font-semibold">Заказ #{{ $order->id }}</div>
                            <div class="text-sm text-gray-600">{{ $order->created_at->format('d.m.Y H:i') }}</div>
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
            @empty
                <p class="p-6 text-[#8c8c8c]">Заказов пока нет.</p>
            @endforelse
        </div>
    </div>
@endsection
