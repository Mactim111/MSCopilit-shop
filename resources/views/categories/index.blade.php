@extends('layouts.main')

@section('title', 'Каталог товаров')

@section('content')

    <div class="max-w-7xl mx-auto px-6 py-10">

        <h1 class="text-3xl font-bold mb-8">Категории</h1>

            {{-- Категории --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($categories as $cat)
                            {{ $cat->title }}
                    @endforeach
            </div>

        <div class="mt-10">
            {{ $categories->links() }}
        </div>

    </div>

@endsection


