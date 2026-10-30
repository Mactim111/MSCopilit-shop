@extends('layouts.main')

@section('title', 'Оформление заказа')

@section('content')
    <div class="max-w-[1500px] mx-auto px-6 py-6">

        <h1 class="text-3xl font-bold mb-8">Оформление заказа</h1>

        <form action="{{ route('orders.store') }}" method="POST" class="space-y-10">
            @csrf
            {{-- ПЕРЕДАЕМ ВЫБРАННЫЕ ID В СТОРИ --}}
            @foreach($selectedIds as $id)
                <input type="hidden" name="items[]" value="{{ $id }}">
            @endforeach

            {{-- Контактные данные --}}
            <div class="border border-[#e5e5e5] rounded-[10px] p-6">
                <h2 class="text-xl font-semibold mb-4">Контактные данные</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-medium mb-1">Имя</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}"
                               class="w-full p-1 border-gray-300 rounded-[6px] border @error('name') border-red-500 @enderror px-3">
                        @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}"
                               class="w-full p-1 border-gray-300 rounded-[6px] border @error('email') border-red-500 @enderror px-3">
                        @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Фамилия</label>
                        <input type="text" name="last_name" value="{{ old('last_name', auth()->user()?->last_name) }}"
                               class="w-full p-1 border-gray-300 rounded-[6px] border @error('last_name') border-red-500 @enderror px-3">
                        @error('last_name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Телефон</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone) }}"
                               class="w-full p-1 border-gray-300 rounded-[6px] border @error('phone') border-red-500 @enderror px-3">
                        @error('phone')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Блок выбора адреса --}}
            <div class="border border-[#e5e5e5] rounded-[10px] p-6">
                <h2 class="text-xl font-semibold mb-4">Выберите удобный способ доставки:</h2>

                {{-- Сохранённые адреса --}}
                @foreach($addresses as $addr)
                    <label class="flex items-start gap-3 p-3 border border-white hover:border-black focus:border-black rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio"
                               name="address_id"
                               value="{{ $addr->id }}"
                               class="mt-1"
                            @checked(old('address_id', $defaultAddressId) == $addr->id)>
                        <div>
                            <div class="text-[15px] font-semibold">Использовать существующий адрес</div>
                            <div class="text-gray-600 text-sm">{{ $addr->country }}, {{ $addr->city }}, {{ $addr->address_line }}</div>
                        </div>
                    </label>
                @endforeach

                {{-- Новый адрес --}}
                <label class="flex items-start gap-3 p-3 border border-white hover:border-black focus:border-black rounded-lg cursor-pointer hover:bg-gray-50 mt-4">
                    <input type="radio"
                           name="address_id"
                           value=""
                           class="mt-1"
                        @checked(old('address_id') === '' || ($addresses->isEmpty() && old('address_id') === null))>

                    <div class="w-full">
                        <div class="text-[15px] font-semibold">Ввести новый адрес</div>

                        <textarea name="address"
                                  placeholder="Введите новый адрес..."
                                  rows="3"
                                  class="mt-2 w-full border border-gray-300 rounded-lg p-3 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>

                        @error('address')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </label>

                {{-- Самовывоз из пункта выдачи заказов --}}
                @foreach($addresses as $addr)
                    <label class="flex items-start gap-3 p-3 border border-white hover:border-black focus:border-black rounded-lg cursor-pointer hover:bg-gray-50 mt-4">
                        <input type="radio"
                               name="address_id"
                               value="{{ $addr->id }}"
                               class="mt-1"
                            @checked(old('address_id', $defaultAddressId) == $addr->id)>
                        <div>
                            <div class="text-[15px] font-semibold">Самовывоз из пункта выдачи заказов</div>
                            <div class="text-gray-600 text-sm">г.Брест, ул. Московская, 210 (ТЦ «Корона», 1 этаж). 11:00 - 20:00(Пн - Пт), 10:00 - 20:00(Сб - Вс).
                                Доступен к самовывозу с 04.11.2026 в течение 3 дней</div>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Блок выбора адреса --}}
            <div class="border border-[#e5e5e5] rounded-[10px] p-6">
                <h2 class="text-xl font-semibold mb-4">Выберите удобный способ оплаты:</h2>

                <label class="flex items-start gap-3 p-3 border border-white hover:border-black focus:border-black rounded-lg cursor-pointer hover:bg-gray-50 mt-4">
                    <input type="radio"
                            name="address_id"
                            value="{{ $addr->id }}"
                            class="mt-1"
                        @checked(old('address_id', $defaultAddressId) == $addr->id)>
                    <div>
                        <div class="text-[15px] font-semibold">Оплата при получении</div>
                        <div class="text-gray-600 text-sm">Наличными или банковской картой</div>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 border border-white hover:border-black focus:border-black rounded-lg cursor-pointer hover:bg-gray-50 mt-4">
                    <input type="radio"
                            name="address_id"
                            value="{{ $addr->id }}"
                            class="mt-1"
                        @checked(old('address_id', $defaultAddressId) == $addr->id)>
                    <div>
                        <div class="text-[15px] font-semibold">Онлайн</div>
                        <div class="text-gray-600 text-sm">Банковской картой Visa, MasterCard, Белкарт и др.</div>
                    </div>
                </label>
            </div>


            {{-- Кнопка --}}
            <button type="submit"
                    class="w-full md:w-auto bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition">
                Подтвердить заказ
            </button>

        </form>
    </div>
@endsection
