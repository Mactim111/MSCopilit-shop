@extends('layouts.main')

@section('title', 'Избранное')

@section('content')
    <div class="max-w-6xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-semibold mb-6">Избранное</h1>

        @forelse($favorites as $variant)
            <x-variant-list-card :variant="$variant" :isFavorite="true" />
        @empty
            <p class="text-gray-600">В избранном пока нет товаров.</p>
        @endforelse

        {{ $favorites->links() }}
    </div>
@endsection
