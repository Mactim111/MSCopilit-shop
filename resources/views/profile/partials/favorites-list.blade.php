<div id="favorites-list" class="space-y-4">
    @forelse($favorites as $variant)
        <x-variant-list-card
            :variant="$variant"
            :isFavorite="true"
            :isFavoritesPage="true"
        />
    @empty
        <div class="text-left">
            <p class="text-[15px] font-semibold text-black">
                Вы ещё не добавили товары в избранное
            </p>
            <a href="{{ route('catalog.index') }}"
               class="mt-4 inline-block text-[15px] text-[#007eff] hover:text-[#0064cc] transition-all duration-200">
                Перейти в каталог
            </a>
        </div>
    @endforelse
</div>
