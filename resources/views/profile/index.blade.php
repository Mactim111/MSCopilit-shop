@extends('layouts.main')

@section('title', 'Профиль')

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


        <div class="mb-1 mt-1">
            @if(session('success'))
                <div style="color: green">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div style="color: red">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>

        {{-- Основная информация --}}
        <div class="bg-white p-6 rounded-xl shadow mb-3">
            <h2 class="text-xl font-semibold mb-4">Основная информация</h2>

            <h2 class="text-xl font-semibold mb-4">Аватар</h2>

            @if($user->avatar)
                <img src="{{ $user->avatar_url }}" class="w-24 h-24 rounded-full object-cover">
            @else
                <p>Аватар не загружен</p>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-2 font-medium text-gray-800">Новый аватар</label>

                    <!-- Обёртка -->
                    <label class="inline-flex items-center px-5 py-3 bg-gray-900 text-white rounded-lg cursor-pointer hover:bg-gray-800 transition shadow">
                        <span>Выбрать файл</span>

                        <!-- Скрытый input -->
                        <input type="file" name="avatar" class="hidden" id="avatarInput">
                    </label>

                    <!-- Имя выбранного файла -->
                    <p id="avatarFileName" class="text-sm text-gray-600 mt-2"></p>

                    @error('avatar')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- поля имени и email -->

                <label class="block mb-1 font-medium">Имя<br>
                    <input class="w-full px-3 border border-gray-300 rounded-lg" type="text" name="name"
                           value="{{ old('name', $user->name) }}">
                </label>

                <label class="block mb-1 font-medium">Email<br>
                    <input class="w-full px-3 border border-gray-300 rounded-lg" type="email" name="email"
                           value="{{ old('email', $user->email) }}">
                </label>

                <button type="submit"
                        class="bg-red-600 mt-3 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">Сохранить
                </button>
            </form>
        </div>

        {{-- Изменение пароля --}}
        <div class="bg-white p-6 rounded-xl shadow mb-3">
            <h2 class="text-xl font-semibold mb-4">Изменить пароль</h2>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf

                <label class="block mb-1 font-medium">Текущий пароль<br>
                    <input class="w-full px-3 border border-gray-300 rounded-lg" type="password"
                           name="current_password">
                </label>

                <label class="block mb-1 font-medium">Новый пароль<br>
                    <input class="w-full px-3 border border-gray-300 rounded-lg" type="password" name="password">
                </label>

                <label class="block mb-1 font-medium">Повторите новый пароль<br>
                    <input class="w-full px-3 border border-gray-300 rounded-lg" type="password"
                           name="password_confirmation">
                </label>

                <button type="submit"
                        class="bg-red-600 mt-3 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">Обновить
                    пароль
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow mb-3">
            <h2 class="text-xl font-semibold mb-4">Адреса доставки</h2>

            @if($user->addresses->count())
                <ul>
                    @foreach($user->addresses as $addr)
                        <li>
                            <b>{{ $addr->label ?? 'Адрес' }}:</b>
                            <div>{{ $addr->address_line }}, {{ $addr->city }},
                                {{ $addr->state }} {{ $addr->zip }}, {{ $addr->country }}
                            </div>

                            <div class="flex justify-between">
                                <a href="{{ route('profile.address.edit', $addr) }}"
                                   class="mb-2 bg-green-600 mt-3 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">Редактировать</a>

                                <form action="{{ route('profile.address.delete', $addr) }}" method="POST"
                                      class="space-y-4">
                                    @csrf @method('DELETE')
                                    <button
                                        class="mb-2 bg-red-600 mt-3 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                                        Удалить
                                    </button>
                                </form>
                            </div>

                        </li>
                    @endforeach
                </ul>
            @else
                <p>Адресов пока нет</p>
            @endif

            <div>
                <h3 class="text-xl font-semibold mb-4">Добавить новый адрес</h3>

                <form action="{{ route('profile.address.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <label class="block mb-1 font-medium">Название (Дом, Работа)<br>
                        <input class="w-full px-3 border border-gray-300 rounded-lg" type="text" name="label">
                    </label>

                    <label class="block mb-1 font-medium">Адрес<br>
                        <input class="w-full px-3 border border-gray-300 rounded-lg" type="text" name="address_line"
                               required>
                    </label>

                    <label class="block mb-1 font-medium">Город<br>
                        <input class="w-full px-3 border border-gray-300 rounded-lg" type="text" name="city" required>
                    </label>

                    <label class="block mb-1 font-medium">Штат<br>
                        <input class="w-full px-3 border border-gray-300 rounded-lg" type="text" name="state">
                    </label>

                    <label class="block mb-1 font-medium">Индекс<br>
                        <input class="w-full px-3 border border-gray-300 rounded-lg" type="text" name="zip">
                    </label>

                    <label class="block mb-1 font-medium">Страна<br>
                        <input class="w-full px-3 border border-gray-300 rounded-lg" type="text" name="country"
                               value="USA" required>
                    </label>

                    <button class="bg-red-600 mt-3 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition"
                            type="submit">Добавить адрес
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('avatarInput').addEventListener('change', function () {
            const fileName = this.files[0]?.name ?? '';
            document.getElementById('avatarFileName').textContent = fileName;
        });
    </script>

@endsection

