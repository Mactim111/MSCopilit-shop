@extends('layouts.auth')

@section('title', 'Новый пароль')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" class="w-full border-gray-300 rounded-lg  border px-3" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Новый пароль</label>
            <input type="password" name="password" class="w-full border-gray-300 rounded-lg  border px-3" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Повторите пароль</label>
            <input type="password" name="password_confirmation" class="w-full border-gray-300 rounded-lg  border px-3" required>
        </div>

        <button class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
            Сохранить пароль
        </button>
    </form>
@endsection

