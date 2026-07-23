@extends('layouts.main')

@section('content')

    {{-- Хлебные крошки --}}
    <div class="max-w-[1500px] h-[65px] mx-auto flex items-center py-[24px] text-[13px] text-[#7b7979]">
        <x-breadcrumbs :items="[
            ['title' => 'Главная', 'url' => route('home')],
            ['title' => 'Каталог', 'url' => route('catalog.index')],
            ['title' => $group->title, 'url' => route('catalog.group', $group->slug)],
            [
                'title' => $category->title,
                'url' => route('catalog.category', [$group->slug, $category->slug])
            ],
            ['title' => $subcategory->title]
        ]" />
    </div>

    {{-- Заголовок подкатегории + количество товаров --}}
    <div class="max-w-[1500px] mx-auto flex items-center h-[42px] mb-[20px]">
        <h1 class="text-[34px] font-bold text-[#231F20] leading-none">
            {{ $subcategory->title }}

        </h1>
        <div class="ml-[10px] bg-gray-100 text-gray-400 text-xl self-end w-[32px] h-[26px] leading-none">
            {{ $variants->total() ?? $variants->count() }}
        </div>

    </div>

    {{-- Основная двухколоночная часть: 3 + 9 колонок --}}
    <div class="max-w-[1500px] mx-auto flex">

        {{-- Левая колонка: фильтры --}}
        <aside class="w-full max-w-[348px] pr-[32px] flex flex-col">
            @include('catalog.partials.filters')
        </aside>

        {{-- Правая колонка: резерв под блок характеристик + сортировка + список + пагинация --}}
        <main class="w-full max-w-[1152px] flex flex-col">

            {{-- Резерв под блок случайных характеристик (1152x76) --}}
            <div class="w-full h-[76px] bg-white border border-gray-200 rounded-lg mb-4 px-[30px] py-[14px]">
                {{-- TODO: блок случайных характеристик подкатегории --}}
                {{-- Зарезервировано под будущий функционал --}}
            </div>

            @include('catalog.partials.sorting')

            {{-- Список карточек товаров --}}
            {{-- Вынесен в partial для корректной работы AJAX-обновления --}}
            <div id="products-list" class="space-y-4">
                @include('catalog.partials.variants')
            </div>

            {{-- Блок "Показать еще" --}}
            @include('catalog.partials.show-more')

            {{-- Пагинация --}}
            @include('catalog.partials.pagination')

        </main>

    </div>

    {{-- Блок "Ранее вы смотрели" --}}
    @include('catalog.partials.recently-viewed2')
    
    {{-- Блок "Рекоммендуемые товары" --}}
    <!-- @include('catalog.partials.recommend2') -->

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            async function loadPage(url) {
                const list = document.getElementById('products-list');
                const pagination = document.getElementById('pagination-block');

                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');

                // Обновляем карточки
                const newCards = doc.querySelectorAll('#products-list > *');
                list.innerHTML = '';
                newCards.forEach(card => list.appendChild(card));

                // Обновляем пагинацию
                const newPagination = doc.querySelector('#pagination-block');
                if (newPagination) {
                    pagination.innerHTML = newPagination.innerHTML;
                }

                // обновляем show-more
                const oldShowMore = document.getElementById('show-more-wrapper');
                const newShowMore = doc.querySelector('#show-more-wrapper');

                if (oldShowMore && newShowMore) {
                    oldShowMore.replaceWith(newShowMore);
                } else if (oldShowMore && !newShowMore) {
                    oldShowMore.remove();
                } else if (!oldShowMore && newShowMore) {
                    pagination.insertAdjacentElement('beforebegin', newShowMore);
                }

                // Нужно обновлять data-current-page после успешной загрузки, иначе после загрузки новой страницы data-current-page не обновляется. То есть wrapper остаётся 
                // со старым значением, и при следующем клике кнопка будет пытаться грузить ту же страницу снова. Теперь кнопка «Показать ещё» будет корректно подгружать страницы 
                // до конца, а на последней странице исчезнет.
                const wrapper = document.getElementById('show-more-wrapper');
                if (wrapper) {
                    wrapper.dataset.currentPage = newShowMore?.dataset.currentPage || wrapper.dataset.currentPage;
                    wrapper.dataset.lastPage = newShowMore?.dataset.lastPage || wrapper.dataset.lastPage;
                }

                // Скролл вверх
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            window.loadPage = loadPage;

        });
    </script>


@endsection

