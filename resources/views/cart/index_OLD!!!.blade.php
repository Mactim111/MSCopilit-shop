@extends('layouts.main')

@section('title', 'Корзина')

@section('content')
    <div class="max-w-full mx-auto py-6">

        <h1 class="text-[28px] font-bold pb-5 border-b border-dashed border-gray-200">Корзина</h1>

        @if($items->isEmpty())
            <div class="p-10 text-center">
                <p class="text-[28px] text-gray-600 mb-5">Ваша корзина пуста</p>
                <a href="/catalog"
                   class="inline-block bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition">
                    Перейти в каталог
                </a>
            </div>
        @else

            <div>
                <table class="w-full">

                    <tbody>
                    @foreach($items as $item)
                        <tr class="border-b border-dashed border-gray-200 flex py-4">
                            <td class="p-[4px] flex gap-4">
                                <img src="{{ $item->variant->mainImage() }}" alt="{{ $item->variant->title }}"
                                     class="w-20 h-20 object-cover rounded">
                                <span class="font-bold text-[15px]">{{ $item->variant->title }}</span>
                            </td>

                            <td class="py-4 font-bold text-2xl">
                                {!! $item->variant->formattedPrice(24, 24) !!}
                            </td>

                            <td class="py-4">
                                <form action="{{ route('cart.update', $item) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    @method('PUT')

                                    <input type="number"
                                           name="quantity"
                                           min="1"
                                           value="{{ $item->quantity }}"
                                           class="w-[120px] h-[40px] border-gray-300 rounded-lg border px-[10px] text-center" 
                                           onchange="this.form.submit()">   
                                </form>
                            </td>

                            <td class="py-4 font-bold text-2xl">
                                {!! $item->formattedSubtotal(24, 24) !!} 
                            </td>


                            <td class="py-4 text-left">
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
            <div class="p-6 flex justify-between items-center">
                <div class="font-bold text-[28px]">
                    Итого: <span class="text-3xl">{!! $formattedTotal !!}</span>
                </div>

                 <a href="{{ route('orders.checkout') }}"
                   class="bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition">
                    Оформить заказ
                </a>
                
            </div>

        @endif

    </div>
@endsection
