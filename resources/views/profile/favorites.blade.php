@extends('layouts.main')

@section('title', 'Избранное')

@section('content')

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
    <x-breadcrumbs :items="[
        ['title' => 'Главная', 'url' => route('home')],
        ['title' => 'Личный кабинет', 'url' => route('profile')],
        ['title' => 'Избранное'],
    ]" />
</div>


{{-- Основная двухколоночная часть --}}
<div class="max-w-[1500px] mx-auto flex mt-6 mb-[70px]">

        {{-- Левая колонка --}}
        <aside class="w-full max-w-[348px] pr-[32px] flex flex-col">
            <div class="border border-gray-200 rounded-xl px-[24px] py-[16px] bg-white shadow-sm">

            </div>
        </aside>

        {{-- Правая колонка: заголовок с тегами + список вариантов + пагинация --}}
        <main class="w-full max-w-[1152px] flex flex-col">
            <div id="favorites-heading">
                @if($favorites->total() > 0)
                    @include('components.title-with-tags', [
                        'slider_title' => 'Избранное',
                        'slider_tags' => $favoriteTags,
                        'favoritesMode' => true,
                        'favoriteTotal' => auth()->user()->favorites()->count(),
                        'activeTagId' => $categoryId,
                        'tagContainerClass' => 'w-full',
                    ])
                @else
                    <div class="h-[56px] flex items-end mb-[8px]">
                        <h1 class="pb-[16px] text-[28px] font-bold text-[#231f20] leading-none">
                            Избранное
                        </h1>
                    </div>
                @endif
            </div>

            {{-- Список карточек избранных вариантов вынесен в partial для AJAX-замены. --}}
            @include('profile.partials.favorites-list')

            {{-- Блок "Показать еще" использует готовую механику каталога. --}}
            <div id="favorites-show-more">
                @include('catalog.partials.show-more', ['variants' => $favorites])
            </div>

            @if($favorites->total() > $favorites->perPage())
                {{-- Пагинация использует ту же верстку, что и страница подкатегории. --}}
                <div id="favorites-pagination">
                    @include('catalog.partials.pagination', ['variants' => $favorites])
                </div>
            @endif
        </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const loadFavoritesPage = async (url) => {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) {
            throw new Error('Не удалось обновить список избранного');
        }

        const documentFragment = new DOMParser().parseFromString(await response.text(), 'text/html');
        const currentList = document.getElementById('favorites-list');
        const newList = documentFragment.querySelector('#favorites-list');
        const currentHeading = document.getElementById('favorites-heading');
        const newHeading = documentFragment.querySelector('#favorites-heading');
        const currentShowMore = document.getElementById('favorites-show-more');
        const newShowMore = documentFragment.querySelector('#favorites-show-more');
        const currentPagination = document.getElementById('favorites-pagination');
        const newPagination = documentFragment.querySelector('#favorites-pagination');

        if (currentList && newList) currentList.replaceWith(newList);
        if (currentHeading && newHeading) currentHeading.replaceWith(newHeading);
        if (currentShowMore && newShowMore) currentShowMore.replaceWith(newShowMore);
        if (currentShowMore && !newShowMore) currentShowMore.remove();
        if (!currentShowMore && newShowMore) {
            document.getElementById('favorites-list')?.insertAdjacentElement('afterend', newShowMore);
        }
        if (currentPagination && newPagination) currentPagination.replaceWith(newPagination);
        if (currentPagination && !newPagination) currentPagination.remove();
        if (!currentPagination && newPagination) {
            const showMore = document.getElementById('favorites-show-more');
            const list = document.getElementById('favorites-list');
            (showMore || list)?.insertAdjacentElement('afterend', newPagination);
        }

        document.querySelectorAll('[data-favorite-category]').forEach((tag) => {
            const category = new URL(url, window.location.origin).searchParams.get('subcategory');
            const isActive = category
                ? tag.dataset.favoriteCategory === category
                : tag.dataset.favoriteCategory === 'all';
            tag.classList.toggle('border-black', isActive);
            tag.classList.toggle('bg-white', isActive);
            tag.classList.toggle('border-[#F4F4F4]', !isActive);
            tag.classList.toggle('bg-[#F4F4F4]', !isActive);
            tag.classList.toggle('shadow-none', isActive);
        });

        window.history.pushState({}, '', url);
    };

    window.loadFavoritesPage = loadFavoritesPage;
    // Компонент каталога show-more ожидает это имя обработчика.
    window.loadPage = loadFavoritesPage;

    document.addEventListener('click', async (event) => {
        const tag = event.target.closest('[data-favorite-category]');
        const remove = event.target.closest('[data-favorite-category-remove]');
        const pageLink = event.target.closest('#favorites-pagination a');

        if (remove) {
            event.preventDefault();
            event.stopPropagation();

            const popup = document.createElement('div');
            popup.className = 'fixed z-[60] w-[310px] bg-white border border-gray-200 rounded-xl shadow-xl p-[6px]';
            // Popup добавляется в body, чтобы его не обрезал overflow-hidden слайдера.
            const tagRect = remove.closest('[data-favorite-tag]').getBoundingClientRect();
            // Центр popup совпадает с правой границей выбранной плитки.
            popup.style.left = `${tagRect.right - 155}px`;
            popup.style.top = `${tagRect.bottom + 5}px`;
            popup.innerHTML = `
                <div class="px-[9px] py-[5px]">
                    <div class="mb-[10px] text-[14px] text-[#0e0e0e]">
                        Удалить список товаров (${remove.dataset.categoryTitle})?
                    </div>
                    <div class="flex justify-center gap-[10px]">
                        <button type="button"                         data-confirm-delete class="border border-[#0e0e0e] rounded-lg px-[20px] py-[8px] text-[15px] text-black cursor-pointer">Удалить</button>
                        <button type="button" data-cancel-delete class="border border-[#0e0e0e] rounded-lg px-[20px] py-[8px] text-[15px] text-black cursor-pointer">Не удалять</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);

            popup.querySelector('[data-cancel-delete]').addEventListener('click', () => popup.remove());
            popup.querySelector('[data-confirm-delete]').addEventListener('click', async () => {
                const response = await fetch(remove.dataset.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || 'Не удалось удалить список');
                popup.remove();
                window.updateFavoriteCount(payload.count);
                window.updateFavoritesPageCount(payload.count);
                remove.closest('[data-favorite-tag]')?.remove();
                document.querySelector('[data-favorite-category="all"] span')?.replaceChildren(
                    document.createTextNode(String(payload.count))
                );
                await loadFavoritesPage('{{ route('profile.favorites') }}');
                window.showFlashMessage(payload.message, 'success');
            });
            return;
        }

        if (tag && !event.target.closest('button')) {
            const url = new URL('{{ route('profile.favorites') }}', window.location.origin);
            if (tag.dataset.favoriteCategory !== 'all') {
                url.searchParams.set('subcategory', tag.dataset.favoriteCategory);
            }
            await loadFavoritesPage(url.toString());
            return;
        }

        if (pageLink) {
            event.preventDefault();
            await loadFavoritesPage(pageLink.href);
        }
    });
});
</script>

@endsection
