@extends('admin.layouts.admin')

@section('header-title', 'Категории')

@section('content')
    <div class="mx-auto w-full max-w-[1300px]">
        <h1 class="mb-5 text-[26px] font-semibold leading-none text-[#231f20]">Категории</h1>

        <a href="{{ route('admin.categories.create') }}"
           class="mb-5 inline-flex rounded-[6px] bg-[#007eff] px-4 py-2 text-[15px] text-white transition-colors hover:bg-[#0064cc]">
            Создать категорию
        </a>

        <div class="overflow-x-auto rounded-[10px] border border-[#e5e5e5] bg-white">
            <table class="w-full border-collapse text-left text-[15px]">
                <thead class="bg-[#f4f4f4]">
                    <tr>
                        <th class="px-4 py-3 font-semibold">ID</th>
                        <th class="px-4 py-3 font-semibold">Название</th>
                        <th class="px-4 py-3 font-semibold">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e5e5]">
                    @foreach($categories as $c)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $c->id }}</td>
                            <td class="px-4 py-3">{{ $c->title }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-4">
                                    <a class="text-[#007eff] transition-colors hover:text-[#0064cc]"
                                       href="{{ route('admin.categories.edit', $c) }}">Редактировать</a>
                                    <form action="{{ route('admin.categories.destroy', $c) }}" method="POST"
                                          onsubmit="return confirm('Удалить категорию?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-[#dc092e] transition-colors hover:text-[#a80723]">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $categories->links() }}</div>
    </div>
@endsection
