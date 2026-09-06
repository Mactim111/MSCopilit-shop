{{-- 
    Компонент: Теги подкатегории (Популярные подборки)
    Отображается только если НЕ выбраны фильтры в сайдбаре.
--}}

@php
    // Проверяем, есть ли активные фильтры (кроме пагинации)
    $hasActiveFilters = collect(request()->all())->except(['page', 'sort'])->isNotEmpty();
    
    // Тестовые данные (потом заменишь на реальную выборку из БД)
    $tags = $subcategoryTags ?? collect([
        'iPhone', 'Samsung', 'Xiaomi', 'iPhone 17', 'HONOR', 'Недорогие', 'iPhone 15', 'HUAWEI',
        'iPhone 13', 'iPhone 14', 'POCO', 'iPhone 16', 'iPhone 16 Pro', 'Xiaomi Redmi Note 17 Pro Max',
        'iPhone 17 Pro', 'iPhone 17 Pro Max', 'Gaming phones', 'OLED screens', 'Fast charging', 
        '5G Support', 'Best camera 2024', 'Pixel 9 Pro', 'Sony Xperia', 'Realme GT', 
        'Budget friendly', 'Refurbished', 'Dual SIM', 'Compact models', 'Long battery life', 'Waterproof'
    ]);

    $visibleCount = 16;
@endphp

@if(!$hasActiveFilters && $tags->count() > 0)
<div id="tags-block" class="w-full bg-white rounded-lg mb-6 transition-all duration-500 overflow-hidden">
    
    <div class="flex flex-wrap gap-[12px] items-end">
        
        @foreach($tags as $index => $tag)
            <div class="js-tag-item {{ $index >= $visibleCount ? 'hidden' : '' }}">
                <a href="#" 
                   class="h-[32px] px-[12px] pt-[5px] pb-[7px] bg-[#F4F4F4] rounded-[5px] 
                          text-[15px] text-[#231F20] leading-none inline-flex items-center justify-center 
                          transition-all duration-300 border border-transparent
                          hover:bg-white hover:border-gray-100 hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)] cursor-pointer">
                    {{ $tag }}
                </a>
            </div>
        @endforeach

        {{-- Кнопка "Еще / Скрыть" --}}
        @if($tags->count() > $visibleCount)
            <button id="tags-toggle" 
                    data-state="collapsed"
                    class="flex-none h-[32px] pl-[6px] pb-[1px] text-[14px] text-[#007EEF] hover:text-[#005bb5] transition-colors cursor-pointer outline-none">
                Еще
            </button>
        @endif

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('tags-toggle');
    const hiddenItems = document.querySelectorAll('.js-tag-item.hidden');
    const allItems = document.querySelectorAll('.js-tag-item');

    if (!toggleBtn) return;

    toggleBtn.addEventListener('click', () => {
        const isCollapsed = toggleBtn.getAttribute('data-state') === 'collapsed';

        if (isCollapsed) {
            // Разворачиваем
            hiddenItems.forEach(item => {
                item.classList.remove('hidden');
                item.classList.add('flex');
            });
            toggleBtn.innerText = 'Скрыть';
            toggleBtn.setAttribute('data-state', 'expanded');
        } else {
            // Сворачиваем
            allItems.forEach((item, index) => {
                if (index >= 16) {
                    item.classList.add('hidden');
                    item.classList.remove('flex');
                }
            });
            toggleBtn.innerText = 'Еще';
            toggleBtn.setAttribute('data-state', 'collapsed');
            
            // Скроллим чуть вверх к началу блока, если пользователь нажал "Скрыть" внизу
            document.getElementById('tags-block').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
});
</script>
@endif