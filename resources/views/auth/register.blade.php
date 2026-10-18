@extends('layouts.auth')

@section('title', 'Регистрация')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Имя</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg px-3 @error('name') border-red-500 @enderror" placeholder="Имя" required>
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block mb-1 font-medium">Фамилия</label>
            <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full border border-gray-300 rounded-lg px-3 @error('last_name') border-red-500 @enderror" placeholder="Фамилия">
            @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="block mb-1 font-medium">Пол <span class="text-[#DC092E]">*</span></span>
            <div class="flex gap-4">
                <label class="flex items-center gap-2">
                    <input type="radio" name="gender" value="male" @checked(old('gender', 'male') === 'male') required>
                    Мужской
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="gender" value="female" @checked(old('gender') === 'female') required>
                    Женский
                </label>
            </div>
            @error('gender')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block mb-1 font-medium">Дата рождения</label>
            <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full border border-gray-300 rounded-lg px-3 @error('birth_date') border-red-500 @enderror">
            @error('birth_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block mb-1 font-medium">Телефон <span class="text-[#DC092E]">*</span></label>
            <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-300 rounded-lg px-3 @error('phone') border-red-500 @enderror" placeholder="+375..." required>
            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg px-3 @error('email') border-red-500 @enderror" placeholder="Email" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Пароль</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 @error('password') border-red-500 @enderror" placeholder="Пароль" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Повторите пароль</label>
            <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-3 @error('password_confirmation') border-red-500 @enderror" placeholder="Подтверждение пароля" required>
        </div>

        <button class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
            Создать аккаунт
        </button>

        <p class="text-center text-sm mt-4">
            Уже есть аккаунт?
            <a href="{{ route('login') }}" class="text-red-600 hover:underline">Войти</a>
        </p>
    </form>
@endsection
