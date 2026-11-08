@extends('admin.layouts.admin')

@section('header-title', 'Создание категории')

@section('content')
    <div class="mx-auto w-full max-w-[800px]">
        <h1 class="mb-5 text-[26px] font-semibold leading-none text-[#231f20]">Создать категорию</h1>

        <form action="{{ route('admin.categories.store') }}" method="POST"
              class="rounded-[10px] border border-[#e5e5e5] bg-white p-6">
            @csrf
            <label for="category-title" class="mb-2 block text-[15px] font-medium text-[#231f20]">Название</label>
            <input id="category-title" type="text" name="title" value="{{ old('title') }}" required
                   class="mb-5 w-full rounded-[6px] border border-gray-300 px-4 py-2 text-[15px]">
            <button type="submit"
                    class="rounded-[6px] bg-[#007eff] px-5 py-2 text-[15px] text-white transition-colors hover:bg-[#0064cc]">
                Создать
            </button>
        </form>
    </div>

@endsection
