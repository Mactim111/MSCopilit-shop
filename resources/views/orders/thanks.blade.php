@extends('layouts.main')

@section('title', 'Спасибо за заказ')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-20">
    <div class="bg-white shadow-xl rounded-2xl p-10 text-center">

        <h1 class="text-3xl font-bold mb-4">Спасибо за ваш заказ!</h1>

        <p class="text-gray-600 text-lg mb-8">
            Номер вашего заказа: <span class="font-semibold">#{{ $order->id }}</span>
        </p>

        <a href="{{ route('home') }}"
           class="px-8 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            На главную
        </a>
    </div>
</div>
@endsection
