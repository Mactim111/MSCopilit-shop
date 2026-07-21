@extends('admin.layouts.admin')

@section('header-title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">

    <!-- Товары -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
            @include('admin.svg.package')
        </div>
        <div>
            <p class="text-gray-500 text-sm">Товаров всего</p>
            <p class="text-2xl font-bold">{{ $productsCount }}</p>
        </div>
    </div>

    <!-- Товарные предложения -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
            @include('admin.svg.layers')
        </div>
        <div>
            <p class="text-gray-500 text-sm">Предложений</p>
            <p class="text-2xl font-bold">{{ $offersCount }}</p>
        </div>
    </div>

    <!-- Категории -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 bg-green-100 text-green-600 rounded-lg">
            @include('admin.svg.folder')
        </div>
        <div>
            <p class="text-gray-500 text-sm">Категорий</p>
            <p class="text-2xl font-bold">{{ $categoriesCount }}</p>
        </div>
    </div>

    <!-- Заказы -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 bg-yellow-100 text-yellow-600 rounded-lg">
            @include('admin.svg.cart')
        </div>
        <div>
            <p class="text-gray-500 text-sm">Заказов</p>
            <p class="text-2xl font-bold">{{ $ordersCount }}</p>
        </div>
    </div>

    <!-- Пользователи -->
    <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 bg-red-100 text-red-600 rounded-lg">
            @include('admin.svg.users')
        </div>
        <div>
            <p class="text-gray-500 text-sm">Пользователей</p>
            <p class="text-2xl font-bold">{{ $usersCount }}</p>
        </div>
    </div>

</div>

@endsection

