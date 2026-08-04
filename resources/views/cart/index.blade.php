@extends('layouts.main')

@section('title', 'Корзина')

@section('content')
    <div class="max-w-7xl mx-auto mt-4 mb-2">

        <h1 class="text-3xl font-bold mb-8">Корзина</h1>

        @if($items->isEmpty())
            <div class="bg-white p-10 rounded-xl shadow text-center">
                <p class="text-lg text-gray-600 mb-6">Ваша корзина пуста</p>
                <a href="/catalog"
                   class="inline-block bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition">
                    Перейти в каталог
                </a>
            </div>
        @else

            <div class="bg-white rounded-xl shadow p-6 mb-8">
                <table class="w-full">
                    <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-3">Товар</th>
                        <th class="pb-3">Цена</th>
                        <th class="pb-3">Кол-во</th>
                        <th class="pb-3">Сумма</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($items as $item)
                        <tr class="border-b">
                            <td class="py-4 flex items-center gap-4">
                                <img src="{{ $item->variant->mainImage() }}" alt="{{ $item->variant->title }}"
                                     class="w-20 h-20 object-cover rounded">
                                <span class="font-medium">{{ $item->variant->title }}</span>
                            </td>

                            <td class="py-4 font-bold text-2xl">
                                {!! $item->variant->formattedPrice(24, 24) !!}
                            </td>

                            <td class="py-4">
                                <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')

                                    <input type="number"
                                           name="quantity"
                                           min="1"
                                           value="{{ $item->quantity }}"
                                           class="w-16 border-gray-300 rounded-lg border p-2" 
                                           onchange="this.form.submit()">   
                                </form>
                            </td>

                            <td class="py-4 font-bold text-2xl">
                                {!! $item->formattedSubtotal(24, 24) !!} 
                            </td>


                            <td class="py-4 text-right">
                                <form action="{{ route('cart.remove', $item) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Итог --}}
            <div class="bg-white rounded-xl shadow p-6 flex justify-between items-center">
                <div class="font-bold text-3xl">
                    Итого: {!! $formattedTotal !!} 
                </div>

                 <a href="{{ route('orders.checkout') }}"
                   class="bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition">
                    Оформить заказ
                </a>
                
            </div>

        @endif

    </div>
@endsection
