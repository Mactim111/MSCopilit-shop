@extends('admin.layout')

@section('content')
    <h1>Редактировать категорию</h1>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
        @csrf @method('PUT')

        <label>Название<br>
            <input type="text" name="title" value="{{ old('title', $category->title) }}">
        </label><br><br>

        <button type="submit">Сохранить</button>
    </form>

@endsection

