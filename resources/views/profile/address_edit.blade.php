@extends('layouts.main')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10">

        <!-- Заголовок -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Редактирование адреса</h1>
            <p class="text-gray-600 mt-1">Обновите данные доставки</p>
        </div>

        <!-- Карточка формы -->
        <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-100">

            <form action="{{ route('profile.address.update', $address) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Название (Дом, Работа) -->
                <div class="mb-6">
                    <label class="block text-gray-800 font-medium mb-2">Дом, Работа</label>
                    <input type="text" name="label" value="{{ old('label', $address->label) }}"
                           class="w-full px-4 py-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition
                           @error('label') border-red-500 @enderror">
                    @error('label')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Адрес -->
                <div class="mb-6">
                    <label class="block text-gray-800 font-medium mb-2">Адрес</label>
                    <input type="text" name="address_line" value="{{ old('address_line', $address->address_line) }}"
                           class="w-full px-4 py-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition
                           @error('address_line') border-red-500 @enderror">
                    @error('address_line')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Город -->
                <div class="mb-6">
                    <label class="block text-gray-800 font-medium mb-2">Город</label>
                    <input type="text" name="city" value="{{ old('city', $address->city) }}"
                           class="w-full px-4 py-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition
                           @error('city') border-red-500 @enderror">
                    @error('city')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Регион (область, район) -->
                <div class="mb-6">
                    <label class="block text-gray-800 font-medium mb-2">Регион (область, район)</label>
                    <input type="text" name="state" value="{{ old('state', $address->state) }}"
                           class="w-full px-4 py-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition
                           @error('state') border-red-500 @enderror">
                    @error('state')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Почтовый индекс -->
                <div class="mb-8">
                    <label class="block text-gray-800 font-medium mb-2">Почтовый индекс</label>
                    <input type="text" name="zip" value="{{ old('zip', $address->zip) }}"
                           class="w-full px-4 py-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition
                           @error('zip') border-red-500 @enderror">
                    @error('zip')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Страна -->
                <div class="mb-6">
                    <label class="block text-gray-800 font-medium mb-2">Страна</label>
                    <input type="text" name="country"
                           value="{{ old('country', $address->country) }}"
                           class="w-full px-4 py-3 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition
                           @error('country') border-red-500 @enderror">
                    @error('country')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Кнопки -->
                <div class="flex items-center justify-between mt-8">
                    <a href="{{ route('profile') }}"
                       class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
                        ← Назад
                    </a>

                    <button type="submit"
                            class="px-8 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-semibold shadow">
                        Сохранить изменения
                    </button>
                </div>

            </form>

        </div>

    </div>
@endsection
