@extends('layouts.auth')

@section('title', 'Подтверждение email')

@section('content')
    <p class="text-center text-gray-700 mb-4">
        Мы отправили письмо с подтверждением на ваш email.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="text-center">
        @csrf
        <button class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
            Отправить повторно
        </button>
    </form>

    <p class="text-center text-sm mt-4">
        <a href="/" class="text-red-600 hover:underline">На главную</a>
    </p>
@endsection
