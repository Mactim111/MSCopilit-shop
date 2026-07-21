@extends('admin.layouts.admin')

@section('content')
    <h1>Редактировать товар</h1>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <label>Название<br>
            <input type="text" name="title" value="{{ old('title', $product->title) }}">
        </label><br><br>

        <label>Описание<br>
            <textarea name="description">{{ old('description', $product->description) }}</textarea>
        </label><br><br>

        <label>Цена<br>
            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}">
        </label><br><br>

        <label>Остаток<br>
            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}">
        </label><br><br>

        <label>Категория<br>
            <select name="category_id">
                <option value="">Без категории</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected($product->category_id == $c->id)>
                        {{ $c->title }}
                    </option>
                @endforeach
            </select>
        </label><br><br>

        <div>
            <label class="block mb-2 font-medium text-gray-800">Главное изображение</label>
            @if($product->image)
                <p>Текущее изображение:</p>
                <img src="{{ $product->image_url }}" width="150">
                <br><br>
            @endif
            <!-- Обёртка -->
            <label class="inline-flex items-center px-5 py-3 bg-gray-900 text-white rounded-lg cursor-pointer hover:bg-gray-800 transition shadow">
                <span>Выбрать файл</span>

                <!-- Скрытый input -->
                <input type="file" name="image" class="hidden" id="imageInput">
            </label>

            <!-- Имя выбранного файла -->
            <p id="imageFileName" class="text-sm text-gray-600 mt-2"></p>

            @error('image')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

        </div>


        <div>
            <label class="block font-medium mb-1">Галерея</label>
            @if($product->images->count())
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    @foreach($product->images as $img)
                        <label class="block">
                            <img src="{{ $img->url }}" width="150" class="rounded mb-2">
                            <input type="checkbox" name="remove_gallery[]" value="{{ $img->id }}">
                            <span class="text-sm">Удалить</span>
                        </label>
                    @endforeach
                </div>
            @else
                <p>Нет изображений</p>
            @endif
            <input type="file" name="images[]" multiple class="w-full border-gray-300 rounded-lg">
        </div>

        <button class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 mb-3 mt-3" type="submit">Сохранить</button>
    </form>

@endsection
