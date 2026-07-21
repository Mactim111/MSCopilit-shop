@extends('layouts.auth')

@section('title', 'Восстановление пароля')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Ваш Email</label>
            <input type="email" name="email" class="w-full border-gray-300 rounded-lg border px-3" required>
        </div>

        <button class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
            Отправить ссылку
        </button>

        <p class="text-center text-sm mt-4">
            <a href="{{ route('login') }}" class="text-red-600 hover:underline">Назад ко входу</a>
        </p>
    </form>
@endsection
