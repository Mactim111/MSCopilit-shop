@extends('layouts.auth')

@section('title', 'Регистрация')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Имя</label>
            <input type="text" name="name" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg px-3 @error('name') border-red-500 @enderror" placeholder="Имя" required>
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
