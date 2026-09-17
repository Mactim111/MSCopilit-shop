@extends('layouts.main')

@section('title', 'Избранное')

@section('content')
    <div class="max-w-2xl mx-auto px-6 py-20 text-center">
        <h1 class="text-2xl font-semibold mb-4">Избранное</h1>
        <p class="text-gray-600 mb-8">
            Войдите или создайте личный кабинет, чтобы сохранять товары в избранное.
        </p>
        <a href="{{ route('login') }}"
           class="inline-block bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition">
            Войти
        </a>
    </div>
@endsection
