@extends('admin.layout')

@section('content')
    <h1>Категории</h1>

    <a href="{{ route('admin.categories.create') }}">Создать категорию</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th></th>
        </tr>

        @foreach($categories as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->title }}</td>
                <td>
                    <a href="{{ route('admin.categories.edit', $c) }}">Редактировать</a>
                    <form action="{{ route('admin.categories.destroy', $c) }}" method="POST">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Удалить категорию?')">Удалить</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>

    {{ $categories->links() }}
@endsection

