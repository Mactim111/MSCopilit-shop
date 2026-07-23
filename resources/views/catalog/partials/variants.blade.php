{{--
    Partial: список карточек вариантов товаров.
    Переменная: $variants (LengthAwarePaginator по ProductVariant).
    Рендерится внутри <div id="products-list"> в catalog.index.
    При AJAX обновляется отдельно — контроллер рендерит только этот partial.
--}}

@if ($variants->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-gray-400">
        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-lg font-medium">Ничего не найдено</p>
        <p class="text-sm mt-1">Попробуйте изменить или сбросить фильтры</p>
    </div>
@else
    @foreach ($variants as $variant)
        <x-variant-list-card :variant="$variant" />
    @endforeach
@endif
