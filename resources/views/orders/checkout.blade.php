@extends('layouts.main')

@section('title', 'Оформление заказа')

@section('content')
    <div class="max-w-4xl mx-auto px-6 py-12">

        <h1 class="text-3xl font-bold mb-8">Оформление заказа</h1>

        <form action="{{ route('orders.store') }}" method="POST" class="space-y-10">
            @csrf

            {{-- Блок выбора адреса --}}
            <div class="bg-white shadow rounded-xl p-6">
                <h2 class="text-xl font-semibold mb-4">Адрес доставки</h2>

                {{-- Сохранённые адреса --}}
                @foreach($addresses as $addr)
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio"
                               name="address_id"
                               value="{{ $addr->id }}"
                               class="mt-1"
                            @checked(old('address_id', $defaultAddressId) == $addr->id)>
                        <div>
                            <div class="font-medium">{{ $addr->label ?? 'Адрес' }}</div>
                            <div class="text-gray-600 text-sm">{{ $addr->address_line }}</div>
                        </div>
                    </label>
                @endforeach

                {{-- Новый адрес --}}
                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 mt-4">
                    <input type="radio"
                           name="address_id"
                           value=""
                           class="mt-1"
                        @checked(old('address_id') === '' || ($addresses->isEmpty() && old('address_id') === null))>

                    <div class="w-full">
                        <div class="font-medium">Ввести новый адрес</div>

                        <textarea name="address"
                                  placeholder="Введите новый адрес..."
                                  rows="3"
                                  class="mt-2 w-full border border-gray-300 rounded-lg p-3 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>

                        @error('address')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </label>

                {{-- Контактные данные --}}
            <div class="bg-white shadow rounded-xl p-6">
                <h2 class="text-xl font-semibold mb-4">Контактные данные</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-medium mb-1">Имя</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                               class="w-full border-gray-300 rounded-lg border @error('name') border-red-500 @enderror px-3">
                        @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                               class="w-full border-gray-300 rounded-lg border @error('email') border-red-500 @enderror px-3">
                        @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Телефон</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full border-gray-300 rounded-lg border @error('phone') border-red-500 @enderror px-3">
                        @error('phone')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Кнопка --}}
            <button type="submit"
                    class="w-full md:w-auto bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition">
                Подтвердить заказ
            </button>

        </form>
    </div>
@endsection
