@extends('admin.layout')

@section('content')
    <h1>Товары</h1>

    <a href="{{ route('admin.products.create') }}">Создать товар</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Категория</th>
            <th>Цена</th>
            <th>Остаток</th>
            <th>Изображение товара</th>
            <th>Галерея</th>
            <th></th>
        </tr>

        @foreach($products as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->title }}</td>
                <td>{{ $p->category->title ?? '-' }}</td>
                <td>{{ $p->price }} ₽</td>
                <td>{{ $p->stock }}</td>
                <td>
                    @if($p->image)
                        <img src="{{ $p->image_url }}" width="60">
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($p->images->count())
                        <img src="{{ $p->images->first()->url }}" width="60">
                    @else
                        —
                    @endif
                </td>

                <td>
                    <a href="{{ route('admin.products.edit', $p) }}">Редактировать</a>

                    
                    @if($p->deleted_at)
                    <form action="{{ route('admin.products.restore', $p) }}" method="POST">
                        @csrf
                        <button onclick="return confirm('Восстановить товар?')" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                            Восстановить
                        </button>
                    </form>
                    @else
                    <form action="{{ route('admin.products.destroy', $p) }}" method="POST">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Удалить товар?')">Удалить</button>
                    </form>
                    @endif

                </td>
            </tr>
        @endforeach
    </table>

    {{ $products->links() }}
@endsection
