@extends('admin.layouts.admin')

@section('content')
    <h1>Создать товар</h1>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label>Название<br>
            <input type="text" name="title" value="{{ old('title') }}">
        </label><br><br>

        <label>Описание<br>
            <textarea name="description">{{ old('description') }}</textarea>
        </label><br><br>

        <label>Цена<br>
            <input type="number" step="0.01" name="price" value="{{ old('price') }}">
        </label><br><br>

        <label>Остаток<br>
            <input type="number" name="stock" value="{{ old('stock', 0) }}">
        </label><br><br>

        <label>Категория<br>
            <select name="category_id">
                <option value="">Без категории</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                @endforeach
            </select>
        </label><br><br>

        <label>Изображение<br>
            <input type="file" name="image">
        </label><br><br>

        <label>Галерея изображений<br>
            <input type="file" name="images[]" multiple>
        </label>
        <br><br>

        <button type="submit">Создать</button>
    </form>

@endsection
