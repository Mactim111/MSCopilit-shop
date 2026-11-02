@extends('admin.layouts.admin')

@section('header-title', 'Товары')

@section('content')

<!-- Фильтры -->
<div class="bg-white shadow rounded-xl p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <!-- Поиск -->
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Поиск по названию..."
            class="border rounded-lg px-4 py-2 w-full">

        <!-- Категория -->
        <select name="category" class="border rounded-lg px-4 py-2 w-full">
            <option value="">Все категории</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category')==$cat->id)>
                {{ $cat->title }}
            </option>
            @endforeach
        </select>

        <!-- Наличие -->
        <select name="stock" class="border rounded-lg px-4 py-2 w-full">
            <option value="">Все товары</option>
            <option value="in" @selected(request('stock')==='in' )>В наличии</option>
            <option value="out" @selected(request('stock')==='out' )>Нет в наличии</option>
        </select>

        <!-- Цена -->
        <div class="flex gap-2">
            <input type="number" name="price_min" value="{{ request('price_min') }}"
                placeholder="Цена от" class="border rounded-lg px-4 py-2 w-full">
            <input type="number" name="price_max" value="{{ request('price_max') }}"
                placeholder="Цена до" class="border rounded-lg px-4 py-2 w-full">
        </div>

        <div class="md:col-span-4 flex justify-end">
            <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                Применить
            </button>
        </div>

    </form>
</div>

<!-- Таблица -->
<div class="bg-white shadow rounded-xl overflow-hidden">

    <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
        <table class="w-full border-collapse">

            <!-- Фиксированная шапка -->
            <thead class="bg-gray-100 text-left text-gray-700 text-sm uppercase sticky top-0 z-10">
                <tr>
                    @php
                    function sort_link($label, $field, $sort, $dir) {
                    $newDir = ($sort === $field && $dir === 'asc') ? 'desc' : 'asc';
                    return "?sort=$field&dir=$newDir";
                    }
                    @endphp

                    <th class="px-4 py-3"><a href="{{ sort_link('ID','id',$sort,$dir) }}">ID</a></th>
                    <th class="px-4 py-3"><a href="{{ sort_link('Название','title',$sort,$dir) }}">Название</a></th>
                    <th class="px-4 py-3">Категория</th>
                    <th class="px-4 py-3"><a href="{{ sort_link('Цена','price',$sort,$dir) }}">Цена</a></th>
                    <th class="px-4 py-3"><a href="{{ sort_link('Остаток','stock',$sort,$dir) }}">Остаток</a></th>
                    <th class="px-4 py-3">Изображение</th>
                    <th class="px-4 py-3">Галерея</th>
                    <th class="px-4 py-3 w-32">Действия</th>
                </tr>
            </thead>

            <tbody class="text-gray-800">
                @foreach($products as $p)
                <tr class="border-t hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $p->id }}</td>
                    <td class="px-4 py-3">{{ $p->title }}</td>
                    <td class="px-4 py-3">{{ $p->category->title ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $p->price }} ₽</td>
                    <td class="px-4 py-3">{{ $p->stock }}</td>

                    <!-- Главное изображение -->
                    <td class="px-4 py-3">
                        @if($p->image)
                        <img src="{{ $p->image_url }}"
                            class="w-16 h-16 object-cover rounded shadow">
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>

                    <!-- Галерея с превью при наведении -->
                    <td class="px-4 py-3 relative group">
                        @if($p->images->count())
                        <img src="{{ $p->images->first()->url }}"
                            class="w-16 h-16 object-cover rounded shadow cursor-pointer">

                        
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>

                    <!-- Действия -->
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-2">

                            <a href="{{ route('admin.products.edit', $p) }}"
                                class="bg-green-600 text-white text-center px-3 py-2 rounded hover:bg-green-700 cursor-pointer transition">
                                Редактировать
                            </a>

                            @if($p->deleted_at)
                            <button
                                data-slug="{{ $p->slug }}"
                                class="restore-btn bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                                Восстановить
                            </button>
                            @else
                            <button
                                data-slug="{{ $p->slug }}"
                                class="delete-btn bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700">
                                Удалить
                            </button>
                            @endif



                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>

<div class="mt-6">
    {{ $products->links() }}
</div>

<script>
document.addEventListener('click', async function(e) {

    // DELETE (soft delete)
    const deleteBtn = e.target.closest('.delete-btn');
    if (deleteBtn) {
        const slug = deleteBtn.dataset.slug;

        if (!confirm('Удалить товар?')) return;

        const res = await fetch(`{{ url('/admin/products') }}/${slug}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });

        if (res.ok) {
            deleteBtn.classList.remove('bg-red-600', 'hover:bg-red-700', 'delete-btn');
            deleteBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'restore-btn');
            deleteBtn.textContent = 'Восстановить';

            // 🔥 Показываем JS‑уведомление
            showToast('Товар успешно удалён!');
        }

        return;
    }

    // RESTORE
    const restoreBtn = e.target.closest('.restore-btn');
    if (restoreBtn) {
        const slug = restoreBtn.dataset.slug;

        if (!confirm('Восстановить товар?')) return;

        const res = await fetch(`{{ url('/admin/products') }}/${slug}/restore`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });

        if (res.ok) {
            restoreBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'restore-btn');
            restoreBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'delete-btn');
            restoreBtn.textContent = 'Удалить';

            // 🔥 Показываем JS‑уведомление
            showToast('Товар успешно восстановлен!');
        }
    }
});
</script>





@endsection