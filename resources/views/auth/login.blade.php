@extends('layouts.auth')

@section('title', 'Вход в аккаунт')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg px-3 @error('email') border-red-500 @enderror" placeholder="Email" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Пароль</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 @error('password') border-red-500 @enderror" placeholder="Пароль" required>
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember">
                <span class="text-sm">Запомнить меня</span>
            </label>

            <a href="{{ route('password.request') }}" class="text-sm text-red-600 hover:underline">
                Забыли пароль
            </a>
        </div>

        <button class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
            Войти
        </button>

        <p class="text-center text-sm mt-4">
            Нет аккаунта?
            <a href="{{ route('register') }}" class="text-red-600 hover:underline">Регистрация</a>
        </p>
    </form>
@endsection

