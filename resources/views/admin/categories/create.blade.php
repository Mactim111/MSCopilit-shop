@extends('admin.layout')

@section('content')
    <h1>Создать категорию</h1>

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf

        <label>Название<br>
            <input type="text" name="title" value="{{ old('title') }}">
        </label><br><br>

        <button type="submit">Создать</button>
    </form>

@endsection
